@extends('layouts.app')

@section('title', 'Customer Analytics & Retention Report — MantraHeal CRM')

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
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Customer Retention & LTV Report</h1>
            <p class="text-sm text-slate-500">Analyze repeat purchase rates, lifetime customer value, and top spenders</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('reports.customers', ['export' => 'csv']) }}" class="btn btn-secondary text-sm inline-flex items-center gap-1.5">
                <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export CSV
            </a>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card p-5">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Customers</div>
            <div class="text-2xl font-black text-slate-800 mt-1">{{ $reports['total_customers'] }}</div>
            <div class="text-xs text-slate-400 mt-0.5">Registered database profiles</div>
        </div>

        <div class="card p-5">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Repeat Purchase Rate</div>
            <div class="text-2xl font-black text-emerald-800 mt-1">{{ $reports['repeat_rate'] }}%</div>
            <div class="text-xs text-slate-400 mt-0.5">{{ $reports['repeat_customers'] }} returning buyers</div>
        </div>

        <div class="card p-5">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Cumulative LTV Spend</div>
            <div class="text-2xl font-black text-slate-800 mt-1">₹{{ number_format($reports['total_spend'], 2) }}</div>
            <div class="text-xs text-slate-400 mt-0.5">All customer historical purchases</div>
        </div>

        <div class="card p-5">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Average Customer LTV</div>
            <div class="text-2xl font-black text-emerald-800 mt-1">₹{{ number_format($reports['overall_aov'], 2) }}</div>
            <div class="text-xs text-slate-400 mt-0.5">Mean revenue per order</div>
        </div>
    </div>

    <!-- Cohort Comparison -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="card p-6 flex items-center justify-between">
            <div>
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">New Customers (First Order)</div>
                <div class="text-3xl font-black text-slate-800 mt-1">{{ $reports['new_customers'] }}</div>
                <p class="text-xs text-slate-400 mt-1">Target for WhatsApp post-delivery replenishment reminders</p>
            </div>
            <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center font-bold text-slate-600 text-lg">
                {{ $reports['total_customers'] > 0 ? round(($reports['new_customers'] / $reports['total_customers']) * 100) : 0 }}%
            </div>
        </div>

        <div class="card p-6 flex items-center justify-between bg-emerald-50/40 border border-emerald-200/80">
            <div>
                <div class="text-xs font-semibold text-emerald-800 uppercase tracking-wider">Loyal Repeat Customers (&gt;1 Order)</div>
                <div class="text-3xl font-black text-emerald-900 mt-1">{{ $reports['repeat_customers'] }}</div>
                <p class="text-xs text-emerald-700 mt-1">Prime candidates for seasonal Ayurvedic wellness bundles</p>
            </div>
            <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-lg">
                {{ $reports['repeat_rate'] }}%
            </div>
        </div>
    </div>

    <!-- Top Spenders Leaderboard -->
    <div class="card overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold text-slate-800 text-sm">Top 10 High-Value Customers (LTV Leaderboard)</h2>
            <span class="text-xs text-slate-500">Highest cumulative order value</span>
        </div>
        <div class="overflow-x-auto">
            <table class="table w-full text-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Customer Name</th>
                        <th>Phone</th>
                        <th>City / State</th>
                        <th class="text-center">Orders Placed</th>
                        <th class="text-right">Total Spend (₹)</th>
                        <th class="text-right">Average Order (₹)</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($reports['top_customers'] as $idx => $c)
                        <tr class="hover:bg-slate-50/75 transition-colors">
                            <td class="font-mono text-xs text-slate-400 font-bold">{{ $idx + 1 }}</td>
                            <td>
                                <div class="font-semibold text-slate-800">
                                    <a href="{{ route('customers.show', $c) }}" class="hover:text-emerald-700">
                                        {{ $c->name }}
                                    </a>
                                </div>
                                <div class="text-xs text-slate-400 font-mono">{{ $c->customer_code }}</div>
                            </td>
                            <td class="text-xs font-mono text-slate-700">{{ $c->phone }}</td>
                            <td class="text-xs text-slate-600">{{ $c->city }}, {{ $c->state }}</td>
                            <td class="text-center font-bold text-slate-800">{{ $c->total_orders }}</td>
                            <td class="text-right font-black text-emerald-800">₹{{ number_format($c->total_spend, 2) }}</td>
                            <td class="text-right text-xs font-semibold text-slate-700">₹{{ number_format($c->average_order_value, 2) }}</td>
                            <td class="text-right">
                                <a href="{{ route('customers.show', $c) }}" class="btn btn-secondary text-xs py-1 px-2.5">
                                    Customer 360°
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-slate-400 text-xs">No customer orders recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
