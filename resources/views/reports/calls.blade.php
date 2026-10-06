@extends('layouts.app')

@section('title', 'Sales Call Analytics & Agent Performance — MantraHeal CRM')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xs text-slate-500">Business Intelligence</span>
                <span class="text-slate-300">/</span>
                <span class="text-xs text-slate-500">Reports</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Sales Call & Agent Productivity Report</h1>
            <p class="text-sm text-slate-500">Track outbound call volumes, connection rates, average talk time, and outcome metrics</p>
        </div>
        <div>
            <a href="{{ route('calls.index') }}" class="btn btn-secondary text-sm">
                Sales Call Register
            </a>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card p-5">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Calls Logged</div>
            <div class="text-2xl font-black text-slate-800 mt-1">{{ $reports['total_calls'] }}</div>
            <div class="text-xs text-slate-400 mt-0.5">Telephonic customer touchpoints</div>
        </div>

        <div class="card p-5">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Connected Calls</div>
            <div class="text-2xl font-black text-emerald-800 mt-1">{{ $reports['connected_calls'] }}</div>
            @php
                $connRate = $reports['total_calls'] > 0 ? round(($reports['connected_calls'] / $reports['total_calls']) * 100, 1) : 0;
            @endphp
            <div class="text-xs text-emerald-600 font-semibold mt-0.5">{{ $connRate }}% Connection Rate</div>
        </div>

        <div class="card p-5">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Cumulative Talk Time</div>
            @php
                $totalMins = floor($reports['total_duration'] / 60);
            @endphp
            <div class="text-2xl font-black text-slate-800 mt-1">{{ $totalMins }} mins</div>
            <div class="text-xs text-slate-400 mt-0.5">Total agent telephonic duration</div>
        </div>

        <div class="card p-5">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Average Call Duration</div>
            <div class="text-2xl font-black text-emerald-800 mt-1">{{ $reports['avg_duration'] }}</div>
            <div class="text-xs text-slate-400 mt-0.5">MM:SS per customer conversation</div>
        </div>
    </div>

    <!-- 2 Columns: Outcomes & Agent Performance -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Call Outcomes -->
        <div class="card overflow-hidden">
            <div class="p-4 border-b border-slate-100">
                <h2 class="font-bold text-slate-800 text-sm">Call Outcome Distribution</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full text-sm">
                    <thead>
                        <tr>
                            <th>Call Outcome</th>
                            <th class="text-center">Count</th>
                            <th class="text-center">Ratio %</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($reports['by_outcome'] as $out)
                            @php
                                $pct = $reports['total_calls'] > 0 ? round(($out->count / $reports['total_calls']) * 100, 1) : 0;
                            @endphp
                            <tr>
                                <td class="font-medium text-slate-800">{{ $out->outcome }}</td>
                                <td class="text-center font-bold text-slate-700">{{ $out->count }}</td>
                                <td class="text-center font-mono text-xs text-slate-500">{{ $pct }}%</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center py-6 text-slate-400 text-xs">No call outcomes logged</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Executive Activity -->
        <div class="card overflow-hidden">
            <div class="p-4 border-b border-slate-100">
                <h2 class="font-bold text-slate-800 text-sm">Sales Executive Activity Leaderboard</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full text-sm">
                    <thead>
                        <tr>
                            <th>Sales Executive</th>
                            <th class="text-center">Calls Made</th>
                            <th class="text-center">Leads Handled</th>
                            <th class="text-center">Orders Converted</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($reports['by_employee'] as $emp)
                            <tr>
                                <td class="font-medium text-slate-800">{{ $emp->name }}</td>
                                <td class="text-center font-bold text-slate-800">{{ $emp->sales_calls_count }}</td>
                                <td class="text-center text-slate-600">{{ $emp->leads_count }}</td>
                                <td class="text-center font-bold text-emerald-800">{{ $emp->orders_count }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center py-6 text-slate-400 text-xs">No sales executives active</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
