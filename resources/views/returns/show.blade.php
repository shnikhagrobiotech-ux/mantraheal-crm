@extends('layouts.app')

@section('title', "Return #{$return->return_number} — MantraHeal CRM")

@section('content')
<div class="space-y-6" x-data="{ showQcModal: false, showRefundModal: false }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('returns.index') }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Returns
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-xs text-slate-500 font-mono">{{ $return->return_number }}</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Return #{{ $return->return_number }}</h1>
                <span class="badge bg-emerald-50 text-emerald-800 text-xs font-medium">{{ $return->status }}</span>
                @php
                    $qcBadge = match($return->qc_status) {
                        'Passed' => 'badge-success',
                        'Failed' => 'badge-danger',
                        default => 'badge-warning'
                    };
                @endphp
                <span class="badge {{ $qcBadge }} text-xs">QC: {{ $return->qc_status }}</span>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @if($return->qc_status === 'Pending')
                <button type="button" @click="showQcModal = true" class="btn btn-primary text-sm inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Perform QC Check
                </button>
            @endif

            @if(!$return->refund && $return->qc_status === 'Passed' && $return->refund_action === 'Refund')
                <button type="button" @click="showRefundModal = true" class="btn btn-primary text-sm inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Issue Refund
                </button>
            @endif
        </div>
    </div>

    <!-- Return Details Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <!-- Returned Items -->
            <div class="card overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <h2 class="font-bold text-slate-800 text-sm">Items in Return Package</h2>
                    <span class="text-xs text-slate-500">{{ $return->items->count() }} line items</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="table w-full text-sm">
                        <thead>
                            <tr>
                                <th>Product / SKU</th>
                                <th class="text-center">Return Qty</th>
                                <th>Reported Condition</th>
                                <th class="text-right">Unit Value</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($return->items as $item)
                                <tr>
                                    <td>
                                        <div class="font-medium text-slate-800">{{ $item->product?->name }}</div>
                                        <div class="text-xs text-slate-400 font-mono">{{ $item->product?->sku }}</div>
                                    </td>
                                    <td class="text-center font-bold text-slate-800">{{ $item->quantity }}</td>
                                    <td class="text-xs text-slate-600">{{ $item->condition_notes ?? 'Original unopened pack' }}</td>
                                    <td class="text-right font-medium text-slate-800">
                                        ₹{{ number_format($item->orderItem?->unit_price ?? 0, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Refund Section if processed -->
            @if($return->refund)
                <div class="card p-5 space-y-3 bg-emerald-50/40 border border-emerald-200">
                    <div class="flex items-center justify-between">
                        <h3 class="font-bold text-emerald-900 text-sm">Refund Successfully Disbursed</h3>
                        <span class="badge badge-success text-xs">Completed</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                        <div>
                            <span class="text-slate-500">Refund Amount:</span>
                            <div class="text-lg font-bold text-emerald-800">₹{{ number_format($return->refund->amount, 2) }}</div>
                        </div>
                        <div>
                            <span class="text-slate-500">Method & Txn Ref:</span>
                            <div class="font-medium text-slate-800">{{ ucfirst($return->refund->refund_method) }}</div>
                            <div class="font-mono text-slate-500">{{ $return->refund->transaction_reference ?? 'N/A' }}</div>
                        </div>
                        <div>
                            <span class="text-slate-500">Processed On:</span>
                            <div class="font-medium text-slate-800">{{ $return->refund->processed_at?->format('d M Y, h:i A') }}</div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Right Side: Order & Customer Meta -->
        <div class="space-y-6">
            <div class="card p-5 space-y-3 text-sm">
                <h3 class="font-bold text-slate-800 text-sm border-b border-slate-100 pb-2">Return Information</h3>

                <div class="space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Original Order:</span>
                        <a href="{{ route('orders.show', $return->order_id) }}" class="font-mono font-bold text-emerald-700 hover:underline">
                            {{ $return->order?->order_number }}
                        </a>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Return Date:</span>
                        <span class="text-slate-800">{{ $return->return_date->format('d M Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Return Reason:</span>
                        <span class="font-medium text-slate-800">{{ $return->reason }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Preferred Resolution:</span>
                        <span class="font-semibold text-slate-800">{{ $return->refund_action }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Restocking Depot:</span>
                        <span class="font-medium text-slate-800">{{ $return->warehouse?->name ?? 'Pending Assignment' }}</span>
                    </div>
                </div>

                @if($return->notes)
                    <div class="pt-2 border-t border-slate-100 text-xs text-slate-600">
                        <strong>Customer Remarks:</strong> {{ $return->notes }}
                    </div>
                @endif
            </div>

            <!-- Customer Card -->
            <div class="card p-5 space-y-3 text-sm">
                <h3 class="font-bold text-slate-800 text-sm border-b border-slate-100 pb-2">Customer Profile</h3>
                @if($return->customer)
                    <div class="space-y-1 text-xs">
                        <div class="font-bold text-slate-800 text-sm">{{ $return->customer->name }}</div>
                        <div class="text-slate-500">Phone: {{ $return->customer->phone }}</div>
                        <div class="text-slate-500">City: {{ $return->customer->city }}, {{ $return->customer->state }}</div>
                        <div class="pt-2">
                            <a href="{{ route('customers.show', $return->customer) }}" class="text-emerald-700 font-semibold hover:underline">
                                View Customer 360° Profile →
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- QC Check Modal -->
    <div x-show="showQcModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 flex items-center justify-center p-4" style="display: none;">
        <div class="card max-w-md w-full p-6 space-y-4 shadow-2xl bg-white" @click.away="showQcModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-800">Quality Check Inspection</h3>
                <button type="button" @click="showQcModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <form action="{{ route('returns.qc', $return) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">QC Decision *</label>
                    <select name="qc_status" required class="form-control w-full text-sm">
                        <option value="Passed">QC Passed (Intact seal, restocking permitted)</option>
                        <option value="Failed">QC Failed (Damaged/used, mark for write-off)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Restocking Warehouse</label>
                    <select name="restocking_warehouse_id" class="form-control w-full text-sm">
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->city }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Inspection Notes</label>
                    <textarea name="notes" rows="2" placeholder="Outer carton condition, cap intact, batch code verified..." class="form-control w-full text-sm"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showQcModal = false" class="btn btn-secondary text-xs">Cancel</button>
                    <button type="submit" class="btn btn-primary text-xs">Complete QC & Update Stock</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Issue Refund Modal -->
    <div x-show="showRefundModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 flex items-center justify-center p-4" style="display: none;">
        <div class="card max-w-md w-full p-6 space-y-4 shadow-2xl bg-white" @click.away="showRefundModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-800">Disburse Refund to Customer</h3>
                <button type="button" @click="showRefundModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <form action="{{ route('refunds.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="order_return_id" value="{{ $return->id }}">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Refund Amount (₹) *</label>
                    <input type="number" step="0.01" name="amount" value="{{ $return->order?->total_amount }}" required class="form-control w-full text-sm font-bold text-emerald-800">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Refund Channel *</label>
                    <select name="refund_method" required class="form-control w-full text-sm">
                        <option value="bank_transfer">Original Payment Source / Bank Transfer</option>
                        <option value="upi">Direct UPI Refund</option>
                        <option value="store_credit">Store Credit / Wallet</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Bank UTR / Transaction Ref</label>
                    <input type="text" name="transaction_reference" placeholder="e.g. UTR90129384" class="form-control w-full text-sm font-mono">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Notes</label>
                    <input type="text" name="notes" placeholder="Approved by Customer Care Manager" class="form-control w-full text-sm">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showRefundModal = false" class="btn btn-secondary text-xs">Cancel</button>
                    <button type="submit" class="btn btn-primary text-xs">Authorize Refund</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
