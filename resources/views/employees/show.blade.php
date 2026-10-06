@extends('layouts.app')

@section('title', "{$employee->name} — Employee Profile — MantraHeal CRM")

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('employees.index') }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Employees
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-xs text-slate-500">{{ $employee->email }}</span>
            </div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-800 font-bold flex items-center justify-center text-sm">
                    {{ strtoupper(substr($employee->name, 0, 2)) }}
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 tracking-tight">{{ $employee->name }}</h1>
                    <div class="text-xs text-slate-500">{{ $employee->designation ?? $employee->role?->name }} • {{ $employee->email }} • {{ $employee->phone ?? 'No phone' }}</div>
                </div>
                <span class="badge badge-success text-xs capitalize">{{ $employee->status }}</span>
            </div>
        </div>
    </div>

    <!-- Real Activity Metric Cards (Section 21 Requirements) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="card p-4">
            <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Calls Today</div>
            <div class="text-2xl font-bold text-slate-800 mt-1">{{ $callsToday }}</div>
            <div class="text-[11px] text-emerald-700 font-semibold mt-0.5">{{ $connectedCalls }} Connected</div>
        </div>

        <div class="card p-4">
            <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Talk Time (Total)</div>
            <div class="text-2xl font-bold text-emerald-800 mt-1">{{ $talkTimeFormatted }}</div>
            <div class="text-[11px] text-slate-400 mt-0.5">HH:MM:SS Duration</div>
        </div>

        <div class="card p-4">
            <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Assigned Leads</div>
            <div class="text-2xl font-bold text-slate-800 mt-1">{{ $assignedLeads }}</div>
            <div class="text-[11px] text-emerald-700 font-semibold mt-0.5">{{ $convertedLeads }} Converted (Won)</div>
        </div>

        <div class="card p-4">
            <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Follow-ups Today</div>
            <div class="text-2xl font-bold text-slate-800 mt-1">{{ $followupsToday }}</div>
            <div class="text-[11px] text-slate-400 mt-0.5">Scheduled for today</div>
        </div>

        <div class="card p-4">
            <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Overdue Tasks</div>
            <div class="text-2xl font-bold {{ $overdueFollowups > 0 ? 'text-rose-600 font-black' : 'text-slate-800' }} mt-1">
                {{ $overdueFollowups }}
            </div>
            <div class="text-[11px] text-rose-600 mt-0.5">Needs immediate action</div>
        </div>

        <div class="card p-4">
            <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Gross Sales Closed</div>
            <div class="text-xl font-bold text-emerald-800 mt-1">₹{{ number_format($totalRevenue, 0) }}</div>
            <div class="text-[11px] text-slate-400 mt-0.5">{{ $ordersCount }} Orders Placed</div>
        </div>
    </div>

    <!-- 2 Columns: Recent Calls and Scheduled Follow-ups -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Calls -->
        <div class="card overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <h2 class="font-bold text-slate-800 text-sm">Recent Calls Placed by {{ $employee->name }}</h2>
                <a href="{{ route('calls.index', ['user_id' => $employee->id]) }}" class="text-xs font-semibold text-emerald-700 hover:underline">
                    All Calls →
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full text-xs">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Customer</th>
                            <th>Duration</th>
                            <th>Outcome</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentCalls as $call)
                            <tr>
                                <td class="font-mono text-slate-500">{{ $call->call_datetime->format('d M, h:i A') }}</td>
                                <td class="font-medium text-slate-800">{{ $call->customer?->name ?? 'Prospect' }}</td>
                                <td class="font-mono text-slate-600">{{ sprintf('%02d:%02d', floor($call->duration_seconds / 60), $call->duration_seconds % 60) }}</td>
                                <td>
                                    <span class="badge bg-slate-100 text-slate-700 text-[10px]">{{ $call->outcome }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center py-6 text-slate-400">No calls logged yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Follow-ups Queue -->
        <div class="card overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <h2 class="font-bold text-slate-800 text-sm">Upcoming Scheduled Follow-ups</h2>
                <a href="{{ route('followups.index') }}" class="text-xs font-semibold text-emerald-700 hover:underline">
                    View Follow-ups →
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full text-xs">
                    <thead>
                        <tr>
                            <th>Due Date</th>
                            <th>Target Contact</th>
                            <th>Priority</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentFollowups as $f)
                            <tr>
                                <td class="font-mono text-slate-500">{{ $f->due_date->format('d M Y') }}</td>
                                <td class="font-medium text-slate-800">{{ $f->customer?->name ?? $f->lead?->name }}</td>
                                <td>
                                    @php
                                        $fBadge = match($f->priority) {
                                            'high' => 'badge-danger',
                                            'medium' => 'badge-warning',
                                            default => 'badge-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $fBadge }} text-[10px] uppercase">{{ $f->priority }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-slate-100 text-slate-700 text-[10px]">{{ $f->status }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center py-6 text-slate-400">No pending follow-ups.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
