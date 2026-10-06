@extends('layouts.app')

@section('title', 'Inventory & Stock Management — MantraHeal CRM')

@section('content')
<div class="space-y-6" x-data="{ showAdjustModal: false, selectedProduct: null }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Inventory & Stock Balances</h1>
            <p class="text-sm text-slate-500">Multi-warehouse stock tracking, real-time availability & batch balances</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('inventory.ledger') }}" class="btn btn-secondary text-sm inline-flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Stock Ledger
            </a>
            <a href="{{ route('batches.expiry-alerts') }}" class="btn btn-secondary text-sm inline-flex items-center gap-1.5 text-amber-700">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                Expiry Alerts
            </a>
            <button type="button" @click="showAdjustModal = true" class="btn btn-primary text-sm inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Adjust Stock
            </button>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="card p-4">
        <form method="GET" action="{{ route('inventory.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="lg:col-span-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by product name or SKU..." class="form-control text-sm w-full">
            </div>
            <div>
                <select name="warehouse_id" class="form-control text-sm w-full">
                    <option value="">All Warehouses</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>
                            {{ $wh->name }} ({{ $wh->city }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="btn btn-primary text-sm py-2 px-4 flex-1">Filter</button>
                <a href="{{ route('inventory.index') }}" class="btn btn-secondary text-sm py-2 px-3">Reset</a>
            </div>
        </form>
    </div>

    <!-- Inventory Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr>
                        <th>Product / Formulation</th>
                        <th>SKU</th>
                        <th>Category</th>
                        <th class="text-center">Physical Qty</th>
                        <th class="text-center">Reserved</th>
                        <th class="text-center">Available Stock</th>
                        <th class="text-center">Min / Reorder</th>
                        <th>Stock Health</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $product)
                        @php
                            $stockBalances = $product->stockBalances;
                            if (request('warehouse_id')) {
                                $stockBalances = $stockBalances->where('warehouse_id', request('warehouse_id'));
                            }
                            $physical = $stockBalances->sum('quantity');
                            $reserved = $stockBalances->sum('reserved_quantity');
                            $available = $physical - $reserved;
                            $minStock = $product->minimum_stock ?? 15;
                        @endphp
                        <tr class="hover:bg-slate-50/75 transition-colors">
                            <td>
                                <div class="font-semibold text-slate-800">
                                    <a href="{{ route('products.show', $product) }}" class="hover:text-emerald-700">
                                        {{ $product->name }}
                                    </a>
                                </div>
                                <div class="text-xs text-slate-400 font-mono">{{ $product->product_code }}</div>
                            </td>
                            <td class="font-mono text-xs text-slate-600 font-medium">
                                {{ $product->sku }}
                            </td>
                            <td>
                                <span class="badge bg-slate-100 text-slate-700 text-xs">
                                    {{ $product->category?->name ?? 'General' }}
                                </span>
                            </td>
                            <td class="text-center font-semibold text-slate-800">{{ $physical }}</td>
                            <td class="text-center text-slate-500">{{ $reserved }}</td>
                            <td class="text-center font-bold {{ $available <= 0 ? 'text-rose-600' : ($available <= $minStock ? 'text-amber-600' : 'text-emerald-800') }}">
                                {{ $available }} units
                            </td>
                            <td class="text-center text-xs text-slate-500 font-mono">
                                {{ $minStock }} / {{ $product->reorder_level ?? 30 }}
                            </td>
                            <td>
                                @if($available <= 0)
                                    <span class="badge badge-danger text-xs">Out of Stock</span>
                                @elseif($available <= $minStock)
                                    <span class="badge badge-warning text-xs">Low Stock Alert</span>
                                @else
                                    <span class="badge badge-success text-xs">Healthy</span>
                                @endif
                            </td>
                            <td class="text-right whitespace-nowrap space-x-1">
                                <a href="{{ route('inventory.ledger', ['product_id' => $product->id]) }}" class="btn btn-secondary text-xs py-1 px-2.5" title="View Audit Ledger">
                                    Ledger
                                </a>
                                <button type="button" @click="selectedProduct = {{ json_encode($product) }}; showAdjustModal = true" class="btn btn-secondary text-xs py-1 px-2.5 text-emerald-700 hover:bg-emerald-50">
                                    Adjust
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-12 text-slate-500">
                                No inventory items found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $products->links() }}
            </div>
        @endif
    </div>

    <!-- Stock Adjustment Modal -->
    <div x-show="showAdjustModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 flex items-center justify-center p-4" style="display: none;">
        <div class="card max-w-lg w-full p-6 space-y-4 shadow-2xl bg-white" @click.away="showAdjustModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-800">Record Stock Adjustment</h3>
                <button type="button" @click="showAdjustModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            
            <form action="{{ route('inventory.adjust') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Product Formulation *</label>
                    <select name="product_id" required class="form-control w-full text-sm">
                        <option value="">-- Choose Product --</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" :selected="selectedProduct && selectedProduct.id == {{ $p->id }}">
                                {{ $p->name }} ({{ $p->sku }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Target Warehouse *</label>
                    <select name="warehouse_id" required class="form-control w-full text-sm">
                        <option value="">-- Choose Warehouse Depot --</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->city }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Movement Type *</label>
                        <select name="movement_type" required class="form-control w-full text-sm">
                            <option value="adjustment">Stock Adjustment</option>
                            <option value="damage">Damaged Stock</option>
                            <option value="opening">Opening Stock Setup</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Adjustment Qty *</label>
                        <input type="number" name="quantity" placeholder="e.g. +20 or -5" required class="form-control w-full text-sm font-mono">
                        <span class="text-[10px] text-slate-400">Use positive to add, negative to deduct</span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Reason / Audit Trail Notes *</label>
                    <textarea name="notes" rows="2" required placeholder="Physical audit discrepancy, water damage in bay 3, etc." class="form-control w-full text-sm"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showAdjustModal = false" class="btn btn-secondary text-xs">Cancel</button>
                    <button type="submit" class="btn btn-primary text-xs">Post Stock Movement</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
