@extends('layouts.app')

@section('title', 'Sales Performance Report — MantraHeal CRM')

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
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Sales Performance Analytics</h1>
            <p class="text-sm text-slate-500">Revenue, average order value, channel performance, and geographic distribution</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('reports.sales', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn btn-secondary text-sm inline-flex items-center gap-1.5">
                <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export CSV
            </a>
        </div>
    </div>

    <!-- Date Range Filter Bar -->
    <div class="card p-4">
        <form method="GET" action="{{ route('reports.sales') }}" class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2">
                <label class="text-xs font-semibold text-slate-600">From:</label>
                <input type="date" name="start_date" value="{{ $startDate ?? date('Y-m-d', strtotime('-30 days')) }}" class="form-control text-sm">
            </div>
            <div class="flex items-center gap-2">
                <label class="text-xs font-semibold text-slate-600">To:</label>
                <input type="date" name="end_date" value="{{ $endDate ?? date('Y-m-d') }}" class="form-control text-sm">
            </div>
            <button type="submit" class="btn btn-primary text-sm py-2 px-4">Generate Report</button>
            <a href="{{ route('reports.sales') }}" class="btn btn-secondary text-sm py-2 px-3">Reset</a>
        </form>
    </div>

    <!-- High Level KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card p-5">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Gross Sales Revenue</div>
            <div class="text-2xl font-black text-emerald-800 mt-1">₹{{ number_format($reports['total_sales'], 2) }}</div>
            <div class="text-xs text-slate-400 mt-0.5">Delivered & confirmed orders</div>
        </div>

        <div class="card p-5">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Orders Placed</div>
            <div class="text-2xl font-black text-slate-800 mt-1">{{ $reports['total_orders'] }}</div>
            <div class="text-xs text-slate-400 mt-0.5">{{ $reports['delivered_orders'] }} successfully delivered</div>
        </div>

        <div class="card p-5">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Average Order Value (AOV)</div>
            <div class="text-2xl font-black text-slate-800 mt-1">₹{{ number_format($reports['aov'], 2) }}</div>
            <div class="text-xs text-slate-400 mt-0.5">Average ticket size per order</div>
        </div>

        <div class="card p-5">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Fulfillment Rate</div>
            @php
                $fulfillRate = $reports['total_orders'] > 0 ? round(($reports['delivered_orders'] / $reports['total_orders']) * 100, 1) : 0;
            @endphp
            <div class="text-2xl font-black text-emerald-800 mt-1">{{ $fulfillRate }}%</div>
            <div class="text-xs text-slate-400 mt-0.5">Delivered ratio</div>
        </div>
    </div>

    <!-- 2 Column Breakdown: Channels & Geographic -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Channel Performance -->
        <div class="card overflow-hidden">
            <div class="p-4 border-b border-slate-100">
                <h2 class="font-bold text-slate-800 text-sm">Revenue by Sales Channel</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full text-sm">
                    <thead>
                        <tr>
                            <th>Channel</th>
                            <th class="text-center">Orders</th>
                            <th class="text-right">Revenue (₹)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($reports['by_channel'] as $ch => $stats)
                            <tr>
                                <td class="font-medium text-slate-800 capitalize">{{ $ch ?: 'Direct CRM' }}</td>
                                <td class="text-center font-semibold text-slate-700">{{ $stats['count'] }}</td>
                                <td class="text-right font-bold text-emerald-800">₹{{ number_format($stats['revenue'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center py-6 text-slate-400 text-xs">No channel data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- State Performance -->
        <div class="card overflow-hidden">
            <div class="p-4 border-b border-slate-100">
                <h2 class="font-bold text-slate-800 text-sm">Top States by Order Volume</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full text-sm">
                    <thead>
                        <tr>
                            <th>State</th>
                            <th class="text-center">Orders</th>
                            <th class="text-right">Revenue (₹)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($reports['by_state'] as $st => $stats)
                            <tr>
                                <td class="font-medium text-slate-800">{{ $st ?: 'Unassigned State' }}</td>
                                <td class="text-center font-semibold text-slate-700">{{ $stats['count'] }}</td>
                                <td class="text-right font-bold text-slate-800">₹{{ number_format($stats['revenue'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center py-6 text-slate-400 text-xs">No state data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Daily Trend Table -->
    <div class="card overflow-hidden">
        <div class="p-4 border-b border-slate-100">
            <h2 class="font-bold text-slate-800 text-sm">Daily Sales Velocity Breakdown</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="table w-full text-sm">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th class="text-center">Orders Count</th>
                        <th class="text-right">Daily Revenue (₹)</th>
                        <th class="text-right">Daily AOV (₹)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($reports['daily_trend'] as $day)
                        @php
                            $dAov = $day->total_orders > 0 ? $day->total_revenue / $day->total_orders : 0;
                        @endphp
                        <tr class="hover:bg-slate-50/75 transition-colors">
                            <td class="font-mono text-xs text-slate-600 font-medium">
                                {{ \Carbon\Carbon::parse($day->date)->format('d M Y (D)') }}
                            </td>
                            <td class="text-center font-semibold text-slate-800">{{ $day->total_orders }}</td>
                            <td class="text-right font-bold text-emerald-800">₹{{ number_format($day->total_revenue, 2) }}</td>
                            <td class="text-right text-xs text-slate-600">₹{{ number_format($dAov, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-8 text-slate-400 text-sm">
                                No sales recorded in the selected date window.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
