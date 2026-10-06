@extends('layouts.app')

@section('title', 'Returns Management — MantraHeal CRM')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xs text-slate-500">Post-Order</span>
                <span class="text-slate-300">/</span>
                <span class="text-xs text-slate-500">Returns</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Customer Returns Management</h1>
            <p class="text-sm text-slate-500">Track reverse logistics: Request → Approval → Pickup → QC → Restock / Refund</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('refunds.index') }}" class="btn btn-secondary text-sm inline-flex items-center gap-1.5">
                Refunds Ledger
            </a>
            <a href="{{ route('rto.index') }}" class="btn btn-secondary text-sm inline-flex items-center gap-1.5 text-rose-700">
                RTO Tracking
            </a>
        </div>
    </div>

    <!-- Workflow Progress Visual Banner -->
    <div class="card p-4 bg-slate-50 border border-slate-200">
        <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Standard Return Workflow Lifecycle</div>
        <div class="flex flex-wrap items-center gap-2 text-xs font-medium">
            <span class="px-2.5 py-1 bg-white border border-slate-200 rounded-lg text-slate-700 shadow-2xs">1. Requested</span>
            <span class="text-slate-300">→</span>
            <span class="px-2.5 py-1 bg-white border border-slate-200 rounded-lg text-slate-700 shadow-2xs">2. Approved</span>
            <span class="text-slate-300">→</span>
            <span class="px-2.5 py-1 bg-white border border-slate-200 rounded-lg text-slate-700 shadow-2xs">3. Pickup Scheduled</span>
            <span class="text-slate-300">→</span>
            <span class="px-2.5 py-1 bg-white border border-slate-200 rounded-lg text-slate-700 shadow-2xs">4. Received at Depot</span>
            <span class="text-slate-300">→</span>
            <span class="px-2.5 py-1 bg-emerald-100 border border-emerald-300 rounded-lg text-emerald-800 font-bold shadow-2xs">5. QC Check</span>
            <span class="text-slate-300">→</span>
            <span class="px-2.5 py-1 bg-white border border-slate-200 rounded-lg text-slate-700 shadow-2xs">6. Restock & Refund</span>
        </div>
    </div>

    <!-- Filters -->
    <div class="card p-4">
        <form method="GET" action="{{ route('returns.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <select name="status" class="form-control text-sm w-full">
                    <option value="">All Return Stages</option>
                    @foreach($statuses as $st)
                        <option value="{{ $st }}" {{ request('status') == $st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="qc_status" class="form-control text-sm w-full">
                    <option value="">All QC Statuses</option>
                    <option value="Pending" {{ request('qc_status') == 'Pending' ? 'selected' : '' }}>QC Pending</option>
                    <option value="Passed" {{ request('qc_status') == 'Passed' ? 'selected' : '' }}>QC Passed (Restocked)</option>
                    <option value="Failed" {{ request('qc_status') == 'Failed' ? 'selected' : '' }}>QC Failed (Damaged)</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="btn btn-primary text-sm py-2 px-4 flex-1">Filter</button>
                <a href="{{ route('returns.index') }}" class="btn btn-secondary text-sm py-2 px-3">Reset</a>
            </div>
        </form>
    </div>

    <!-- Returns Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table w-full text-sm">
                <thead>
                    <tr>
                        <th>Return #</th>
                        <th>Date</th>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Reason</th>
                        <th>Action Requested</th>
                        <th>QC Status</th>
                        <th>Lifecycle Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($returns as $return)
                        <tr class="hover:bg-slate-50/75 transition-colors">
                            <td class="font-mono text-xs font-bold text-rose-800 whitespace-nowrap">
                                <a href="{{ route('returns.show', $return) }}" class="hover:underline">
                                    {{ $return->return_number }}
                                </a>
                            </td>
                            <td class="text-xs text-slate-500 whitespace-nowrap">
                                {{ $return->return_date->format('d M Y') }}
                            </td>
                            <td class="font-mono text-xs whitespace-nowrap">
                                <a href="{{ route('orders.show', $return->order_id) }}" class="text-emerald-700 hover:underline">
                                    {{ $return->order?->order_number }}
                                </a>
                            </td>
                            <td class="whitespace-nowrap">
                                <div class="font-medium text-slate-800">{{ $return->customer?->name }}</div>
                                <div class="text-xs text-slate-400">{{ $return->customer?->phone }}</div>
                            </td>
                            <td class="text-xs text-slate-600">
                                {{ $return->reason }}
                            </td>
                            <td>
                                <span class="badge bg-slate-100 text-slate-700 text-xs">
                                    {{ $return->refund_action }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $qcBadge = match($return->qc_status) {
                                        'Passed' => 'badge-success',
                                        'Failed' => 'badge-danger',
                                        default => 'badge-warning'
                                    };
                                @endphp
                                <span class="badge {{ $qcBadge }} text-xs">{{ $return->qc_status }}</span>
                            </td>
                            <td>
                                <span class="badge bg-emerald-50 text-emerald-800 text-xs font-medium">
                                    {{ $return->status }}
                                </span>
                            </td>
                            <td class="text-right whitespace-nowrap">
                                <a href="{{ route('returns.show', $return) }}" class="btn btn-secondary text-xs py-1 px-2.5">
                                    View / QC
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-12 text-slate-400 text-sm">
                                No return requests found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($returns->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $returns->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
