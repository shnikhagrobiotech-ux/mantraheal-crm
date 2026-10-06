@extends('layouts.app')

@section('title', "PO #{$purchaseOrder->po_number} — MantraHeal CRM")

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('purchase-orders.index') }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Purchase Orders
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-xs text-slate-500 font-mono">{{ $purchaseOrder->po_number }}</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Purchase Order #{{ $purchaseOrder->po_number }}</h1>
                @php
                    $poBadge = match($purchaseOrder->status) {
                        'received' => 'badge-success',
                        'partially_received' => 'badge-warning',
                        'cancelled' => 'badge-danger',
                        default => 'badge-info'
                    };
                @endphp
                <span class="badge {{ $poBadge }} text-xs capitalize">{{ str_replace('_', ' ', $purchaseOrder->status) }}</span>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @if(in_array($purchaseOrder->status, ['draft', 'sent', 'partially_received']))
                <a href="{{ route('grn.create', ['purchase_order_id' => $purchaseOrder->id]) }}" class="btn btn-primary text-sm inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Receive GRN (Intake Goods)
                </a>
            @endif
        </div>
    </div>

    <!-- PO Details Header Card -->
    <div class="card p-6 grid grid-cols-1 md:grid-cols-4 gap-6 text-sm">
        <div>
            <div class="text-xs text-slate-400 uppercase font-semibold">Supplier Vendor</div>
            <div class="font-bold text-slate-800 mt-1">
                <a href="{{ route('suppliers.show', $purchaseOrder->supplier_id) }}" class="hover:text-emerald-700">
                    {{ $purchaseOrder->supplier?->name }}
                </a>
            </div>
            <div class="text-xs text-slate-500">{{ $purchaseOrder->supplier?->phone }}</div>
            <div class="text-xs text-slate-500 font-mono">GSTIN: {{ $purchaseOrder->supplier?->gstin ?? 'Unregistered' }}</div>
        </div>

        <div>
            <div class="text-xs text-slate-400 uppercase font-semibold">Destination Depot</div>
            <div class="font-medium text-slate-800 mt-1">{{ $purchaseOrder->warehouse?->name ?? 'Central Depot' }}</div>
            <div class="text-xs text-slate-500">{{ $purchaseOrder->warehouse?->city }}</div>
        </div>

        <div>
            <div class="text-xs text-slate-400 uppercase font-semibold">Important Dates</div>
            <div class="text-slate-700 mt-1">Issued: <strong>{{ $purchaseOrder->po_date->format('d M Y') }}</strong></div>
            <div class="text-xs text-slate-500">Expected: {{ $purchaseOrder->expected_delivery_date ? $purchaseOrder->expected_delivery_date->format('d M Y') : 'Open' }}</div>
        </div>

        <div>
            <div class="text-xs text-slate-400 uppercase font-semibold">Total PO Commitment</div>
            <div class="text-2xl font-bold text-emerald-800 mt-0.5">₹{{ number_format($purchaseOrder->total_amount, 2) }}</div>
            <div class="text-xs text-slate-500">Issued by {{ $purchaseOrder->creator?->name ?? 'Purchase Mgr' }}</div>
        </div>
    </div>

    <!-- Line Items Table -->
    <div class="card overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold text-slate-800 text-sm">Ordered Formulations & Herbal Components</h2>
            <span class="text-xs text-slate-500">{{ $purchaseOrder->items->count() }} items</span>
        </div>
        <div class="overflow-x-auto">
            <table class="table w-full text-sm">
                <thead>
                    <tr>
                        <th>Product / Formulation</th>
                        <th class="text-center">Ordered Qty</th>
                        <th class="text-center">Received Qty</th>
                        <th class="text-right">Unit Rate (₹)</th>
                        <th class="text-right">Tax (₹)</th>
                        <th class="text-right">Line Total (₹)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($purchaseOrder->items as $item)
                        <tr>
                            <td>
                                <div class="font-medium text-slate-800">{{ $item->product?->name }}</div>
                                <div class="text-xs text-slate-400 font-mono">{{ $item->product?->sku }}</div>
                            </td>
                            <td class="text-center font-semibold text-slate-800">{{ $item->quantity }}</td>
                            <td class="text-center font-semibold {{ $item->received_quantity >= $item->quantity ? 'text-emerald-700' : 'text-amber-600' }}">
                                {{ $item->received_quantity }}
                            </td>
                            <td class="text-right text-slate-700">₹{{ number_format($item->rate, 2) }}</td>
                            <td class="text-right text-slate-600">₹{{ number_format($item->tax_amount, 2) }}</td>
                            <td class="text-right font-bold text-slate-900">₹{{ number_format($item->total_amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- GRN Receipts Associated -->
    <div class="card overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold text-slate-800 text-sm">Goods Received Notes (GRN Intake History)</h2>
            <span class="text-xs text-slate-500">{{ $purchaseOrder->goodsReceivedNotes->count() }} GRNs</span>
        </div>
        <div class="overflow-x-auto">
            <table class="table w-full text-sm">
                <thead>
                    <tr>
                        <th>GRN Number</th>
                        <th>Intake Date</th>
                        <th>Vendor Invoice #</th>
                        <th>Items Received</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($purchaseOrder->goodsReceivedNotes as $grn)
                        <tr>
                            <td class="font-mono text-xs font-bold text-emerald-800">{{ $grn->grn_number }}</td>
                            <td class="text-xs text-slate-500">{{ $grn->grn_date->format('d M Y') }}</td>
                            <td class="text-xs font-mono text-slate-700">{{ $grn->invoice_number ?? '—' }}</td>
                            <td class="text-xs text-slate-600">{{ $grn->items->sum('accepted_quantity') }} accepted</td>
                            <td class="text-xs text-slate-500">{{ $grn->remarks ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-6 text-slate-400 text-xs">
                                No physical inventory received against this PO yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
