<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\User;
use App\Services\OrderService;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LeadController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected ReportService $reportService
    ) {}

    public function index(Request $request)
    {
        $query = Lead::with('assignedUser');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('lead_code', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%");
            });
        }

        if ($stage = $request->input('stage')) {
            $query->where('stage', $stage);
        }

        if ($source = $request->input('source')) {
            $query->where('source', $source);
        }

        if ($assignedTo = $request->input('assigned_user_id')) {
            $query->where('assigned_user_id', $assignedTo);
        }

        $viewMode = $request->input('view', 'list'); // list or kanban
        $salesExecutives = User::whereIn('role_slug', ['sales_executive', 'sales_manager'])->get();
        $stages = config('mantraheal.lead_stages', ['New', 'Contacted', 'Interested', 'Follow-up', 'Quotation Sent', 'Order Confirmed', 'Won', 'Lost']);

        if ($viewMode === 'kanban') {
            $allLeads = $query->latest()->get();
            $kanbanLeads = [];
            foreach ($stages as $st) {
                $kanbanLeads[$st] = $allLeads->where('stage', $st);
            }
            return view('leads.kanban', compact('kanbanLeads', 'stages', 'salesExecutives'));
        }

        $leads = $query->latest()->paginate(15)->withQueryString();
        return view('leads.index', compact('leads', 'salesExecutives', 'stages'));
    }

    public function create()
    {
        $salesExecutives = User::whereIn('role_slug', ['sales_executive', 'sales_manager'])->get();
        return view('leads.create', compact('salesExecutives'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'source' => 'required|string',
            'stage' => 'required|string',
            'estimated_value' => 'nullable|numeric|min:0',
            'assigned_user_id' => 'nullable|exists:users,id',
            'next_followup_at' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $lead = DB::transaction(function () use ($validated) {
            $leadCode = 'MH-LEAD-' . strtoupper(Str::random(6));

            $lead = Lead::create([
                'lead_code' => $leadCode,
                'name' => $validated['name'],
                'mobile' => $validated['mobile'],
                'whatsapp' => $validated['whatsapp'] ?? $validated['mobile'],
                'email' => $validated['email'] ?? null,
                'city' => $validated['city'] ?? null,
                'state' => $validated['state'] ?? null,
                'pincode' => $validated['pincode'] ?? null,
                'source' => $validated['source'],
                'stage' => $validated['stage'],
                'estimated_value' => $validated['estimated_value'] ?? 0,
                'assigned_user_id' => $validated['assigned_user_id'] ?? Auth::id(),
                'next_followup_at' => $validated['next_followup_at'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            LeadActivity::create([
                'lead_id' => $lead->id,
                'user_id' => Auth::id(),
                'type' => 'created',
                'description' => "Lead created from source: {$lead->source}",
                'new_stage' => $lead->stage,
            ]);

            AuditLog::log('created', $lead, "Lead {$lead->name} created ({$lead->lead_code})");

            return $lead;
        });

        return redirect()->route('leads.show', $lead->id)->with('success', 'Lead created successfully.');
    }

    public function show(Lead $lead)
    {
        $lead->load(['assignedUser', 'activities.user', 'salesCalls.user', 'followups.user']);
        $salesExecutives = User::whereIn('role_slug', ['sales_executive', 'sales_manager'])->get();
        $stages = config('mantraheal.lead_stages');
        $lostReasons = config('mantraheal.lost_reasons');

        return view('leads.show', compact('lead', 'salesExecutives', 'stages', 'lostReasons'));
    }

    public function update(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'source' => 'required|string',
            'stage' => 'required|string',
            'lost_reason' => 'nullable|string',
            'estimated_value' => 'nullable|numeric|min:0',
            'assigned_user_id' => 'nullable|exists:users,id',
            'next_followup_at' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $oldStage = $lead->stage;
        $newStage = $validated['stage'];

        $lead->update($validated);

        if ($oldStage !== $newStage) {
            LeadActivity::create([
                'lead_id' => $lead->id,
                'user_id' => Auth::id(),
                'type' => 'stage_change',
                'description' => "Stage changed from {$oldStage} to {$newStage}" . ($validated['lost_reason'] ? " (Reason: {$validated['lost_reason']})" : ""),
                'old_stage' => $oldStage,
                'new_stage' => $newStage,
            ]);
        }

        AuditLog::log('updated', $lead, "Lead {$lead->name} updated");

        return back()->with('success', 'Lead updated successfully.');
    }

    public function convert(Request $request, Lead $lead)
    {
        if ($lead->converted_to_customer_id) {
            return back()->with('error', 'This lead is already converted to a customer.');
        }

        $customer = DB::transaction(function () use ($lead, $request) {
            // Check if customer with mobile exists
            $customer = Customer::where('mobile', $lead->mobile)->first();
            if (!$customer) {
                $customerCode = 'MH-CUST-' . strtoupper(Str::random(6));
                $customer = Customer::create([
                    'customer_code' => $customerCode,
                    'name' => $lead->name,
                    'mobile' => $lead->mobile,
                    'whatsapp' => $lead->whatsapp ?? $lead->mobile,
                    'email' => $lead->email,
                    'customer_source' => $lead->source,
                    'customer_type' => 'Retail',
                    'assigned_user_id' => $lead->assigned_user_id,
                    'notes' => "Converted from Lead {$lead->lead_code}. " . $lead->notes,
                ]);
            }

            $lead->update([
                'stage' => 'Won',
                'converted_to_customer_id' => $customer->id,
                'converted_at' => now(),
            ]);

            LeadActivity::create([
                'lead_id' => $lead->id,
                'user_id' => Auth::id(),
                'type' => 'conversion',
                'description' => "Lead converted into Customer {$customer->name} ({$customer->customer_code})",
                'new_stage' => 'Won',
            ]);

            AuditLog::log('converted', $lead, "Lead {$lead->name} converted to Customer {$customer->name}");

            return $customer;
        });

        return redirect()->route('customers.show', $customer->id)
            ->with('success', "Lead successfully converted to Customer #{$customer->customer_code}!");
    }

    public function addNote(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'note' => 'required|string|max:1000',
        ]);

        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => Auth::id(),
            'type' => 'note',
            'description' => $validated['note'],
        ]);

        return back()->with('success', 'Note added to lead timeline.');
    }

    public function destroy(Lead $lead)
    {
        AuditLog::log('deleted', $lead, "Lead {$lead->name} deleted");
        $lead->delete();
        return redirect()->route('leads.index')->with('success', 'Lead deleted.');
    }

    public function exportCsv()
    {
        $leads = Lead::with('assignedUser')->get();
        $headers = ['Lead Code', 'Name', 'Mobile', 'Email', 'City', 'Source', 'Stage', 'Lost Reason', 'Est Value', 'Assigned To', 'Created At'];
        $rows = [];

        foreach ($leads as $l) {
            $rows[] = [
                $l->lead_code,
                $l->name,
                $l->mobile,
                $l->email ?? '',
                $l->city ?? '',
                $l->source,
                $l->stage,
                $l->lost_reason ?? '',
                $l->estimated_value,
                $l->assignedUser?->name ?? 'Unassigned',
                $l->created_at->format('Y-m-d H:i'),
            ];
        }

        return $this->reportService->exportCsv($headers, $rows, 'mantraheal_leads_' . date('Y-m-d') . '.csv');
    }
}
