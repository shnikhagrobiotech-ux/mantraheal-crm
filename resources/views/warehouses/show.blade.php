@extends('layouts.app')

@section('title', "{$warehouse->name} — MantraHeal CRM")

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('warehouses.index') }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Warehouses
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-xs text-slate-500 font-mono">{{ $warehouse->code }}</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">{{ $warehouse->name }}</h1>
                @if($warehouse->is_default)
                    <span class="badge badge-success text-xs">Primary Depot</span>
                @endif
                <span class="badge bg-slate-100 text-slate-700 text-xs">{{ $warehouse->city }}, {{ $warehouse->state }}</span>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('inventory.ledger', ['warehouse_id' => $warehouse->id]) }}" class="btn btn-secondary text-sm inline-flex items-center gap-1.5">
                Warehouse Stock Ledger
            </a>
        </div>
    </div>

    <!-- Warehouse Info Card -->
    <div class="card p-6 grid grid-cols-1 md:grid-cols-4 gap-6 text-sm">
        <div>
            <div class="text-xs text-slate-400 uppercase font-semibold">Address</div>
            <div class="font-medium text-slate-800 mt-1">{{ $warehouse->address }}</div>
            <div class="text-slate-500 text-xs">{{ $warehouse->city }}, {{ $warehouse->state }} - {{ $warehouse->pincode }}</div>
        </div>
        <div>
            <div class="text-xs text-slate-400 uppercase font-semibold">Manager / Contact</div>
            <div class="font-medium text-slate-800 mt-1">{{ $warehouse->contact_person ?? 'Not Assigned' }}</div>
            <div class="text-slate-500 text-xs">{{ $warehouse->phone ?? 'N/A' }}</div>
        </div>
        <div>
            <div class="text-xs text-slate-400 uppercase font-semibold">Email Contact</div>
            <div class="font-medium text-slate-800 mt-1">{{ $warehouse->email ?? 'N/A' }}</div>
        </div>
        <div>
            <div class="text-xs text-slate-400 uppercase font-semibold">Total SKUs Stored</div>
            <div class="text-2xl font-bold text-emerald-800 mt-0.5">{{ $warehouse->stockBalances->count() }}</div>
        </div>
    </div>

    <!-- Inventory in this Warehouse -->
    <div class="card overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold text-slate-800 text-sm">Inventory Balances in {{ $warehouse->name }}</h2>
            <span class="text-xs text-slate-500">{{ $warehouse->stockBalances->sum('quantity') }} total physical units</span>
        </div>
        <div class="overflow-x-auto">
            <table class="table w-full text-sm">
                <thead>
                    <tr>
                        <th>Product / Formulation</th>
                        <th>SKU</th>
                        <th>Category</th>
                        <th class="text-center">Physical Qty</th>
                        <th class="text-center">Reserved</th>
                        <th class="text-center">Available Stock</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($warehouse->stockBalances as $sb)
                        <tr>
                            <td>
                                <div class="font-semibold text-slate-800">
                                    <a href="{{ route('products.show', $sb->product_id) }}" class="hover:text-emerald-700">
                                        {{ $sb->product?->name }}
                                    </a>
                                </div>
                            </td>
                            <td class="font-mono text-xs text-slate-600">{{ $sb->product?->sku }}</td>
                            <td>
                                <span class="badge bg-slate-100 text-slate-700 text-xs">{{ $sb->product?->category?->name ?? 'General' }}</span>
                            </td>
                            <td class="text-center font-bold text-slate-800">{{ $sb->quantity }}</td>
                            <td class="text-center text-slate-500">{{ $sb->reserved_quantity }}</td>
                            <td class="text-center font-bold text-emerald-800">{{ $sb->quantity - $sb->reserved_quantity }}</td>
                            <td class="text-right">
                                <a href="{{ route('inventory.ledger', ['product_id' => $sb->product_id, 'warehouse_id' => $warehouse->id]) }}" class="btn btn-secondary text-xs py-1 px-2.5">
                                    Ledger
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-slate-400 text-xs">
                                No stock balances recorded in this warehouse depot.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
