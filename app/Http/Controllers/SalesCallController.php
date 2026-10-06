<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\CallRecording;
use App\Models\Customer;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\SalesCall;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SalesCallController extends Controller
{
    public function index(Request $request)
    {
        $query = SalesCall::with(['customer', 'lead', 'user', 'recording']);

        if ($outcome = $request->input('outcome')) {
            $query->where('outcome', $outcome);
        }

        if ($direction = $request->input('direction')) {
            $query->where('direction', $direction);
        }

        if ($employeeId = $request->input('user_id')) {
            $query->where('user_id', $employeeId);
        }

        if ($date = $request->input('date')) {
            $query->whereDate('call_datetime', $date);
        }

        $calls = $query->latest('call_datetime')->paginate(15)->withQueryString();
        $employees = User::whereIn('role_slug', ['sales_executive', 'sales_manager'])->get();
        $outcomes = config('mantraheal.call_outcomes');

        return view('calls.index', compact('calls', 'employees', 'outcomes'));
    }

    public function create(Request $request)
    {
        $customerId = $request->input('customer_id');
        $leadId = $request->input('lead_id');

        $customer = $customerId ? Customer::find($customerId) : null;
        $lead = $leadId ? Lead::find($leadId) : null;
        $outcomes = config('mantraheal.call_outcomes');

        return view('calls.create', compact('customer', 'lead', 'outcomes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'lead_id' => 'nullable|exists:leads,id',
            'direction' => 'required|in:incoming,outgoing',
            'call_datetime' => 'required|date',
            'duration_seconds' => 'required|integer|min:0',
            'status' => 'required|string',
            'outcome' => 'required|string',
            'notes' => 'nullable|string',
            'follow_up_date' => 'nullable|date',
            'recording_file' => 'nullable|file|mimes:mp3,wav,ogg,m4a,aac|max:20480', // 20MB
        ]);

        $call = DB::transaction(function () use ($validated, $request) {
            $call = SalesCall::create([
                'customer_id' => $validated['customer_id'] ?? null,
                'lead_id' => $validated['lead_id'] ?? null,
                'user_id' => Auth::id(),
                'direction' => $validated['direction'],
                'call_datetime' => $validated['call_datetime'],
                'duration_seconds' => $validated['duration_seconds'],
                'status' => $validated['status'],
                'outcome' => $validated['outcome'],
                'notes' => $validated['notes'] ?? null,
                'follow_up_date' => $validated['follow_up_date'] ?? null,
            ]);

            // Save audio recording if uploaded
            if ($request->hasFile('recording_file')) {
                $file = $request->file('recording_file');
                $filename = 'call_' . $call->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('call-recordings', $filename);

                $recording = CallRecording::create([
                    'sales_call_id' => $call->id,
                    'customer_id' => $call->customer_id,
                    'user_id' => Auth::id(),
                    'file_path' => $path,
                    'file_name' => $filename,
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'duration_seconds' => $call->duration_seconds,
                    'is_protected' => true,
                ]);

                $call->recording_path = $path;
                $call->save();
            } else {
                // If no file uploaded, link to sample demo recording if in local demo mode
                $samplePath = 'call-recordings/sample_call.wav';
                if (Storage::disk('local')->exists($samplePath)) {
                    CallRecording::create([
                        'sales_call_id' => $call->id,
                        'customer_id' => $call->customer_id,
                        'user_id' => Auth::id(),
                        'file_path' => $samplePath,
                        'file_name' => 'sample_call.wav',
                        'mime_type' => 'audio/wav',
                        'file_size' => Storage::disk('local')->size($samplePath),
                        'duration_seconds' => $call->duration_seconds,
                        'is_protected' => true,
                    ]);
                    $call->recording_path = $samplePath;
                    $call->save();
                }
            }

            // Create automatic follow-up if date is set
            if (!empty($validated['follow_up_date'])) {
                FollowUp::create([
                    'customer_id' => $call->customer_id,
                    'lead_id' => $call->lead_id,
                    'user_id' => Auth::id(),
                    'due_date' => $validated['follow_up_date'],
                    'reason' => 'Call Outcome: ' . $call->outcome,
                    'priority' => in_array($call->outcome, ['Interested', 'Order Taken']) ? 'High' : 'Medium',
                    'status' => 'Pending',
                    'notes' => 'Generated from Call on ' . now()->format('d M Y') . ': ' . ($call->notes ?? 'Follow up required'),
                ]);
            }

            AuditLog::log('call_logged', $call, "Call logged for " . ($call->customer?->name ?? $call->lead?->name ?? 'Contact') . " ({$call->outcome})");

            return $call;
        });

        if ($call->customer_id) {
            return redirect()->route('customers.show', $call->customer_id)
                ->with('success', 'Call record saved successfully.');
        } elseif ($call->lead_id) {
            return redirect()->route('leads.show', $call->lead_id)
                ->with('success', 'Call record saved successfully.');
        }

        return redirect()->route('calls.index')->with('success', 'Call logged successfully.');
    }
}
