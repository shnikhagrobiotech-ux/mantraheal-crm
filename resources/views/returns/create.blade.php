@extends('layouts.app')

@section('title', "Request Return — Order #{$order->order_number} — MantraHeal CRM")

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div>
        <a href="{{ route('orders.show', $order) }}" class="text-sm font-medium text-emerald-700 hover:text-emerald-800 flex items-center gap-1 mb-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Order #{{ $order->order_number }}
        </a>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Initiate Customer Return</h1>
        <p class="text-sm text-slate-500">Customer: <strong>{{ $order->customer?->name }}</strong> ({{ $order->customer?->phone }})</p>
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

    <form action="{{ route('returns.store') }}" method="POST" class="space-y-6">
        @csrf
        <input type="hidden" name="order_id" value="{{ $order->id }}">

        <!-- Return Meta -->
        <div class="card p-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Return Date *</label>
                <input type="date" name="return_date" value="{{ old('return_date', date('Y-m-d')) }}" required class="form-control w-full text-sm">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Primary Reason *</label>
                <select name="reason" required class="form-control w-full text-sm">
                    @foreach($reasons as $r)
                        <option value="{{ $r }}" {{ old('reason') == $r ? 'selected' : '' }}>{{ $r }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Customer Resolution Preference *</label>
                <select name="refund_action" required class="form-control w-full text-sm">
                    <option value="Refund">Full / Partial Refund</option>
                    <option value="Replacement">Replacement Formulation</option>
                    <option value="Store Credit">Store Credit / Coupon</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Return Intake Depot</label>
                <select name="restocking_warehouse_id" class="form-control w-full text-sm">
                    <option value="">-- Depot to Receive Return --</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->city }})</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Detailed Customer Feedback</label>
                <input type="text" name="notes" placeholder="Customer reported bottle seal leak during transit, etc." class="form-control w-full text-sm">
            </div>
        </div>

        <!-- Select Products to Return -->
        <div class="card p-6 space-y-4">
            <h3 class="font-bold text-slate-800 border-b border-slate-100 pb-3">Select Items to Return</h3>

            <div class="space-y-3">
                @foreach($order->items as $idx => $item)
                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-sm">
                        <div class="flex-1">
                            <div class="font-semibold text-slate-800">{{ $item->product_name }}</div>
                            <div class="text-xs text-slate-500">Ordered: {{ $item->quantity }} units | Price: ₹{{ number_format($item->unit_price, 2) }}</div>
                        </div>

                        <input type="hidden" name="items[{{ $idx }}][order_item_id]" value="{{ $item->id }}">
                        <input type="hidden" name="items[{{ $idx }}][product_id]" value="{{ $item->product_id }}">

                        <div class="flex items-center gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-500 mb-0.5">Return Qty</label>
                                <input type="number" name="items[{{ $idx }}][quantity]" value="{{ $item->quantity }}" min="1" max="{{ $item->quantity }}" required class="form-control w-20 text-center text-sm font-semibold">
                            </div>

                            <div class="flex-1">
                                <label class="block text-[11px] font-semibold text-slate-500 mb-0.5">Item Condition</label>
                                <input type="text" name="items[{{ $idx }}][condition_notes]" placeholder="Unopened seal / damaged box" class="form-control text-xs w-48">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Submit -->
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('orders.show', $order) }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary px-6 py-2.5">
                Register Return Request
            </button>
        </div>
    </form>
</div>
@endsection
