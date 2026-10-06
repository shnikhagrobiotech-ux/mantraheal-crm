@extends('layouts.app')

@section('title', 'Refunds Ledger — MantraHeal CRM')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('returns.index') }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Returns
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-xs text-slate-500">Refunds</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Customer Refunds Ledger</h1>
            <p class="text-sm text-slate-500">Audit record of all bank transfers, UPI refunds, and store credits disbursed</p>
        </div>
        <div>
            <a href="{{ route('returns.index') }}" class="btn btn-secondary text-sm">
                Return Requests
            </a>
        </div>
    </div>

    <!-- Refunds Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table w-full text-sm">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Order #</th>
                        <th>Return #</th>
                        <th>Customer</th>
                        <th class="text-right">Refund Amount (₹)</th>
                        <th>Method</th>
                        <th>UTR / Reference</th>
                        <th>Authorized By</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($refunds as $ref)
                        <tr class="hover:bg-slate-50/75 transition-colors">
                            <td class="text-xs text-slate-500 whitespace-nowrap">
                                {{ $ref->processed_at ? $ref->processed_at->format('d M Y, h:i A') : $ref->created_at->format('d M Y') }}
                            </td>
                            <td class="font-mono text-xs whitespace-nowrap">
                                <a href="{{ route('orders.show', $ref->order_id) }}" class="text-emerald-700 hover:underline">
                                    {{ $ref->order?->order_number }}
                                </a>
                            </td>
                            <td class="font-mono text-xs whitespace-nowrap">
                                @if($ref->orderReturn)
                                    <a href="{{ route('returns.show', $ref->orderReturn) }}" class="text-rose-700 hover:underline">
                                        {{ $ref->orderReturn->return_number }}
                                    </a>
                                @else
                                    <span class="text-slate-400">Direct Refund</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap">
                                <div class="font-medium text-slate-800">{{ $ref->customer?->name }}</div>
                                <div class="text-xs text-slate-400">{{ $ref->customer?->phone }}</div>
                            </td>
                            <td class="text-right font-bold text-slate-900 whitespace-nowrap">
                                ₹{{ number_format($ref->amount, 2) }}
                            </td>
                            <td class="text-xs capitalize font-medium text-slate-700 whitespace-nowrap">
                                {{ str_replace('_', ' ', $ref->refund_method) }}
                            </td>
                            <td class="font-mono text-xs text-slate-600 whitespace-nowrap">
                                {{ $ref->transaction_reference ?? 'Pending Reference' }}
                            </td>
                            <td class="text-xs text-slate-500 whitespace-nowrap">
                                {{ $ref->processor?->name ?? 'System' }}
                            </td>
                            <td class="whitespace-nowrap">
                                <span class="badge badge-success text-[10px] uppercase">
                                    {{ $ref->status ?? 'Completed' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-12 text-slate-400 text-sm">
                                No refund transactions recorded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($refunds->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $refunds->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
