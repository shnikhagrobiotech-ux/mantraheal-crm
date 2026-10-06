@extends('layouts.app')

@section('title', "{$product->name} — MantraHeal CRM")

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('products.index') }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Catalog
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-xs text-slate-500 font-mono">{{ $product->sku }}</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">{{ $product->name }}</h1>
                @if($product->is_active)
                    <span class="badge badge-success text-xs">Active</span>
                @else
                    <span class="badge badge-secondary text-xs">Inactive</span>
                @endif
                <span class="badge bg-emerald-50 text-emerald-800 text-xs">{{ $product->category?->name ?? 'General' }}</span>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('inventory.ledger', ['product_id' => $product->id]) }}" class="btn btn-secondary text-sm inline-flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Stock Ledger
            </a>
            <a href="{{ route('purchase-orders.create') }}" class="btn btn-secondary text-sm inline-flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Create PO
            </a>
            <a href="{{ route('products.edit', $product) }}" class="btn btn-primary text-sm inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Product
            </a>
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card p-4">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Selling Price</div>
            <div class="text-2xl font-bold text-slate-800 mt-1">₹{{ number_format($product->selling_price, 2) }}</div>
            <div class="text-xs text-slate-400 mt-0.5">MRP: ₹{{ number_format($product->mrp, 2) }}</div>
        </div>

        <div class="card p-4">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Cost / Purchase Price</div>
            <div class="text-2xl font-bold text-slate-700 mt-1">₹{{ number_format($product->purchase_price, 2) }}</div>
            @php
                $margin = $product->selling_price > 0 ? (($product->selling_price - $product->purchase_price) / $product->selling_price) * 100 : 0;
            @endphp
            <div class="text-xs text-emerald-600 font-semibold mt-0.5">Gross Margin: {{ number_format($margin, 1) }}%</div>
        </div>

        <div class="card p-4">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Available Stock</div>
            <div class="text-2xl font-bold text-emerald-800 mt-1">{{ $product->stock_quantity ?? 0 }} units</div>
            <div class="text-xs text-slate-500 mt-0.5">Across all registered warehouses</div>
        </div>

        <div class="card p-4">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">GST Rate & HSN</div>
            <div class="text-2xl font-bold text-slate-800 mt-1">{{ $product->gst_percent ?? 12 }}%</div>
            <div class="text-xs text-slate-500 font-mono mt-0.5">HSN: {{ $product->hsn_code ?? '30049011' }}</div>
        </div>
    </div>

    <!-- Detailed 2-Column Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left 2 Cols: Warehouse Distribution & Batches -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Warehouse Stock Distribution -->
            <div class="card overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <h2 class="font-bold text-slate-800 text-sm">Warehouse Stock Balances</h2>
                    <span class="text-xs text-slate-500">Multi-warehouse inventory</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="table w-full text-sm">
                        <thead>
                            <tr>
                                <th>Warehouse</th>
                                <th>Location</th>
                                <th class="text-center">Physical Qty</th>
                                <th class="text-center">Reserved Qty</th>
                                <th class="text-center">Available Qty</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($product->stockBalances as $sb)
                                <tr>
                                    <td class="font-medium text-slate-800">
                                        <a href="{{ route('warehouses.show', $sb->warehouse) }}" class="hover:text-emerald-700">
                                            {{ $sb->warehouse?->name ?? 'Primary Depot' }}
                                        </a>
                                    </td>
                                    <td class="text-xs text-slate-500">{{ $sb->warehouse?->city ?? 'N/A' }}</td>
                                    <td class="text-center font-bold text-slate-800">{{ $sb->quantity }}</td>
                                    <td class="text-center text-slate-500">{{ $sb->reserved_quantity }}</td>
                                    <td class="text-center font-bold text-emerald-800">{{ $sb->quantity - $sb->reserved_quantity }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-6 text-slate-400 text-xs">
                                        No warehouse stock balance records created yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Batches & Expiry (FEFO) -->
            <div class="card overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <h2 class="font-bold text-slate-800 text-sm">Active Batches & FEFO Expiry</h2>
                    <a href="{{ route('batches.expiry-alerts') }}" class="text-xs font-semibold text-emerald-700 hover:underline">
                        Expiry Dashboard →
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="table w-full text-sm">
                        <thead>
                            <tr>
                                <th>Batch No</th>
                                <th>Warehouse</th>
                                <th>Mfg Date</th>
                                <th>Expiry Date</th>
                                <th class="text-center">Quantity</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($product->batches as $batch)
                                @php
                                    $isExpired = $batch->expiry_date && \Carbon\Carbon::parse($batch->expiry_date)->isPast();
                                    $isNearExpiry = $batch->expiry_date && \Carbon\Carbon::parse($batch->expiry_date)->diffInDays(now()) <= 60;
                                @endphp
                                <tr>
                                    <td class="font-mono text-xs font-bold text-slate-800">{{ $batch->batch_number }}</td>
                                    <td class="text-xs text-slate-600">{{ $batch->warehouse?->name ?? 'Central' }}</td>
                                    <td class="text-xs text-slate-500">{{ $batch->mfg_date ? \Carbon\Carbon::parse($batch->mfg_date)->format('d M Y') : '—' }}</td>
                                    <td class="text-xs font-medium {{ $isExpired ? 'text-rose-600 font-bold' : ($isNearExpiry ? 'text-amber-600 font-bold' : 'text-slate-700') }}">
                                        {{ $batch->expiry_date ? \Carbon\Carbon::parse($batch->expiry_date)->format('d M Y') : '—' }}
                                    </td>
                                    <td class="text-center font-bold text-slate-800">{{ $batch->current_quantity }}</td>
                                    <td>
                                        @if($isExpired)
                                            <span class="badge badge-danger text-[10px]">Expired</span>
                                        @elseif($isNearExpiry)
                                            <span class="badge badge-warning text-[10px]">Expiring Soon</span>
                                        @else
                                            <span class="badge badge-success text-[10px]">Healthy</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-6 text-slate-400 text-xs">
                                        No active batches found for this formulation.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Right Col: Product Info Card & Thresholds -->
        <div class="space-y-6">
            <div class="card p-5 space-y-4">
                <h2 class="font-bold text-slate-800 text-sm border-b border-slate-100 pb-2">Specification & Codes</h2>

                <div class="space-y-2.5 text-xs">
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Internal Code:</span>
                        <span class="font-mono font-medium text-slate-800">{{ $product->product_code }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">SKU:</span>
                        <span class="font-mono font-medium text-slate-800">{{ $product->sku }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Brand:</span>
                        <span class="font-medium text-slate-800">{{ $product->brand }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Barcode / EAN:</span>
                        <span class="font-mono text-slate-800">{{ $product->barcode ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Min Buffer Stock:</span>
                        <span class="font-bold text-amber-700">{{ $product->minimum_stock }} units</span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-slate-500">Reorder Threshold:</span>
                        <span class="font-bold text-emerald-800">{{ $product->reorder_level }} units</span>
                    </div>
                </div>

                @if($product->description)
                    <div class="pt-3 border-t border-slate-100">
                        <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Description</div>
                        <p class="text-xs text-slate-600 leading-relaxed">{{ $product->description }}</p>
                    </div>
                @endif
            </div>

            <!-- Danger Zone / Delete -->
            <div class="card p-4 border-rose-100 bg-rose-50/20">
                <div class="text-xs font-bold text-rose-800 mb-1">Danger Zone</div>
                <p class="text-[11px] text-slate-500 mb-3">Deleting this product removes it from the catalog. Existing orders remain recorded.</p>
                <form action="{{ route('products.destroy', $product) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this product?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-secondary text-xs text-rose-600 hover:bg-rose-50 border-rose-200 w-full">
                        Delete Product SKU
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
