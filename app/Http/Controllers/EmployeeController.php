<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\Order;
use App\Models\Role;
use App\Models\SalesCall;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = User::with('role')->paginate(15);
        $roles = Role::all();
        return view('employees.index', compact('employees', 'roles'));
    }

    public function show(User $employee)
    {
        $today = Carbon::today();

        // Actual employee activity metrics
        $callsToday = SalesCall::where('user_id', $employee->id)->whereDate('call_datetime', $today)->count();
        $connectedCalls = SalesCall::where('user_id', $employee->id)->whereDate('call_datetime', $today)
            ->whereIn('outcome', ['Connected', 'Interested', 'Order Taken', 'Follow-up Required'])
            ->count();
        $totalTalkTimeSeconds = SalesCall::where('user_id', $employee->id)->sum('duration_seconds');
        $talkTimeFormatted = sprintf('%02d:%02d:%02d', floor($totalTalkTimeSeconds / 3600), floor(($totalTalkTimeSeconds % 3600) / 60), $totalTalkTimeSeconds % 60);

        $assignedLeads = Lead::where('assigned_user_id', $employee->id)->count();
        $convertedLeads = Lead::where('assigned_user_id', $employee->id)->where('stage', 'Won')->count();

        $followupsToday = FollowUp::where('user_id', $employee->id)->today()->count();
        $overdueFollowups = FollowUp::where('user_id', $employee->id)->overdue()->count();

        $ordersCount = Order::where('assigned_user_id', $employee->id)->count();
        $totalRevenue = Order::where('assigned_user_id', $employee->id)->whereIn('order_status', ['Delivered', 'Shipped', 'Confirmed', 'Processing'])->sum('grand_total');

        $recentCalls = SalesCall::where('user_id', $employee->id)->with('customer')->latest('call_datetime')->limit(5)->get();
        $recentFollowups = FollowUp::where('user_id', $employee->id)->with(['customer', 'lead'])->latest('due_date')->limit(5)->get();

        return view('employees.show', compact(
            'employee',
            'callsToday',
            'connectedCalls',
            'talkTimeFormatted',
            'assignedLeads',
            'convertedLeads',
            'followupsToday',
            'overdueFollowups',
            'ordersCount',
            'totalRevenue',
            'recentCalls',
            'recentFollowups'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id',
            'designation' => 'nullable|string|max:100',
        ]);

        $role = Role::findOrFail($validated['role_id']);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role_id' => $role->id,
            'role_slug' => $role->slug,
            'designation' => $validated['designation'] ?? $role->name,
            'status' => 'active',
        ]);

        AuditLog::log('created', $user, "Employee account created for {$user->name} ({$role->name})");

        return back()->with('success', 'Employee account created successfully.');
    }

    public function update(Request $request, User $employee)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'role_id' => 'required|exists:roles,id',
            'designation' => 'nullable|string|max:100',
            'status' => 'required|in:active,inactive',
        ]);

        $role = Role::findOrFail($validated['role_id']);
        $validated['role_slug'] = $role->slug;

        $employee->update($validated);
        AuditLog::log('updated', $employee, "Employee details updated for {$employee->name}");

        return back()->with('success', 'Employee updated successfully.');
    }
}
