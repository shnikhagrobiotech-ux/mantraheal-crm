<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeadImportController extends Controller
{
    /**
     * Show the bulk lead import interface
     */
    public function index()
    {
        $salesExecutives = User::whereIn('role_slug', ['sales_executive', 'sales_manager'])->get();
        $stages = config('mantraheal.lead_stages', ['New', 'Contacted', 'Interested', 'Follow-up', 'Quotation Sent', 'Order Confirmed', 'Won', 'Lost']);
        $sources = config('mantraheal.lead_sources', ['Website', 'Meta Ads', 'Shopify', 'WhatsApp', 'Exhibition', 'Bulk Import', 'Referral']);

        $recentImportsCount = LeadActivity::where('type', 'import')->whereDate('created_at', today())->count();

        return view('leads.import', compact('salesExecutives', 'stages', 'sources', 'recentImportsCount'));
    }

    /**
     * Download a sample CSV template with example records
     */
    public function downloadTemplate(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="mantraheal_lead_import_template.csv"',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            
            // UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // CSV Header
            fputcsv($handle, [
                'Name',
                'Mobile',
                'Email',
                'City',
                'State',
                'Pincode',
                'Source',
                'Stage',
                'Estimated Value',
                'Notes'
            ]);

            // Sample Ayurvedic Lead Records
            fputcsv($handle, [
                'Vikram Singhania',
                '+91 98210 44556',
                'vikram.singhania@example.com',
                'Jaipur',
                'Rajasthan',
                '302001',
                'Bulk Import',
                'New',
                '2499',
                'Inquired about Pure Himalayan Shilajit 50g resin bundle'
            ]);

            fputcsv($handle, [
                'Pooja Nambiar',
                '+91 94470 12389',
                'pooja.nambiar@example.com',
                'Kochi',
                'Kerala',
                '682001',
                'Meta Ads',
                'Interested',
                '1899',
                'Interested in KSM-66 Ashwagandha for stress relief'
            ]);

            fputcsv($handle, [
                'Harpreet Singh',
                '+91 98140 98765',
                'harpreet.singh@example.com',
                'Ludhiana',
                'Punjab',
                '141001',
                'WhatsApp',
                'New',
                '3500',
                'Looking for diabetic wellness Ayurvedic care pack'
            ]);

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Process uploaded CSV file and import leads
     */
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
            'duplicate_action' => 'required|in:skip,update,allow',
            'assignment_mode' => 'required|in:round_robin,specific,unassigned',
            'assigned_user_id' => 'nullable|exists:users,id',
            'default_stage' => 'required|string',
            'default_source' => 'required|string',
        ]);

        $file = $request->file('csv_file');
        $duplicateAction = $request->input('duplicate_action');
        $assignmentMode = $request->input('assignment_mode');
        $specificUserId = $request->input('assigned_user_id');
        $defaultStage = $request->input('default_stage');
        $defaultSource = $request->input('default_source');

        // Prepare round-robin pool
        $salesReps = User::whereIn('role_slug', ['sales_executive', 'sales_manager'])->pluck('id')->toArray();
        $repIndex = 0;
        $totalReps = count($salesReps);

        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            return back()->with('error', 'Unable to open the uploaded file.');
        }

        // Detect BOM and read header
        $firstLine = fgets($handle);
        $firstLine = preg_replace('/^\xEF\xBB\xBF/', '', $firstLine); // remove BOM
        $header = str_getcsv($firstLine);

        // Normalize header columns to index mapping
        $columnMap = $this->mapHeaders($header);

        if (!isset($columnMap['name']) || !isset($columnMap['mobile'])) {
            fclose($handle);
            return back()->with('error', 'CSV must contain at least "Name" and "Mobile" or "Phone" columns.');
        }

        $importedCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;
        $errorRows = [];
        $rowNum = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            if (empty(array_filter($row))) {
                continue; // Skip empty rows
            }

            $name = trim($row[$columnMap['name']] ?? '');
            $rawMobile = trim($row[$columnMap['mobile']] ?? '');

            if (empty($name) || empty($rawMobile)) {
                $errorRows[] = "Row #{$rowNum}: Missing name or mobile number.";
                continue;
            }

            // Clean digits for robust comparison
            $cleanDigits = preg_replace('/[^0-9]/', '', $rawMobile);
            $searchDigits = strlen($cleanDigits) > 10 ? substr($cleanDigits, -10) : $cleanDigits;

            if (strlen($searchDigits) < 7) {
                $errorRows[] = "Row #{$rowNum} ({$name}): Invalid phone number '{$rawMobile}'.";
                continue;
            }

            $email = isset($columnMap['email']) ? trim($row[$columnMap['email']] ?? '') : null;
            $city = isset($columnMap['city']) ? trim($row[$columnMap['city']] ?? '') : null;
            $state = isset($columnMap['state']) ? trim($row[$columnMap['state']] ?? '') : null;
            $pincode = isset($columnMap['pincode']) ? substr(trim($row[$columnMap['pincode']] ?? ''), 0, 10) : null;
            $source = isset($columnMap['source']) && !empty(trim($row[$columnMap['source']])) ? trim($row[$columnMap['source']]) : $defaultSource;
            $stage = isset($columnMap['stage']) && !empty(trim($row[$columnMap['stage']])) ? trim($row[$columnMap['stage']]) : $defaultStage;
            $estValue = isset($columnMap['estimated_value']) ? (float) preg_replace('/[^0-9.]/', '', $row[$columnMap['estimated_value']] ?? '0') : 0.00;
            $notes = isset($columnMap['notes']) ? trim($row[$columnMap['notes']] ?? '') : null;

            // Check for existing lead by phone
            $existingLead = Lead::whereRaw("REPLACE(REPLACE(REPLACE(mobile, ' ', ''), '-', ''), '+', '') LIKE ?", ["%{$searchDigits}%"])
                ->orWhere('mobile', 'like', "%{$searchDigits}%")
                ->when($email, fn($q) => $q->orWhere('email', $email))
                ->first();

            if ($existingLead && $duplicateAction === 'skip') {
                $skippedCount++;
                continue;
            }

            if ($existingLead && $duplicateAction === 'update') {
                $existingLead->update([
                    'name' => $name,
                    'city' => $city ?: $existingLead->city,
                    'state' => $state ?: $existingLead->state,
                    'pincode' => $pincode ?: $existingLead->pincode,
                    'estimated_value' => $estValue > 0 ? $estValue : $existingLead->estimated_value,
                    'notes' => $notes ? ($existingLead->notes . "\n[Re-imported] " . $notes) : $existingLead->notes,
                ]);

                LeadActivity::create([
                    'lead_id' => $existingLead->id,
                    'user_id' => Auth::id(),
                    'type' => 'import',
                    'description' => "Lead profile updated via CSV import from '{$file->getClientOriginalName()}'.",
                ]);

                $updatedCount++;
                continue;
            }

            // Assign sales rep
            $assignedId = null;
            if ($assignmentMode === 'specific') {
                $assignedId = $specificUserId;
            } elseif ($assignmentMode === 'round_robin' && $totalReps > 0) {
                $assignedId = $salesReps[$repIndex % $totalReps];
                $repIndex++;
            }

            // Create new lead
            $newLead = Lead::create([
                'name' => $name,
                'mobile' => $rawMobile,
                'email' => $email ?: null,
                'city' => $city ?: null,
                'state' => $state ?: null,
                'pincode' => $pincode ?: null,
                'source' => $source,
                'stage' => $stage,
                'estimated_value' => $estValue,
                'assigned_user_id' => $assignedId,
                'notes' => $notes ?: 'Imported via bulk CSV upload',
            ]);

            LeadActivity::create([
                'lead_id' => $newLead->id,
                'user_id' => Auth::id(),
                'type' => 'import',
                'description' => "Lead created via bulk CSV import from '{$file->getClientOriginalName()}'.",
            ]);

            $importedCount++;
        }

        fclose($handle);

        AuditLog::log('imported', null, "Imported {$importedCount} leads, updated {$updatedCount}, skipped {$skippedCount} duplicates from {$file->getClientOriginalName()}");

        $feedback = "Lead Import Complete! Imported {$importedCount} new leads";
        if ($updatedCount > 0) $feedback .= ", updated {$updatedCount} existing leads";
        if ($skippedCount > 0) $feedback .= ", skipped {$skippedCount} duplicates";
        $feedback .= ".";

        return redirect()->route('leads.index')->with([
            'success' => $feedback,
            'import_errors' => array_slice($errorRows, 0, 10),
        ]);
    }

    /**
     * Map flexible CSV header names to canonical lead attributes
     */
    protected function mapHeaders(array $headers): array
    {
        $map = [];
        foreach ($headers as $index => $col) {
            $colClean = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '', $col)));

            if (in_array($colClean, ['name', 'fullname', 'leadname', 'customername', 'clientname'])) {
                $map['name'] = $index;
            } elseif (in_array($colClean, ['mobile', 'phone', 'phonenumber', 'contact', 'contactnumber', 'cell', 'mobilephone'])) {
                $map['mobile'] = $index;
            } elseif (in_array($colClean, ['email', 'emailaddress', 'mail'])) {
                $map['email'] = $index;
            } elseif (in_array($colClean, ['city', 'location', 'town'])) {
                $map['city'] = $index;
            } elseif (in_array($colClean, ['state', 'province', 'region'])) {
                $map['state'] = $index;
            } elseif (in_array($colClean, ['pincode', 'pin', 'zip', 'zipcode', 'postalcode'])) {
                $map['pincode'] = $index;
            } elseif (in_array($colClean, ['source', 'leadsource', 'channel', 'utmcampaign'])) {
                $map['source'] = $index;
            } elseif (in_array($colClean, ['stage', 'leadstage', 'status'])) {
                $map['stage'] = $index;
            } elseif (in_array($colClean, ['estimatedvalue', 'estvalue', 'value', 'budget', 'amount', 'dealvalue'])) {
                $map['estimated_value'] = $index;
            } elseif (in_array($colClean, ['notes', 'note', 'remarks', 'comment', 'query', 'inquiry'])) {
                $map['notes'] = $index;
            }
        }

        return $map;
    }
}
