@extends('layouts.app')

@section('title', "Order #{$order->order_number} — MantraHeal CRM")

@section('content')
<div class="space-y-6" x-data="{ showPaymentModal: false, showStatusModal: false }">
    <!-- Header with Quick Action Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('orders.index') }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Orders
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-xs text-slate-500 font-mono">{{ $order->order_number }}</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Order #{{ $order->order_number }}</h1>
                @php
                    $orderBadge = match($order->status) {
                        'delivered' => 'badge-success',
                        'shipped', 'out_for_delivery' => 'badge-info',
                        'processing', 'packed', 'confirmed' => 'badge-primary',
                        'rto', 'returned' => 'badge-danger',
                        'cancelled' => 'bg-slate-200 text-slate-700',
                        default => 'badge-warning'
                    };
                @endphp
                <span class="badge {{ $orderBadge }} text-xs capitalize py-1 px-2.5 font-medium">
                    {{ str_replace('_', ' ', $order->status) }}
                </span>
                <span class="badge bg-slate-100 text-slate-700 text-xs">
                    {{ ucfirst($order->sales_channel ?? 'direct') }}
                </span>
            </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <button type="button" @click="showStatusModal = true" class="btn btn-secondary text-sm inline-flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Change Status
            </button>
            <button type="button" @click="showPaymentModal = true" class="btn btn-secondary text-sm inline-flex items-center gap-1.5">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Record Payment
            </button>
            <a href="{{ route('orders.invoice', $order) }}" target="_blank" class="btn btn-primary text-sm inline-flex items-center gap-1.5 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print GST Invoice
            </a>
        </div>
    </div>

    <!-- Main Order Details Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left 2 Cols: Order Items & Payment Breakdown -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Items Table -->
            <div class="card overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <h2 class="font-bold text-slate-800 text-sm">Ordered Products & Herbal Formulations</h2>
                    <span class="text-xs text-slate-500 font-medium">{{ $order->items->count() }} line items</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="table w-full text-sm">
                        <thead>
                            <tr>
                                <th>Product / SKU</th>
                                <th class="text-center">HSN</th>
                                <th class="text-center">Qty</th>
                                <th class="text-right">Unit Price</th>
                                <th class="text-right">GST %</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($order->items as $item)
                                <tr>
                                    <td>
                                        <div class="font-medium text-slate-800">{{ $item->product_name }}</div>
                                        <div class="text-xs text-slate-400 font-mono">SKU: {{ $item->sku ?? $item->product?->sku ?? 'N/A' }}</div>
                                    </td>
                                    <td class="text-center text-xs text-slate-500 font-mono">{{ $item->hsn_code ?? '3004' }}</td>
                                    <td class="text-center font-medium">{{ $item->quantity }}</td>
                                    <td class="text-right text-slate-700">₹{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-right text-xs text-slate-500">{{ $item->tax_rate ?? 18 }}%</td>
                                    <td class="text-right font-semibold text-slate-800">₹{{ number_format($item->total_price, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Financial Summary Footer -->
                <div class="bg-slate-50 p-6 border-t border-slate-100">
                    <div class="max-w-xs ml-auto space-y-2 text-sm">
                        <div class="flex justify-between text-slate-600">
                            <span>Subtotal:</span>
                            <span class="font-medium text-slate-800">₹{{ number_format($order->subtotal, 2) }}</span>
                        </div>
                        @if($order->discount_amount > 0)
                            <div class="flex justify-between text-emerald-700">
                                <span>Discount ({{ $order->coupon_code ?? 'Promo' }}):</span>
                                <span>- ₹{{ number_format($order->discount_amount, 2) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between text-slate-600">
                            <span>GST (Inclusive / CGST + SGST):</span>
                            <span class="font-medium text-slate-800">₹{{ number_format($order->tax_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Shipping / Logistics:</span>
                            <span class="font-medium text-slate-800">₹{{ number_format($order->shipping_charge, 2) }}</span>
                        </div>
                        <div class="border-t border-slate-200 pt-2 flex justify-between text-base font-bold text-slate-900">
                            <span>Grand Total:</span>
                            <span class="text-emerald-800 text-lg">₹{{ number_format($order->total_amount, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment & Transaction Records -->
            <div class="card p-5 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <h2 class="font-bold text-slate-800 text-sm">Payments & Financial Transactions</h2>
                        @php
                            $pBadge = match($order->payment_status) {
                                'paid' => 'badge-success',
                                'partial' => 'badge-warning',
                                'refunded' => 'badge-danger',
                                default => 'badge-secondary'
                            };
                        @endphp
                        <span class="badge {{ $pBadge }} text-xs capitalize">{{ $order->payment_status }}</span>
                    </div>
                    <button type="button" @click="showPaymentModal = true" class="text-xs font-semibold text-emerald-700 hover:text-emerald-800">
                        + Add Payment
                    </button>
                </div>

                @if($order->payments && $order->payments->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="table w-full text-xs">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Method</th>
                                    <th>Reference / Txn ID</th>
                                    <th>Recorded By</th>
                                    <th class="text-right">Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->payments as $payment)
                                    <tr>
                                        <td>{{ $payment->created_at->format('d M Y, h:i A') }}</td>
                                        <td class="capitalize font-medium text-slate-700">{{ str_replace('_', ' ', $payment->payment_method) }}</td>
                                        <td class="font-mono text-slate-600">{{ $payment->transaction_reference ?? 'N/A' }}</td>
                                        <td class="text-slate-500">{{ $payment->recorder?->name ?? 'System' }}</td>
                                        <td class="text-right font-semibold text-slate-800">₹{{ number_format($payment->amount, 2) }}</td>
                                        <td>
                                            <span class="badge badge-success text-[10px]">{{ $payment->status ?? 'Completed' }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-6 text-slate-400 text-sm">
                        No payments recorded yet. Status is marked as <strong class="text-slate-600">{{ $order->payment_status }}</strong>.
                    </div>
                @endif
            </div>

            <!-- Shipments & Tracking -->
            <div class="card p-5 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h2 class="font-bold text-slate-800 text-sm">Shipping & Fulfillment Details</h2>
                    @if($order->status === 'delivered')
                        <a href="{{ route('returns.create', ['order_id' => $order->id]) }}" class="text-xs font-semibold text-rose-600 hover:underline">
                            Request Customer Return
                        </a>
                    @endif
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                    <div class="p-3 bg-slate-50 rounded-lg">
                        <div class="text-xs text-slate-500">Logistics Carrier</div>
                        <div class="font-medium text-slate-800 mt-0.5">{{ $order->courier_name ?? 'Not Assigned' }}</div>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-lg">
                        <div class="text-xs text-slate-500">Tracking / AWB Number</div>
                        <div class="font-mono text-xs font-medium text-slate-800 mt-0.5">{{ $order->tracking_number ?? 'Pending Dispatch' }}</div>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-lg">
                        <div class="text-xs text-slate-500">Fulfillment Status</div>
                        <div class="font-medium capitalize text-slate-800 mt-0.5">{{ str_replace('_', ' ', $order->status) }}</div>
                    </div>
                </div>

                @if($order->notes)
                    <div class="p-3 bg-amber-50/50 border border-amber-200/50 rounded-lg text-xs text-amber-900">
                        <strong class="font-medium">Order Notes:</strong> {{ $order->notes }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Col: Customer 360 Card & Quick Actions -->
        <div class="space-y-6">
            <!-- Customer Card -->
            <div class="card p-5 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h2 class="font-bold text-slate-800 text-sm">Customer Profile</h2>
                    @if($order->customer)
                        <a href="{{ route('customers.show', $order->customer) }}" class="text-xs font-semibold text-emerald-700 hover:underline">
                            View 360° Profile
                        </a>
                    @endif
                </div>

                @if($order->customer)
                    <div class="space-y-3 text-sm">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold">
                                {{ strtoupper(substr($order->customer->name, 0, 2)) }}
                            </div>
                            <div>
                                <div class="font-semibold text-slate-800">{{ $order->customer->name }}</div>
                                <div class="text-xs text-slate-500 font-mono">{{ $order->customer->customer_code }}</div>
                            </div>
                        </div>

                        <div class="space-y-2 pt-2 border-t border-slate-100 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">Phone:</span>
                                <span class="font-medium text-slate-800">{{ $order->customer->phone }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">Email:</span>
                                <span class="text-slate-700">{{ $order->customer->email ?? '—' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">Total Orders:</span>
                                <span class="badge bg-emerald-50 text-emerald-700">{{ $order->customer->orders_count ?? $order->customer->orders()->count() }} orders</span>
                            </div>
                        </div>

                        <!-- Quick Communication Buttons -->
                        <div class="grid grid-cols-2 gap-2 pt-2">
                            <a href="{{ route('calls.create', ['customer_id' => $order->customer->id]) }}" class="btn btn-secondary text-xs py-1.5 flex items-center justify-center gap-1">
                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                Call
                            </a>
                            <a href="{{ route('communication.whatsapp', ['customer_id' => $order->customer->id]) }}" class="btn btn-secondary text-xs py-1.5 flex items-center justify-center gap-1 text-emerald-700">
                                WhatsApp
                            </a>
                        </div>
                    </div>
                @else
                    <div class="text-slate-400 text-sm">Guest Checkout Customer</div>
                @endif
            </div>

            <!-- Shipping Address Card -->
            <div class="card p-5 space-y-3 text-sm">
                <h2 class="font-bold text-slate-800 text-sm border-b border-slate-100 pb-2">Delivery Destination</h2>
                <div class="text-slate-700 space-y-1">
                    <div class="font-semibold text-slate-800">{{ $order->shipping_name ?? $order->customer?->name }}</div>
                    <div>{{ $order->shipping_address_line1 }}</div>
                    @if($order->shipping_address_line2)
                        <div>{{ $order->shipping_address_line2 }}</div>
                    @endif
                    <div>{{ $order->shipping_city }}, {{ $order->shipping_state }} — <span class="font-mono font-medium">{{ $order->shipping_pincode }}</span></div>
                    <div class="text-xs text-slate-500 pt-1">Phone: {{ $order->shipping_phone ?? $order->customer?->phone }}</div>
                </div>
            </div>

            <!-- Sales Representative Card -->
            <div class="card p-5 space-y-3 text-sm">
                <h2 class="font-bold text-slate-800 text-sm border-b border-slate-100 pb-2">Assigned Sales Executive</h2>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-500">Representative:</span>
                    <span class="font-medium text-slate-800">{{ $order->assignedUser?->name ?? 'Direct Web/Store' }}</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-500">Order Placed On:</span>
                    <span class="text-slate-700">{{ $order->created_at->format('d M Y, h:i A') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Change Modal -->
    <div x-show="showStatusModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 flex items-center justify-center p-4" style="display: none;">
        <div class="card max-w-md w-full p-6 space-y-4 shadow-2xl bg-white" @click.away="showStatusModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-800">Update Order Status</h3>
                <button type="button" @click="showStatusModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <form action="{{ route('orders.update-status', $order) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Status *</label>
                    <select name="order_status" class="form-control w-full text-sm" required>
                        @foreach($statuses as $st)
                            <option value="{{ $st }}" {{ $order->status === $st ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $st)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Status Notes / Remarks</label>
                    <textarea name="notes" rows="2" class="form-control w-full text-sm" placeholder="Reason for status change, carrier pickup notes..."></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showStatusModal = false" class="btn btn-secondary text-xs">Cancel</button>
                    <button type="submit" class="btn btn-primary text-xs">Save Status</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Payment Recording Modal -->
    <div x-show="showPaymentModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 flex items-center justify-center p-4" style="display: none;">
        <div class="card max-w-md w-full p-6 space-y-4 shadow-2xl bg-white" @click.away="showPaymentModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-800">Record Payment</h3>
                <button type="button" @click="showPaymentModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <form action="{{ route('orders.record-payment', $order) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Amount (₹) *</label>
                    <input type="number" step="0.01" name="amount" value="{{ $order->total_amount - $order->payments->sum('amount') }}" required class="form-control w-full text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Payment Method *</label>
                    <select name="payment_method" required class="form-control w-full text-sm">
                        @foreach($paymentMethods as $pmKey => $pmVal)
                            <option value="{{ $pmKey }}">{{ $pmVal }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Transaction Ref / UTR</label>
                    <input type="text" name="transaction_reference" placeholder="UPI Ref, Cheque #, Razorpay ID" class="form-control w-full text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Payment Date *</label>
                    <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required class="form-control w-full text-sm">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showPaymentModal = false" class="btn btn-secondary text-xs">Cancel</button>
                    <button type="submit" class="btn btn-primary text-xs">Confirm Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
