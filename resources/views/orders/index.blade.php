@extends('layouts.app')

@section('title', 'Orders Management — MantraHeal CRM')

@section('content')
<div class="space-y-6">
    <!-- Header & Quick Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Order Management</h1>
            <p class="text-sm text-slate-500">Track, fulfill, and manage omnichannel orders and shipments</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('reports.sales') }}" class="btn btn-secondary inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Sales Report
            </a>
            <a href="{{ route('orders.create') }}" class="btn btn-primary inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Create Order
            </a>
        </div>
    </div>

    <!-- Quick Status Filter Pills -->
    <div class="flex items-center gap-2 overflow-x-auto pb-2 text-sm">
        <a href="{{ route('orders.index') }}" 
           class="px-3 py-1.5 rounded-lg font-medium whitespace-nowrap transition-colors {{ !request('status') ? 'bg-emerald-800 text-white shadow-sm' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
            All Orders
        </a>
        @foreach(['new' => 'New', 'confirmed' => 'Confirmed', 'processing' => 'Processing', 'packed' => 'Packed', 'shipped' => 'Shipped', 'delivered' => 'Delivered', 'cancelled' => 'Cancelled', 'returned' => 'Returned', 'rto' => 'RTO'] as $sKey => $sLabel)
            <a href="{{ route('orders.index', array_merge(request()->query(), ['status' => $sKey])) }}" 
               class="px-3 py-1.5 rounded-lg font-medium whitespace-nowrap transition-colors {{ request('status') === $sKey ? 'bg-emerald-800 text-white shadow-sm' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
                {{ $sLabel }}
            </a>
        @endforeach
    </div>

    <!-- Filters & Search Bar -->
    <div class="card p-4">
        <form method="GET" action="{{ route('orders.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Order #, Customer, Mobile..." 
                       class="form-control text-sm w-full">
            </div>
            <div>
                <select name="status" class="form-control text-sm w-full">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $st)
                        <option value="{{ $st }}" {{ request('status') == $st ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $st)) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="payment_status" class="form-control text-sm w-full">
                    <option value="">All Payment Statuses</option>
                    @foreach($paymentStatuses as $ps)
                        <option value="{{ $ps }}" {{ request('payment_status') == $ps ? 'selected' : '' }}>{{ ucfirst($ps) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="channel" class="form-control text-sm w-full">
                    <option value="">All Channels</option>
                    @foreach($channels as $ch)
                        <option value="{{ $ch }}" {{ request('channel') == $ch ? 'selected' : '' }}>{{ ucfirst($ch) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="btn btn-primary text-sm py-2 px-4 flex-1">Filter</button>
                <a href="{{ route('orders.index') }}" class="btn btn-secondary text-sm py-2 px-3" title="Clear Filters">Reset</a>
            </div>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Channel</th>
                        <th>Items</th>
                        <th>Total Amount</th>
                        <th>Payment</th>
                        <th>Fulfillment</th>
                        <th>Courier</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($orders as $order)
                        <tr class="hover:bg-slate-50/75 transition-colors">
                            <td class="font-medium text-emerald-800 whitespace-nowrap">
                                <a href="{{ route('orders.show', $order) }}" class="hover:underline flex items-center gap-1.5">
                                    <span>{{ $order->order_number }}</span>
                                </a>
                            </td>
                            <td class="text-xs text-slate-500 whitespace-nowrap">
                                {{ $order->created_at->format('d M Y, h:i A') }}
                            </td>
                            <td class="whitespace-nowrap">
                                @if($order->customer)
                                    <div class="font-medium text-slate-800">
                                        <a href="{{ route('customers.show', $order->customer) }}" class="hover:text-emerald-700">
                                            {{ $order->customer->name }}
                                        </a>
                                    </div>
                                    <div class="text-xs text-slate-500">{{ $order->customer->phone }}</div>
                                @else
                                    <span class="text-slate-400 italic">Walk-in Customer</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-slate-100 text-slate-700 text-xs">
                                    {{ ucfirst($order->sales_channel ?? 'direct') }}
                                </span>
                            </td>
                            <td class="text-xs text-slate-600">
                                {{ $order->items->sum('quantity') }} items
                            </td>
                            <td class="font-semibold text-slate-800 whitespace-nowrap">
                                ₹{{ number_format($order->total_amount, 2) }}
                            </td>
                            <td>
                                @php
                                    $payBadge = match($order->payment_status) {
                                        'paid' => 'badge-success',
                                        'partial' => 'badge-warning',
                                        'refunded' => 'badge-danger',
                                        default => 'badge-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $payBadge }} text-xs capitalize">
                                    {{ $order->payment_status }}
                                </span>
                            </td>
                            <td>
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
                                <span class="badge {{ $orderBadge }} text-xs capitalize">
                                    {{ str_replace('_', ' ', $order->status) }}
                                </span>
                            </td>
                            <td class="text-xs text-slate-600 whitespace-nowrap">
                                @if($order->courier_name)
                                    <div>{{ $order->courier_name }}</div>
                                    @if($order->tracking_number)
                                        <span class="font-mono text-[11px] text-slate-500">{{ $order->tracking_number }}</span>
                                    @endif
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="text-right whitespace-nowrap space-x-1">
                                <a href="{{ route('orders.show', $order) }}" class="btn btn-secondary text-xs py-1 px-2.5" title="View Order">
                                    View
                                </a>
                                <a href="{{ route('orders.invoice', $order) }}" target="_blank" class="btn btn-secondary text-xs py-1 px-2.5 text-slate-600 hover:text-emerald-700" title="GST Invoice">
                                    Invoice
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-12 text-slate-500">
                                <div class="max-w-sm mx-auto text-center space-y-3">
                                    <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 mx-auto flex items-center justify-center">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                    </div>
                                    <div class="font-medium text-slate-800">No orders found</div>
                                    <p class="text-xs text-slate-500">No orders match your active filter parameters or search criteria.</p>
                                    <a href="{{ route('orders.create') }}" class="btn btn-primary text-xs py-1.5 px-3">Create First Order</a>
                                </div>
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
