@extends('layouts.app')

@section('title', "Receive Goods — PO #{$po->po_number} — MantraHeal CRM")

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Header -->
    <div>
        <a href="{{ route('purchase-orders.show', $po) }}" class="text-sm font-medium text-emerald-700 hover:text-emerald-800 flex items-center gap-1 mb-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to PO #{{ $po->po_number }}
        </a>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Receive Goods & Create GRN</h1>
        <p class="text-sm text-slate-500">Record incoming herbal shipment, quality check, and generate lot batches</p>
    </div>

    @if($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
            <div class="font-semibold mb-1">Please fix the following validation errors:</div>
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('grn.store') }}" method="POST" class="space-y-6">
        @csrf
        <input type="hidden" name="purchase_order_id" value="{{ $po->id }}">

        <!-- Intake Info -->
        <div class="card p-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Supplier Vendor</label>
                <div class="font-bold text-slate-800 text-sm mt-1">{{ $po->supplier?->name }}</div>
                <div class="text-xs text-slate-500">{{ $po->supplier?->supplier_code }}</div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Receiving Depot</label>
                <div class="font-bold text-slate-800 text-sm mt-1">{{ $po->warehouse?->name ?? 'Central' }}</div>
                <div class="text-xs text-slate-500">{{ $po->warehouse?->city }}</div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">GRN Intake Date *</label>
                <input type="date" name="grn_date" value="{{ old('grn_date', date('Y-m-d')) }}" required class="form-control w-full text-sm">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Vendor Invoice #</label>
                <input type="text" name="invoice_number" placeholder="INV-2026-091" class="form-control w-full text-sm font-mono">
            </div>

            <div class="sm:col-span-2 md:col-span-4">
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Quality Inspection Remarks</label>
                <input type="text" name="remarks" placeholder="Airtight seal verified, tamper evident strips intact..." class="form-control w-full text-sm">
            </div>
        </div>

        <!-- Line Items Intake with Batch & Expiry -->
        <div class="card p-6 space-y-4">
            <h3 class="font-bold text-slate-800 border-b border-slate-100 pb-3">Line Items & Quality Check Inspection</h3>

            <div class="space-y-6">
                @foreach($po->items as $idx => $item)
                    @php
                        $remaining = max(0, $item->quantity - $item->received_quantity);
                    @endphp
                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-200/80 space-y-4">
                        <input type="hidden" name="items[{{ $idx }}][product_id]" value="{{ $item->product_id }}">
                        <input type="hidden" name="items[{{ $idx }}][purchase_order_item_id]" value="{{ $item->id }}">

                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-200/60 pb-3">
                            <div>
                                <h4 class="font-bold text-slate-800 text-sm">{{ $item->product?->name }}</h4>
                                <div class="text-xs text-slate-500 font-mono">SKU: {{ $item->product?->sku }}</div>
                            </div>
                            <div class="text-xs text-slate-600 space-x-3">
                                <span>Ordered: <strong>{{ $item->quantity }}</strong></span>
                                <span>Already Received: <strong>{{ $item->received_quantity }}</strong></span>
                                <span class="text-emerald-700 font-bold">Pending: {{ $remaining }}</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-6 gap-3 text-xs">
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">Received Qty *</label>
                                <input type="number" name="items[{{ $idx }}][received_quantity]" value="{{ old("items.{$idx}.received_quantity", $remaining) }}" min="0" required class="form-control w-full text-center text-sm font-semibold">
                            </div>

                            <div>
                                <label class="block font-semibold text-rose-600 mb-1">Damaged / Rejected</label>
                                <input type="number" name="items[{{ $idx }}][damaged_quantity]" value="{{ old("items.{$idx}.damaged_quantity", 0) }}" min="0" class="form-control w-full text-center text-sm">
                            </div>

                            <div>
                                <label class="block font-semibold text-emerald-800 mb-1">Accepted Qty *</label>
                                <input type="number" name="items[{{ $idx }}][accepted_quantity]" value="{{ old("items.{$idx}.accepted_quantity", $remaining) }}" min="0" required class="form-control w-full text-center text-sm font-bold text-emerald-800">
                            </div>

                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">Batch / Lot #</label>
                                <input type="text" name="items[{{ $idx }}][batch_number]" value="{{ 'LOT-' . strtoupper(Str::random(6)) }}" class="form-control w-full text-sm font-mono">
                            </div>

                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">Mfg Date</label>
                                <input type="date" name="items[{{ $idx }}][manufacturing_date]" value="{{ date('Y-m-d') }}" class="form-control w-full text-sm">
                            </div>

                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">Expiry Date</label>
                                <input type="date" name="items[{{ $idx }}][expiry_date]" value="{{ date('Y-m-d', strtotime('+2 years')) }}" class="form-control w-full text-sm">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Submit -->
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('purchase-orders.show', $po) }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary px-6 py-2.5">
                Confirm Intake & Post to Stock Ledger
            </button>
        </div>
    </form>
</div>
@endsection
