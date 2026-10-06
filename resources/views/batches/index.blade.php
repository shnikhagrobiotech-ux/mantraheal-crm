@extends('layouts.app')

@section('title', 'Product Batches & FEFO Tracking — MantraHeal CRM')

@section('content')
<div class="space-y-6" x-data="{ showCreateModal: false }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('inventory.index') }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Inventory
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-xs text-slate-500">Batches</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Batch Tracking (FEFO Management)</h1>
            <p class="text-sm text-slate-500">Track herbal manufacturing lots, expiry schedules, and batch cost valuation</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('batches.expiry-alerts') }}" class="btn btn-secondary text-sm inline-flex items-center gap-1.5 text-amber-700">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                Expiry Alerts
            </a>
            <button type="button" @click="showCreateModal = true" class="btn btn-primary text-sm inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Create Batch
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card p-4">
        <form method="GET" action="{{ route('batches.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Batch # or product name..." class="form-control text-sm w-full">
            </div>
            <div>
                <select name="product_id" class="form-control text-sm w-full">
                    <option value="">All Products</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="warehouse_id" class="form-control text-sm w-full">
                    <option value="">All Warehouses</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="btn btn-primary text-sm py-2 px-4 flex-1">Filter</button>
                <a href="{{ route('batches.index') }}" class="btn btn-secondary text-sm py-2 px-3">Reset</a>
            </div>
        </form>
    </div>

    <!-- Batches Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table w-full text-sm">
                <thead>
                    <tr>
                        <th>Batch Number</th>
                        <th>Product / Formulation</th>
                        <th>Warehouse</th>
                        <th>Mfg Date</th>
                        <th>Expiry Date</th>
                        <th class="text-right">Unit Cost (₹)</th>
                        <th class="text-center">Initial Qty</th>
                        <th class="text-center">Current Qty</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($batches as $batch)
                        @php
                            $isExpired = $batch->expiry_date && \Carbon\Carbon::parse($batch->expiry_date)->isPast();
                            $daysLeft = $batch->expiry_date ? now()->diffInDays(\Carbon\Carbon::parse($batch->expiry_date), false) : 999;
                        @endphp
                        <tr class="hover:bg-slate-50/75 transition-colors">
                            <td class="font-mono text-xs font-bold text-slate-800">
                                {{ $batch->batch_number }}
                            </td>
                            <td>
                                <div class="font-medium text-slate-800">
                                    <a href="{{ route('products.show', $batch->product_id) }}" class="hover:text-emerald-700">
                                        {{ $batch->product?->name }}
                                    </a>
                                </div>
                                <div class="text-xs text-slate-400 font-mono">{{ $batch->product?->sku }}</div>
                            </td>
                            <td class="text-xs text-slate-600">
                                {{ $batch->warehouse?->name ?? 'Central Depot' }}
                            </td>
                            <td class="text-xs text-slate-500 whitespace-nowrap">
                                {{ $batch->manufacturing_date ? \Carbon\Carbon::parse($batch->manufacturing_date)->format('d M Y') : '—' }}
                            </td>
                            <td class="text-xs whitespace-nowrap font-medium {{ $isExpired ? 'text-rose-600 font-bold' : ($daysLeft <= 60 ? 'text-amber-600 font-bold' : 'text-slate-700') }}">
                                {{ $batch->expiry_date ? \Carbon\Carbon::parse($batch->expiry_date)->format('d M Y') : '—' }}
                                @if(!$isExpired && $daysLeft <= 90)
                                    <span class="text-[10px] block text-amber-600 font-normal">({{ $daysLeft }} days left)</span>
                                @endif
                            </td>
                            <td class="text-right text-slate-700">₹{{ number_format($batch->cost_price, 2) }}</td>
                            <td class="text-center text-slate-500">{{ $batch->initial_quantity }}</td>
                            <td class="text-center font-bold text-slate-800">{{ $batch->current_quantity }}</td>
                            <td>
                                @if($batch->current_quantity <= 0)
                                    <span class="badge bg-slate-100 text-slate-600 text-[10px]">Exhausted</span>
                                @elseif($isExpired)
                                    <span class="badge badge-danger text-[10px]">Expired</span>
                                @elseif($daysLeft <= 30)
                                    <span class="badge badge-danger text-[10px]">Expires in &lt;30d</span>
                                @elseif($daysLeft <= 60)
                                    <span class="badge badge-warning text-[10px]">Expires in &lt;60d</span>
                                @else
                                    <span class="badge badge-success text-[10px]">Active</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-12 text-slate-400 text-sm">
                                No batch records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($batches->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $batches->links() }}
            </div>
        @endif
    </div>

    <!-- Create Batch Modal -->
    <div x-show="showCreateModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 flex items-center justify-center p-4" style="display: none;">
        <div class="card max-w-lg w-full p-6 space-y-4 shadow-2xl bg-white" @click.away="showCreateModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-800">Add New Manufacturing Batch</h3>
                <button type="button" @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            
            <form action="{{ route('batches.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Batch Number *</label>
                        <input type="text" name="batch_number" required placeholder="e.g. MH2026-B08" class="form-control w-full text-sm font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Warehouse *</label>
                        <select name="warehouse_id" required class="form-control w-full text-sm">
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Product Formulation *</label>
                    <select name="product_id" required class="form-control w-full text-sm">
                        <option value="">-- Select Product --</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" data-purchase="{{ $p->purchase_price }}" data-sell="{{ $p->selling_price }}">
                                {{ $p->name }} ({{ $p->sku }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Manufacturing Date *</label>
                        <input type="date" name="manufacturing_date" value="{{ date('Y-m-d') }}" required class="form-control w-full text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Expiry Date *</label>
                        <input type="date" name="expiry_date" value="{{ date('Y-m-d', strtotime('+2 years')) }}" required class="form-control w-full text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Initial Qty *</label>
                        <input type="number" name="initial_quantity" min="1" value="100" required class="form-control w-full text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Cost Price (₹) *</label>
                        <input type="number" step="0.01" name="cost_price" value="250" required class="form-control w-full text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Selling Price (₹) *</label>
                        <input type="number" step="0.01" name="selling_price" value="699" required class="form-control w-full text-sm">
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="showCreateModal = false" class="btn btn-secondary text-xs">Cancel</button>
                    <button type="submit" class="btn btn-primary text-xs">Create Batch & Post Opening Stock</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
