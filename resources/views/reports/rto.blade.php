@extends('layouts.app')

@section('title', 'RTO Intelligence & Non-Delivery Analytics — MantraHeal CRM')

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
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">RTO Intelligence & Courier Analytics</h1>
            <p class="text-sm text-slate-500">Analyze courier non-delivery patterns, high-risk states, and reverse transit costs</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('reports.rto', ['export' => 'csv']) }}" class="btn btn-secondary text-sm inline-flex items-center gap-1.5">
                <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export CSV
            </a>
            <a href="{{ route('rto.index') }}" class="btn btn-primary text-sm">
                RTO Operations
            </a>
        </div>
    </div>

    <!-- RTO KPIs -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="card p-5">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Overall RTO Rate</div>
            <div class="text-3xl font-black text-rose-700 mt-1">{{ $reports['rto_rate'] }}%</div>
            <div class="text-xs text-slate-400 mt-0.5">Ratio of undelivered vs dispatched orders</div>
        </div>

        <div class="card p-5">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Consignments Returned</div>
            <div class="text-3xl font-black text-slate-800 mt-1">{{ $reports['total_rtos'] }}</div>
            <div class="text-xs text-slate-400 mt-0.5">Returned parcels logged</div>
        </div>

        <div class="card p-5">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Tied Capital in Reverse Transit</div>
            <div class="text-3xl font-black text-slate-800 mt-1">₹{{ number_format($reports['total_rto_amount'], 2) }}</div>
            <div class="text-xs text-slate-400 mt-0.5">Gross GMV impacted by RTO</div>
        </div>
    </div>

    <!-- 2 Columns: Courier & Geographic Breakdown -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Courier Performance -->
        <div class="card overflow-hidden">
            <div class="p-4 border-b border-slate-100">
                <h2 class="font-bold text-slate-800 text-sm">RTO Distribution by Courier Partner</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full text-sm">
                    <thead>
                        <tr>
                            <th>Courier Partner</th>
                            <th class="text-center">RTO Count</th>
                            <th class="text-right">Tied Value (₹)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($reports['by_courier'] as $c)
                            <tr>
                                <td class="font-medium text-slate-800">{{ $c->courier_name }}</td>
                                <td class="text-center font-bold text-rose-700">{{ $c->count }}</td>
                                <td class="text-right font-bold text-slate-800">₹{{ number_format($c->amount ?? 0, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center py-6 text-slate-400 text-xs">No courier RTO records.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- State-Wise Geographic Breakdown -->
        <div class="card overflow-hidden">
            <div class="p-4 border-b border-slate-100">
                <h2 class="font-bold text-slate-800 text-sm">Top 10 High RTO States</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full text-sm">
                    <thead>
                        <tr>
                            <th>State</th>
                            <th class="text-center">RTO Count</th>
                            <th class="text-right">Impacted Value (₹)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($reports['by_state'] as $st)
                            <tr>
                                <td class="font-medium text-slate-800">{{ $st->state ?: 'Unknown State' }}</td>
                                <td class="text-center font-bold text-slate-800">{{ $st->count }}</td>
                                <td class="text-right font-bold text-slate-800">₹{{ number_format($st->amount ?? 0, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center py-6 text-slate-400 text-xs">No state RTO records.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- RTO Reason Breakdown -->
    <div class="card overflow-hidden">
        <div class="p-4 border-b border-slate-100">
            <h2 class="font-bold text-slate-800 text-sm">Non-Delivery Reason Breakdown</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="table w-full text-sm">
                <thead>
                    <tr>
                        <th>Non-Delivery Reason</th>
                        <th class="text-center">Occurrences</th>
                        <th class="text-center">Percentage</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($reports['by_reason'] as $r)
                        @php
                            $pct = $reports['total_rtos'] > 0 ? round(($r->count / $reports['total_rtos']) * 100, 1) : 0;
                        @endphp
                        <tr>
                            <td class="font-medium text-slate-800">{{ $r->reason }}</td>
                            <td class="text-center font-bold text-slate-700">{{ $r->count }}</td>
                            <td class="text-center font-mono text-xs text-slate-500">{{ $pct }}%</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center py-6 text-slate-400 text-xs">No reason records logged.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
