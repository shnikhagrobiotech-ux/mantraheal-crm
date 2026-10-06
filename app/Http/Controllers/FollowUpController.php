<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FollowUpController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->input('tab', 'today');
        $user = Auth::user();

        $query = FollowUp::with(['customer', 'lead', 'user']);

        if ($user->isSalesExecutive()) {
            $query->where('user_id', $user->id);
        } elseif ($employeeId = $request->input('user_id')) {
            $query->where('user_id', $employeeId);
        }

        switch ($tab) {
            case 'overdue':
                $query->overdue()->orderBy('due_date', 'asc');
                break;
            case 'tomorrow':
                $query->tomorrow()->orderBy('due_time', 'asc');
                break;
            case 'upcoming':
                $query->upcoming()->orderBy('due_date', 'asc');
                break;
            case 'completed':
                $query->completed()->latest('completed_at');
                break;
            case 'all':
                $query->latest('due_date');
                break;
            case 'today':
            default:
                $query->today()->orderBy('due_time', 'asc');
                break;
        }

        $followups = $query->paginate(15)->withQueryString();

        // Counts for tab badges
        $baseQuery = FollowUp::query();
        if ($user->isSalesExecutive()) {
            $baseQuery->where('user_id', $user->id);
        }

        $counts = [
            'today' => (clone $baseQuery)->today()->count(),
            'overdue' => (clone $baseQuery)->overdue()->count(),
            'tomorrow' => (clone $baseQuery)->tomorrow()->count(),
            'upcoming' => (clone $baseQuery)->upcoming()->count(),
            'completed' => (clone $baseQuery)->completed()->count(),
        ];

        $employees = User::whereIn('role_slug', ['sales_executive', 'sales_manager'])->get();

        return view('followups.index', compact('followups', 'tab', 'counts', 'employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'lead_id' => 'nullable|exists:leads,id',
            'user_id' => 'nullable|exists:users,id',
            'due_date' => 'required|date',
            'due_time' => 'nullable',
            'reason' => 'required|string|max:255',
            'priority' => 'required|in:Low,Medium,High',
            'notes' => 'nullable|string',
        ]);

        $followup = FollowUp::create([
            'customer_id' => $validated['customer_id'] ?? null,
            'lead_id' => $validated['lead_id'] ?? null,
            'user_id' => $validated['user_id'] ?? Auth::id(),
            'due_date' => $validated['due_date'],
            'due_time' => $validated['due_time'] ?? null,
            'reason' => $validated['reason'],
            'priority' => $validated['priority'],
            'status' => 'Pending',
            'notes' => $validated['notes'] ?? null,
        ]);

        AuditLog::log('created', $followup, "Follow-up scheduled for {$followup->due_date} ({$followup->reason})");

        return back()->with('success', 'Follow-up scheduled successfully.');
    }

    public function complete(Request $request, FollowUp $followup)
    {
        $followup->update([
            'status' => 'Completed',
            'completed_at' => now(),
            'notes' => ($followup->notes ? $followup->notes . "\n" : "") . "[Completed on " . now()->format('d M Y') . "] " . $request->input('notes'),
        ]);

        AuditLog::log('followup_completed', $followup, "Follow-up #{$followup->id} marked as completed");

        return back()->with('success', 'Follow-up marked as completed.');
    }

    public function reschedule(Request $request, FollowUp $followup)
    {
        $validated = $request->validate([
            'due_date' => 'required|date',
            'due_time' => 'nullable',
            'notes' => 'nullable|string',
        ]);

        $oldDate = $followup->due_date;
        $followup->update([
            'due_date' => $validated['due_date'],
            'due_time' => $validated['due_time'] ?? null,
            'status' => 'Pending',
            'notes' => ($followup->notes ? $followup->notes . "\n" : "") . "[Rescheduled from " . $oldDate->format('d M') . "] " . $validated['notes'],
        ]);

        AuditLog::log('followup_rescheduled', $followup, "Follow-up rescheduled to {$followup->due_date}");

        return back()->with('success', 'Follow-up rescheduled successfully.');
    }
}
