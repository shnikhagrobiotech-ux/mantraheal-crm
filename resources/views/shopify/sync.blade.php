@extends('layouts.app')

@section('title', 'Shopify E-Commerce Sync Hub — MantraHeal CRM')

@section('content')
<div class="space-y-6" x-data="{
    syncing: false,
    syncResult: null,
    async triggerSync(limit = 5) {
        this.syncing = true;
        this.syncResult = null;
        try {
            const formData = new FormData();
            formData.append('limit', limit);
            formData.append('_token', '{{ csrf_token() }}');
            const res = await fetch('{{ route('shopify.sync.pull-orders') }}', {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: formData
            });
            const data = await res.json();
            this.syncResult = data;
            setTimeout(() => window.location.reload(), 1500);
        } catch (e) {
            this.syncResult = { status: 'error', message: 'Sync failed or timed out.' };
        } finally {
            this.syncing = false;
        }
    }
}">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xs text-slate-500 font-medium">Integrations</span>
                <span class="text-slate-300">/</span>
                <span class="text-xs text-emerald-700 font-semibold">Shopify E-Commerce</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm">SH</span>
                Shopify Active Synchronization Hub
            </h1>
            <p class="text-sm text-slate-500">Bi-directional synchronization of online orders, live inventory allocations, and courier tracking data</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('settings.index') }}#shopify" class="btn btn-secondary text-xs px-3.5 py-2 flex items-center gap-1.5 shadow-sm">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                API Settings
            </a>

            <button type="button" 
                    @click="triggerSync(5)" 
                    :disabled="syncing"
                    class="btn bg-emerald-700 hover:bg-emerald-600 text-white font-bold text-xs px-5 py-2 rounded-lg shadow-sm flex items-center gap-2 cursor-pointer transition">
                <svg x-show="syncing" class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                <svg x-show="!syncing" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span x-text="syncing ? 'Synchronizing...' : 'Pull Shopify Orders Now'"></span>
            </button>
        </div>
    </div>

    <!-- Feedback Notification -->
    <div x-show="syncResult" x-transition class="p-4 rounded-xl border text-sm flex items-center justify-between" :class="syncResult?.status === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-rose-50 border-rose-200 text-rose-900'">
        <div class="flex items-center gap-2">
            <span class="font-bold" x-text="syncResult?.status === 'success' ? '✓ Synchronization Complete' : '✗ Sync Error'"></span>
            <span x-show="syncResult?.imported_count !== undefined" x-text="`(${syncResult?.imported_count} new orders imported, ${syncResult?.updated_count} updated)`"></span>
            <span x-show="syncResult?.message" x-text="syncResult?.message"></span>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success') }}</span>
            </div>
            <button @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
        </div>
    @endif

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card p-5 border-l-4 border-emerald-600">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Synced Shopify Orders</div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-black text-slate-900">{{ number_format($stats['total_shopify_orders']) }}</span>
                <span class="text-xs text-emerald-700 font-semibold">Total in CRM</span>
            </div>
            <div class="text-[11px] text-slate-400 mt-1">Directly bridged with inventory</div>
        </div>

        <div class="card p-5 border-l-4 border-teal-600">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Connected Store</div>
            <div class="mt-2 text-sm font-bold font-mono text-slate-900 truncate" title="{{ $stats['shop_url'] }}">
                {{ $stats['shop_url'] }}
            </div>
            <div class="text-[11px] text-emerald-700 font-medium mt-1 flex items-center gap-1">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Connected & Active
            </div>
        </div>

        <div class="card p-5 border-l-4 border-indigo-600">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Last Order Sync</div>
            <div class="mt-2 text-xs font-bold text-slate-900">
                {{ $stats['last_sync_at'] }}
            </div>
            <div class="text-[11px] text-slate-400 mt-1">Automatic sync every 15 min</div>
        </div>

        <div class="card p-5 border-l-4 border-amber-600">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Pending Fulfillment Push</div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-black text-slate-900">{{ count($unfulfilledShopifyOrders) }}</span>
                <span class="text-xs text-amber-700 font-semibold">Ready to Push</span>
            </div>
            <div class="text-[11px] text-slate-400 mt-1">Orders with AWBs to sync</div>
        </div>
    </div>

    <!-- Main Content Tabs / Panels -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left 2 Cols: Recent Synced Orders -->
        <div class="lg:col-span-2 card p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h2 class="font-bold text-slate-900 text-base flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        Recently Synchronized Shopify Orders
                    </h2>
                    <p class="text-xs text-slate-500">Live feed of orders imported from Shopify Storefront</p>
                </div>
                <a href="{{ route('orders.index', ['channel' => 'Shopify']) }}" class="text-xs text-teal-700 font-bold hover:underline">View All &rarr;</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-500 uppercase text-[10px] font-bold">
                            <th class="py-2.5 px-3">CRM Order #</th>
                            <th class="py-2.5 px-3">Shopify ID</th>
                            <th class="py-2.5 px-3">Customer</th>
                            <th class="py-2.5 px-3">Items / Total</th>
                            <th class="py-2.5 px-3">Payment</th>
                            <th class="py-2.5 px-3">Status</th>
                            <th class="py-2.5 px-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($stats['recent_orders'] as $order)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-3 px-3 font-bold font-mono text-teal-800">
                                    <a href="{{ route('orders.show', $order) }}" class="hover:underline">{{ $order->order_number }}</a>
                                </td>
                                <td class="py-3 px-3 font-mono text-slate-500 text-[11px]">
                                    #{{ $order->shopify_order_id ?? 'N/A' }}
                                </td>
                                <td class="py-3 px-3">
                                    <div class="font-semibold text-slate-900">{{ $order->customer?->name ?? $order->shipping_name }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $order->customer?->phone ?? $order->shipping_phone }}</div>
                                </td>
                                <td class="py-3 px-3">
                                    <div class="font-bold text-slate-900">₹{{ number_format($order->grand_total, 2) }}</div>
                                    <div class="text-[10px] text-slate-500">{{ $order->items->count() }} item(s)</div>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $order->payment_status === 'Paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $order->payment_status }}
                                    </span>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-800">
                                        {{ $order->order_status }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('orders.invoice', $order) }}" target="_blank" title="GST Invoice" class="p-1 text-slate-400 hover:text-teal-700">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        </a>
                                        <a href="{{ route('orders.show', $order) }}" class="btn btn-secondary text-[11px] px-2.5 py-1">View</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-400 text-xs">
                                    No Shopify orders synced yet. Click <strong>Pull Shopify Orders Now</strong> above to synchronize!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right 1 Col: Quick Push Actions -->
        <div class="space-y-6">
            
            <!-- Push Fulfillments Card -->
            <div class="card p-5 space-y-4">
                <div class="border-b border-slate-100 pb-2.5">
                    <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                        Fulfillment & Tracking Push
                    </h3>
                    <p class="text-[11px] text-slate-500">Push AWB & dispatch confirmation back to Shopify customer orders</p>
                </div>

                <div class="space-y-2.5 max-h-72 overflow-y-auto">
                    @forelse($unfulfilledShopifyOrders as $unf)
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs flex items-center justify-between gap-2">
                            <div>
                                <div class="font-bold text-slate-900">{{ $unf->order_number }}</div>
                                <div class="text-[10px] text-slate-500 font-mono">AWB: {{ $unf->tracking_number ?? 'Pending Dispatch' }}</div>
                            </div>
                            <form action="{{ route('shopify.sync.push-fulfillment', $unf) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn bg-indigo-600 hover:bg-indigo-500 text-white text-[10px] font-bold px-3 py-1 rounded shadow-xs cursor-pointer">
                                    Push to Shopify
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="text-center py-4 text-xs text-slate-400">
                            All Shopify orders are currently fulfilled & synchronized!
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Push Inventory Levels Card -->
            <div class="card p-5 space-y-4">
                <div class="border-b border-slate-100 pb-2.5">
                    <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        Push Live Stock to Shopify
                    </h3>
                    <p class="text-[11px] text-slate-500">Prevent overselling by updating Shopify warehouse stock</p>
                </div>

                <div class="space-y-2 max-h-72 overflow-y-auto">
                    @foreach($products as $prod)
                        <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200 text-xs flex items-center justify-between gap-2">
                            <div class="truncate max-w-[140px]">
                                <div class="font-bold text-slate-900 truncate">{{ $prod->name }}</div>
                                <div class="text-[10px] text-slate-500 font-mono">SKU: {{ $prod->sku }}</div>
                            </div>
                            <form action="{{ route('shopify.sync.push-inventory', $prod) }}" method="POST" class="flex items-center gap-1.5">
                                @csrf
                                <input type="number" name="stock" value="{{ $prod->total_stock }}" class="w-14 p-1 text-center font-mono font-bold text-xs bg-white rounded border border-slate-300">
                                <button type="submit" class="btn btn-secondary text-[10px] px-2 py-1" title="Push SKU Stock">
                                    Sync
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
