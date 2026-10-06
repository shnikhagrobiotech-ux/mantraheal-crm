@extends('layouts.app')

@section('title', 'Stock Ledger & Movement Audit Trail — MantraHeal CRM')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('inventory.index') }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Inventory
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-xs text-slate-500">Stock Ledger</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Immutable Stock Ledger</h1>
            <p class="text-sm text-slate-500">Audit-grade record of every physical inbound, outbound, and adjustment movement</p>
        </div>
        <div>
            <a href="{{ route('inventory.index') }}" class="btn btn-secondary text-sm inline-flex items-center gap-1.5">
                Current Stock View
            </a>
        </div>
    </div>

    <!-- Formula Explainer Banner -->
    <div class="bg-emerald-900 text-white rounded-xl p-4 shadow-sm flex flex-col md:flex-row items-center justify-between gap-3 text-xs">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center font-bold text-amber-300">Σ</div>
            <div>
                <span class="font-bold text-amber-300">Stock Ledger Formula:</span>
                <span class="text-emerald-100 font-mono ml-1">Opening + Purchase + Return + Transfer In - Sales - Damage - Transfer Out - Adjustment = Balance</span>
            </div>
        </div>
        <div class="text-emerald-200 text-[11px]">
            Strict FIFO/FEFO Accounting Rule Active
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card p-4">
        <form method="GET" action="{{ route('inventory.ledger') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <select name="product_id" class="form-control text-sm w-full">
                    <option value="">All Products</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->name }} ({{ $p->sku }})
                        </option>
                    @endforeach
                </select>
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
            <div>
                <select name="movement_type" class="form-control text-sm w-full">
                    <option value="">All Movement Types</option>
                    @foreach($types as $t)
                        <option value="{{ $t }}" {{ request('movement_type') == $t ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_', ' ', $t)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="btn btn-primary text-sm py-2 px-4 flex-1">Filter</button>
                <a href="{{ route('inventory.ledger') }}" class="btn btn-secondary text-sm py-2 px-3">Reset</a>
            </div>
        </form>
    </div>

    <!-- Ledger Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table w-full text-sm">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Product / SKU</th>
                        <th>Warehouse</th>
                        <th>Type</th>
                        <th class="text-center">Qty Change</th>
                        <th>Batch #</th>
                        <th>Reference / Document</th>
                        <th>Authorized By</th>
                        <th>Notes / Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($movements as $m)
                        <tr class="hover:bg-slate-50/75 transition-colors">
                            <td class="whitespace-nowrap text-xs text-slate-500 font-mono">
                                {{ $m->created_at->format('d M Y, h:i A') }}
                            </td>
                            <td>
                                <div class="font-medium text-slate-800">
                                    <a href="{{ route('products.show', $m->product_id) }}" class="hover:text-emerald-700">
                                        {{ $m->product?->name }}
                                    </a>
                                </div>
                                <div class="text-xs text-slate-400 font-mono">{{ $m->product?->sku }}</div>
                            </td>
                            <td class="text-xs text-slate-600 whitespace-nowrap">
                                {{ $m->warehouse?->name ?? 'Central' }}
                            </td>
                            <td>
                                @php
                                    $mBadge = match($m->movement_type) {
                                        'purchase', 'opening', 'customer_return', 'transfer_in' => 'badge-success',
                                        'sale' => 'badge-info',
                                        'damage' => 'badge-danger',
                                        default => 'badge-warning'
                                    };
                                @endphp
                                <span class="badge {{ $mBadge }} text-xs capitalize whitespace-nowrap">
                                    {{ str_replace('_', ' ', $m->movement_type) }}
                                </span>
                            </td>
                            <td class="text-center font-bold font-mono whitespace-nowrap {{ $m->quantity > 0 ? 'text-emerald-700' : 'text-rose-600' }}">
                                {{ $m->quantity > 0 ? '+' : '' }}{{ $m->quantity }}
                            </td>
                            <td class="font-mono text-xs text-slate-600 whitespace-nowrap">
                                {{ $m->batch?->batch_number ?? '—' }}
                            </td>
                            <td class="text-xs text-slate-600 whitespace-nowrap">
                                <span class="font-mono">{{ $m->reference_type ?? 'Manual' }}</span>
                                @if($m->reference_id)
                                    <span class="text-slate-400">#{{ $m->reference_id }}</span>
                                @endif
                            </td>
                            <td class="text-xs text-slate-500 whitespace-nowrap">
                                {{ $m->user?->name ?? 'System' }}
                            </td>
                            <td class="text-xs text-slate-500 max-w-xs truncate" title="{{ $m->notes }}">
                                {{ $m->notes ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-12 text-slate-400 text-sm">
                                No stock movement records match the selected filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($movements->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $movements->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
