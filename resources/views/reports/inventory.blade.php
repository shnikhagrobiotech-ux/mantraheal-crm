@extends('layouts.app')

@section('title', 'Inventory Valuation & Aging Report — MantraHeal CRM')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xs text-slate-500">Business Intelligence</span>
                <span class="text-slate-300">/</span>
                <span class="text-xs text-slate-500">Reports</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Inventory Valuation & Stock Aging</h1>
            <p class="text-sm text-slate-500">Comprehensive audit of tied working capital, stockouts, and near-expiry herbal lots</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('inventory.ledger') }}" class="btn btn-secondary text-sm">
                Stock Ledger
            </a>
            <a href="{{ route('batches.expiry-alerts') }}" class="btn btn-primary text-sm inline-flex items-center gap-1.5">
                FEFO Expiry Alerts
            </a>
        </div>
    </div>

    <!-- Inventory KPIs -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card p-5">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Inventory Valuation</div>
            <div class="text-2xl font-black text-emerald-800 mt-1">₹{{ number_format($reports['total_valuation'], 2) }}</div>
            <div class="text-xs text-slate-400 mt-0.5">At actual landed purchase cost</div>
        </div>

        <div class="card p-5">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Catalog SKUs</div>
            <div class="text-2xl font-black text-slate-800 mt-1">{{ $reports['total_products'] }}</div>
            <div class="text-xs text-slate-400 mt-0.5">Active formulations in system</div>
        </div>

        <div class="card p-5">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Low Stock SKUs</div>
            <div class="text-2xl font-black text-amber-600 mt-1">{{ $reports['low_stock_count'] }}</div>
            <div class="text-xs text-amber-700 mt-0.5">Below buffer reorder threshold</div>
        </div>

        <div class="card p-5">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Stockout SKUs</div>
            <div class="text-2xl font-black text-rose-700 mt-1">{{ $reports['out_of_stock_count'] }}</div>
            <div class="text-xs text-rose-700 mt-0.5">Zero physical availability</div>
        </div>
    </div>

    <!-- Near Expiry Batches Table -->
    <div class="card overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold text-slate-800 text-sm">Batches Expiring Within 60 Days (FEFO Priority)</h2>
            <span class="text-xs text-amber-700 font-semibold">{{ $reports['expiring_batches']->count() }} batches need urgent dispatch</span>
        </div>
        <div class="overflow-x-auto">
            <table class="table w-full text-sm">
                <thead>
                    <tr>
                        <th>Batch Number</th>
                        <th>Product / Formulation</th>
                        <th>Expiry Date</th>
                        <th class="text-center">Days Remaining</th>
                        <th class="text-center">Current Quantity</th>
                        <th class="text-right">Tied Value (₹)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($reports['expiring_batches'] as $b)
                        @php
                            $days = now()->diffInDays(\Carbon\Carbon::parse($b->expiry_date), false);
                        @endphp
                        <tr class="hover:bg-slate-50/75 transition-colors">
                            <td class="font-mono text-xs font-bold text-amber-800">{{ $b->batch_number }}</td>
                            <td class="font-medium text-slate-800">{{ $b->product?->name }}</td>
                            <td class="text-xs font-semibold text-amber-700">{{ \Carbon\Carbon::parse($b->expiry_date)->format('d M Y') }}</td>
                            <td class="text-center font-bold text-amber-700">{{ $days }} days</td>
                            <td class="text-center font-bold text-slate-800">{{ $b->current_quantity }}</td>
                            <td class="text-right font-bold text-slate-900">₹{{ number_format($b->current_quantity * $b->cost_price, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-6 text-slate-400 text-xs">No batches expiring within 60 days.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
