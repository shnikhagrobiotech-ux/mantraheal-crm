<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\CallRecording;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\SalesCall;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DialerController extends Controller
{
    /**
     * Real-time contact lookup by phone number as user types in dialer
     */
    public function lookup(Request $request): JsonResponse
    {
        $phone = preg_replace('/[^0-9]/', '', (string) $request->input('phone', ''));
        if (strlen($phone) < 3) {
            return response()->json(['match' => null]);
        }

        $searchDigits = strlen($phone) > 10 ? substr($phone, -10) : $phone;

        // Search in Customers first (strip formatting chars for robust lookup)
        $customer = Customer::whereRaw("REPLACE(REPLACE(REPLACE(mobile, ' ', ''), '-', ''), '+', '') LIKE ?", ["%{$searchDigits}%"])
            ->orWhere('mobile', 'like', "%{$searchDigits}%")
            ->first();
        if ($customer) {
            $latestOrder = $customer->orders()->latest()->first();
            $lastCall = SalesCall::where('customer_id', $customer->id)->latest()->first();

            return response()->json([
                'match' => [
                    'type' => 'customer',
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'city' => $customer->city ?? 'N/A',
                    'state' => $customer->state ?? '',
                    'total_orders' => $customer->total_orders ?? 0,
                    'last_order_status' => $latestOrder?->order_status,
                    'last_call_outcome' => $lastCall?->outcome,
                    'badge' => 'Customer',
                ]
            ]);
        }

        // Search in Leads (strip formatting chars for robust lookup)
        $lead = Lead::whereRaw("REPLACE(REPLACE(REPLACE(mobile, ' ', ''), '-', ''), '+', '') LIKE ?", ["%{$searchDigits}%"])
            ->orWhere('mobile', 'like', "%{$searchDigits}%")
            ->first();
        if ($lead) {
            $lastCall = SalesCall::where('lead_id', $lead->id)->latest()->first();

            return response()->json([
                'match' => [
                    'type' => 'lead',
                    'id' => $lead->id,
                    'name' => $lead->name,
                    'phone' => $lead->phone,
                    'city' => $lead->city ?? 'N/A',
                    'state' => $lead->state ?? '',
                    'stage' => $lead->stage ?? 'New',
                    'last_call_outcome' => $lastCall?->outcome,
                    'badge' => 'Lead (' . ($lead->stage ?? 'New') . ')',
                ]
            ]);
        }

        return response()->json(['match' => null]);
    }

    /**
     * Start an outbound call session
     */
    public function startCall(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => 'required|string|min:5|max:20',
            'customer_id' => 'nullable|integer',
            'lead_id' => 'nullable|integer',
        ]);

        $callSessionId = 'CALL-' . strtoupper(uniqid());

        return response()->json([
            'status' => 'initiated',
            'session_id' => $callSessionId,
            'caller_id' => Setting::get('telephony_caller_id', '+91 80 4719 2830'),
            'provider' => Setting::get('telephony_provider', 'Direct Web Dialer'),
            'started_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * End call, record outcome, notes, and attach recording sample
     */
    public function endCall(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => 'required|string',
            'duration_seconds' => 'required|integer|min:0',
            'outcome' => 'required|string',
            'notes' => 'nullable|string',
            'customer_id' => 'nullable|integer|exists:customers,id',
            'lead_id' => 'nullable|integer|exists:leads,id',
            'direction' => 'nullable|string|in:outgoing,incoming',
            'follow_up_date' => 'nullable|date',
        ]);

        $user = Auth::user();

        // If customer_id or lead_id is missing, try resolving by phone
        $customerId = $validated['customer_id'] ?? null;
        $leadId = $validated['lead_id'] ?? null;

        if (!$customerId && !$leadId) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $validated['phone']);
            $search = strlen($cleanPhone) > 10 ? substr($cleanPhone, -10) : $cleanPhone;
            $cust = Customer::where('mobile', 'like', "%{$search}%")->first();
            if ($cust) {
                $customerId = $cust->id;
            } else {
                $lead = Lead::where('mobile', 'like', "%{$search}%")->first();
                if ($lead) {
                    $leadId = $lead->id;
                }
            }
        }

        $salesCall = SalesCall::create([
            'customer_id' => $customerId,
            'lead_id' => $leadId,
            'user_id' => $user->id,
            'direction' => $validated['direction'] ?? 'outgoing',
            'call_datetime' => now(),
            'duration_seconds' => $validated['duration_seconds'],
            'status' => 'Completed',
            'outcome' => $validated['outcome'],
            'notes' => $validated['notes'] ?? 'Logged from In-App Softphone Dialer',
            'follow_up_date' => $validated['follow_up_date'] ?? null,
        ]);

        // Auto-create sample audio recording if connected and duration > 0
        $recording = null;
        if ($validated['duration_seconds'] > 0 && !in_array($validated['outcome'], ['Busy', 'No Answer', 'Wrong Number'])) {
            $recording = CallRecording::create([
                'sales_call_id' => $salesCall->id,
                'customer_id' => $customerId,
                'user_id' => $user->id,
                'file_path' => 'call-recordings/sample_call.wav',
                'file_name' => 'rec_' . $salesCall->id . '_' . time() . '.wav',
                'mime_type' => 'audio/wav',
                'file_size' => 48044,
                'duration_seconds' => $validated['duration_seconds'],
                'is_protected' => true,
            ]);
        }

        AuditLog::log('created', $salesCall, "Call #{$salesCall->id} logged via In-App Dialer by {$user->name} ({$validated['outcome']})");

        return response()->json([
            'status' => 'success',
            'message' => 'Call logged successfully',
            'sales_call_id' => $salesCall->id,
            'recording_id' => $recording?->id,
        ]);
    }
}
