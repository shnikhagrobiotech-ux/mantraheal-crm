@extends('layouts.app')

@section('title', "{$supplier->name} — MantraHeal CRM")

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('suppliers.index') }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Suppliers
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-xs text-slate-500 font-mono">{{ $supplier->supplier_code }}</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">{{ $supplier->name }}</h1>
                <span class="badge badge-success text-xs capitalize">{{ $supplier->status }}</span>
            </div>
        </div>

        <div>
            <a href="{{ route('purchase-orders.create', ['supplier_id' => $supplier->id]) }}" class="btn btn-primary text-sm inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Issue Purchase Order
            </a>
        </div>
    </div>

    <!-- Vendor Information Card -->
    <div class="card p-6 grid grid-cols-1 md:grid-cols-4 gap-6 text-sm">
        <div>
            <div class="text-xs text-slate-400 uppercase font-semibold">Contact Person</div>
            <div class="font-semibold text-slate-800 mt-1">{{ $supplier->contact_person ?? 'Main Office' }}</div>
            <div class="text-slate-500 text-xs">{{ $supplier->phone }}</div>
            <div class="text-slate-500 text-xs">{{ $supplier->email ?? 'No email' }}</div>
        </div>
        <div>
            <div class="text-xs text-slate-400 uppercase font-semibold">Tax & GSTIN</div>
            <div class="font-mono font-medium text-slate-800 mt-1">{{ $supplier->gstin ?? 'Unregistered' }}</div>
            <div class="text-slate-500 text-xs">Terms: {{ $supplier->payment_terms ?? 'Net 30 Days' }}</div>
        </div>
        <div>
            <div class="text-xs text-slate-400 uppercase font-semibold">Premises / Address</div>
            <div class="text-slate-700 mt-1">{{ $supplier->address }}</div>
            <div class="text-slate-500 text-xs">{{ $supplier->city }}, {{ $supplier->state }} - {{ $supplier->pincode }}</div>
        </div>
        <div>
            <div class="text-xs text-slate-400 uppercase font-semibold">Total Procurement Value</div>
            <div class="text-2xl font-bold text-emerald-800 mt-0.5">
                ₹{{ number_format($supplier->purchaseOrders->sum('total_amount'), 2) }}
            </div>
            <div class="text-xs text-slate-500">{{ $supplier->purchaseOrders->count() }} Purchase Orders</div>
        </div>
    </div>

    <!-- Purchase Orders History -->
    <div class="card overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold text-slate-800 text-sm">Purchase Orders Placed with this Vendor</h2>
            <span class="text-xs text-slate-500">{{ $supplier->purchaseOrders->count() }} POs</span>
        </div>
        <div class="overflow-x-auto">
            <table class="table w-full text-sm">
                <thead>
                    <tr>
                        <th>PO Number</th>
                        <th>Date</th>
                        <th>Depot</th>
                        <th>Items Count</th>
                        <th class="text-right">Total (₹)</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($supplier->purchaseOrders as $po)
                        <tr>
                            <td class="font-mono text-xs font-bold text-emerald-800">
                                <a href="{{ route('purchase-orders.show', $po) }}" class="hover:underline">
                                    {{ $po->po_number }}
                                </a>
                            </td>
                            <td class="text-xs text-slate-500">{{ $po->po_date->format('d M Y') }}</td>
                            <td class="text-xs text-slate-600">{{ $po->warehouse?->name ?? 'Central' }}</td>
                            <td class="text-xs text-slate-600">{{ $po->items->count() }} line items</td>
                            <td class="text-right font-semibold text-slate-800">₹{{ number_format($po->total_amount, 2) }}</td>
                            <td>
                                @php
                                    $pBadge = match($po->status) {
                                        'received' => 'badge-success',
                                        'partially_received' => 'badge-warning',
                                        'cancelled' => 'badge-danger',
                                        default => 'badge-info'
                                    };
                                @endphp
                                <span class="badge {{ $pBadge }} text-xs capitalize">{{ str_replace('_', ' ', $po->status) }}</span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('purchase-orders.show', $po) }}" class="btn btn-secondary text-xs py-1 px-2.5">
                                    View PO
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-slate-400 text-xs">
                                No purchase orders placed with this supplier yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
