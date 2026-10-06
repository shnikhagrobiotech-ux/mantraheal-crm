@extends('layouts.app')

@section('title', 'Purchase Orders — MantraHeal CRM')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xs text-slate-500">Procurement</span>
                <span class="text-slate-300">/</span>
                <span class="text-xs text-slate-500">Purchase Orders</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Purchase Orders</h1>
            <p class="text-sm text-slate-500">Issue and track manufacturing procurement orders with herbal suppliers</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('grn.index') }}" class="btn btn-secondary text-sm inline-flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                GRN Register
            </a>
            <a href="{{ route('purchase-orders.create') }}" class="btn btn-primary text-sm inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Create Purchase Order
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card p-4">
        <form method="GET" action="{{ route('purchase-orders.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <select name="supplier_id" class="form-control text-sm w-full">
                    <option value="">All Suppliers</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="status" class="form-control text-sm w-full">
                    <option value="">All Statuses</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                    <option value="partially_received" {{ request('status') == 'partially_received' ? 'selected' : '' }}>Partially Received</option>
                    <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Received</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div>
                <select name="warehouse_id" class="form-control text-sm w-full">
                    <option value="">All Receiving Warehouses</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="btn btn-primary text-sm py-2 px-4 flex-1">Filter</button>
                <a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary text-sm py-2 px-3">Reset</a>
            </div>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table w-full text-sm">
                <thead>
                    <tr>
                        <th>PO Number</th>
                        <th>PO Date</th>
                        <th>Supplier</th>
                        <th>Destination Warehouse</th>
                        <th>Expected Delivery</th>
                        <th class="text-right">Total Amount (₹)</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($orders as $po)
                        <tr class="hover:bg-slate-50/75 transition-colors">
                            <td class="font-mono text-xs font-bold text-emerald-800 whitespace-nowrap">
                                <a href="{{ route('purchase-orders.show', $po) }}" class="hover:underline">
                                    {{ $po->po_number }}
                                </a>
                            </td>
                            <td class="text-xs text-slate-500 whitespace-nowrap">
                                {{ $po->po_date->format('d M Y') }}
                            </td>
                            <td>
                                <div class="font-medium text-slate-800">{{ $po->supplier?->name }}</div>
                                <div class="text-xs text-slate-400 font-mono">{{ $po->supplier?->supplier_code }}</div>
                            </td>
                            <td class="text-xs text-slate-600 whitespace-nowrap">
                                {{ $po->warehouse?->name ?? 'Central Depot' }}
                            </td>
                            <td class="text-xs text-slate-500 whitespace-nowrap">
                                {{ $po->expected_delivery_date ? $po->expected_delivery_date->format('d M Y') : '—' }}
                            </td>
                            <td class="text-right font-bold text-slate-800 whitespace-nowrap">
                                ₹{{ number_format($po->total_amount, 2) }}
                            </td>
                            <td>
                                @php
                                    $poBadge = match($po->status) {
                                        'received' => 'badge-success',
                                        'partially_received' => 'badge-warning',
                                        'cancelled' => 'badge-danger',
                                        default => 'badge-info'
                                    };
                                @endphp
                                <span class="badge {{ $poBadge }} text-xs capitalize whitespace-nowrap">
                                    {{ str_replace('_', ' ', $po->status) }}
                                </span>
                            </td>
                            <td class="text-right whitespace-nowrap space-x-1">
                                <a href="{{ route('purchase-orders.show', $po) }}" class="btn btn-secondary text-xs py-1 px-2.5">
                                    View
                                </a>
                                @if(in_array($po->status, ['sent', 'partially_received', 'draft']))
                                    <a href="{{ route('grn.create', ['purchase_order_id' => $po->id]) }}" class="btn btn-primary text-xs py-1 px-2.5">
                                        Receive GRN
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-12 text-slate-400 text-sm">
                                No purchase orders found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
