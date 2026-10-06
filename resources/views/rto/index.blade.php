@extends('layouts.app')

@section('title', 'RTO (Return to Origin) Analytics & Tracking — MantraHeal CRM')

@section('content')
<div class="space-y-6" x-data="{ showUpdateModal: false, selectedRto: null }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xs text-slate-500">Logistics</span>
                <span class="text-slate-300">/</span>
                <span class="text-xs text-slate-500">RTO</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">RTO (Return to Origin) Management</h1>
            <p class="text-sm text-slate-500">Track undelivered shipments, courier non-delivery reasons, and warehouse intake</p>
        </div>
        <div>
            <a href="{{ route('reports.rto') }}" class="btn btn-secondary text-sm inline-flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                RTO Analytics Report
            </a>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card p-4">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total RTOs Logged</div>
            <div class="text-2xl font-bold text-rose-700 mt-1">{{ $totalRtos }}</div>
            <div class="text-xs text-slate-400 mt-0.5">Undelivered consignments</div>
        </div>

        <div class="card p-4">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Tied Capital in RTO</div>
            <div class="text-2xl font-bold text-slate-800 mt-1">₹{{ number_format($totalRtoAmount, 2) }}</div>
            <div class="text-xs text-slate-400 mt-0.5">Invoice value in reverse transit</div>
        </div>

        <div class="card p-4 sm:col-span-2">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Courier Performance (RTO Rate)</div>
            <div class="flex items-center gap-3 overflow-x-auto text-xs">
                @forelse($courierStats as $cs)
                    <div class="bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-200">
                        <span class="font-bold text-slate-800">{{ $cs->courier_name }}:</span>
                        <span class="text-rose-600 font-semibold ml-1">{{ $cs->count }} RTOs</span>
                        <span class="text-slate-400 font-mono text-[11px] block">₹{{ number_format($cs->total, 0) }}</span>
                    </div>
                @empty
                    <span class="text-slate-400">No courier statistics recorded yet</span>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card p-4">
        <form method="GET" action="{{ route('rto.index') }}" class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-4 gap-3">
            <div>
                <select name="courier_name" class="form-control text-sm w-full">
                    <option value="">All Courier Partners</option>
                    @foreach($couriers as $c)
                        <option value="{{ $c }}" {{ request('courier_name') == $c ? 'selected' : '' }}>{{ $c }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="reason" class="form-control text-sm w-full">
                    <option value="">All RTO Reasons</option>
                    @foreach($reasons as $r)
                        <option value="{{ $r }}" {{ request('reason') == $r ? 'selected' : '' }}>{{ $r }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <input type="text" name="state" value="{{ request('state') }}" placeholder="Filter by State (e.g. Bihar, UP)..." class="form-control text-sm w-full">
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="btn btn-primary text-sm py-2 px-4 flex-1">Filter</button>
                <a href="{{ route('rto.index') }}" class="btn btn-secondary text-sm py-2 px-3">Reset</a>
            </div>
        </form>
    </div>

    <!-- RTO Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table w-full text-sm">
                <thead>
                    <tr>
                        <th>RTO Code</th>
                        <th>Order #</th>
                        <th>Initiated Date</th>
                        <th>Customer</th>
                        <th>Courier & AWB</th>
                        <th>State / City</th>
                        <th>RTO Reason</th>
                        <th class="text-right">Value (₹)</th>
                        <th>Reverse Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($rtos as $rto)
                        <tr class="hover:bg-slate-50/75 transition-colors">
                            <td class="font-mono text-xs font-bold text-rose-800 whitespace-nowrap">
                                {{ $rto->rto_code }}
                            </td>
                            <td class="font-mono text-xs whitespace-nowrap">
                                <a href="{{ route('orders.show', $rto->order_id) }}" class="text-emerald-700 hover:underline">
                                    {{ $rto->order?->order_number }}
                                </a>
                            </td>
                            <td class="text-xs text-slate-500 whitespace-nowrap">
                                {{ $rto->rto_initiated_date->format('d M Y') }}
                            </td>
                            <td class="whitespace-nowrap">
                                <div class="font-medium text-slate-800">{{ $rto->customer?->name }}</div>
                                <div class="text-xs text-slate-400">{{ $rto->customer?->phone }}</div>
                            </td>
                            <td class="text-xs whitespace-nowrap">
                                <div class="font-medium text-slate-700">{{ $rto->courier_name }}</div>
                                <div class="font-mono text-[11px] text-slate-500">{{ $rto->tracking_number }}</div>
                            </td>
                            <td class="text-xs text-slate-600 whitespace-nowrap">
                                {{ $rto->state }}
                            </td>
                            <td class="text-xs text-slate-600 max-w-xs truncate" title="{{ $rto->reason }}">
                                {{ $rto->reason }}
                            </td>
                            <td class="text-right font-bold text-slate-800 whitespace-nowrap">
                                ₹{{ number_format($rto->total_amount, 2) }}
                            </td>
                            <td class="whitespace-nowrap">
                                @php
                                    $rBadge = match($rto->status) {
                                        'QC Completed' => 'badge-success',
                                        'Received at Warehouse' => 'badge-info',
                                        default => 'badge-warning'
                                    };
                                @endphp
                                <span class="badge {{ $rBadge }} text-[11px]">{{ $rto->status }}</span>
                            </td>
                            <td class="text-right whitespace-nowrap">
                                <button type="button" @click="selectedRto = {{ json_encode($rto) }}; showUpdateModal = true" class="btn btn-secondary text-xs py-1 px-2.5 text-emerald-700 hover:bg-emerald-50">
                                    Update
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-12 text-slate-400 text-sm">
                                No RTO records logged.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($rtos->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $rtos->links() }}
            </div>
        @endif
    </div>

    <!-- Update RTO Modal -->
    <div x-show="showUpdateModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 flex items-center justify-center p-4" style="display: none;">
        <div class="card max-w-md w-full p-6 space-y-4 shadow-2xl bg-white" @click.away="showUpdateModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-800">Update Reverse Logistics Status</h3>
                <button type="button" @click="showUpdateModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <form :action="'{{ url('rto') }}/' + (selectedRto ? selectedRto.id : '')" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Reverse Transit Status *</label>
                    <select name="status" required class="form-control w-full text-sm">
                        <option value="In Transit">In Transit (Returning to Hub)</option>
                        <option value="Received at Warehouse">Received at Warehouse</option>
                        <option value="QC Completed">QC Completed (Restocked / Written Off)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Receiving Depot Warehouse</label>
                    <select name="received_warehouse_id" class="form-control w-full text-sm">
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->city }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Delivered to Depot Date</label>
                    <input type="date" name="rto_delivered_date" value="{{ date('Y-m-d') }}" class="form-control w-full text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Logistics Notes</label>
                    <textarea name="notes" rows="2" placeholder="Returned intact in sealed envelope..." class="form-control w-full text-sm"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showUpdateModal = false" class="btn btn-secondary text-xs">Cancel</button>
                    <button type="submit" class="btn btn-primary text-xs">Save Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
