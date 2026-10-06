@extends('layouts.app')

@section('title', 'Batch Expiry Alerts & FEFO Warning System — MantraHeal CRM')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('batches.index') }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Batches
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-xs text-slate-500">Expiry Management</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">FEFO Batch Expiry Alerts</h1>
            <p class="text-sm text-slate-500">Monitor near-expiry herbal formulations to prioritize dispatch (First Expiry, First Out)</p>
        </div>
        <div>
            <a href="{{ route('inventory.index') }}" class="btn btn-secondary text-sm">
                Stock Balances
            </a>
        </div>
    </div>

    <!-- Expiry Alert Tabs & Counter Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="{{ route('batches.expiry-alerts', ['filter' => 'expired']) }}" 
           class="card p-4 transition-all border-l-4 {{ $filter === 'expired' ? 'border-l-rose-600 bg-rose-50/30 ring-2 ring-rose-500' : 'border-l-rose-400 hover:bg-slate-50' }}">
            <div class="text-xs font-bold text-rose-700 uppercase tracking-wider">Expired Batches</div>
            <div class="text-2xl font-black text-rose-800 mt-1">{{ $counts['expired'] }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">Unsellable inventory</div>
        </a>

        <a href="{{ route('batches.expiry-alerts', ['filter' => '30']) }}" 
           class="card p-4 transition-all border-l-4 {{ $filter === '30' ? 'border-l-amber-600 bg-amber-50/30 ring-2 ring-amber-500' : 'border-l-amber-400 hover:bg-slate-50' }}">
            <div class="text-xs font-bold text-amber-700 uppercase tracking-wider">Expiring in &lt;30 Days</div>
            <div class="text-2xl font-black text-amber-800 mt-1">{{ $counts['30'] }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">Critical FEFO priority</div>
        </a>

        <a href="{{ route('batches.expiry-alerts', ['filter' => '60']) }}" 
           class="card p-4 transition-all border-l-4 {{ $filter === '60' ? 'border-l-yellow-600 bg-yellow-50/30 ring-2 ring-yellow-500' : 'border-l-yellow-400 hover:bg-slate-50' }}">
            <div class="text-xs font-bold text-yellow-700 uppercase tracking-wider">Expiring in 60 Days</div>
            <div class="text-2xl font-black text-slate-800 mt-1">{{ $counts['60'] }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">Promotional bundling target</div>
        </a>

        <a href="{{ route('batches.expiry-alerts', ['filter' => '90']) }}" 
           class="card p-4 transition-all border-l-4 {{ $filter === '90' ? 'border-l-emerald-600 bg-emerald-50/30 ring-2 ring-emerald-500' : 'border-l-emerald-400 hover:bg-slate-50' }}">
            <div class="text-xs font-bold text-emerald-800 uppercase tracking-wider">Expiring in 90 Days</div>
            <div class="text-2xl font-black text-slate-800 mt-1">{{ $counts['90'] }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">Normal sales velocity queue</div>
        </a>
    </div>

    <!-- Batches Listing -->
    <div class="card overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold text-slate-800 text-sm">
                Batches in Category: 
                <span class="text-emerald-800 uppercase">{{ $filter === 'expired' ? 'Expired' : "Expiring in <= {$filter} Days" }}</span>
            </h2>
            <span class="text-xs text-slate-500">{{ $batches->total() }} matching batches</span>
        </div>
        <div class="overflow-x-auto">
            <table class="table w-full text-sm">
                <thead>
                    <tr>
                        <th>Batch #</th>
                        <th>Product / SKU</th>
                        <th>Depot Warehouse</th>
                        <th>Expiry Date</th>
                        <th class="text-center">Days Remaining</th>
                        <th class="text-center">Remaining Qty</th>
                        <th class="text-right">Tied Capital (₹)</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($batches as $b)
                        @php
                            $isPast = $b->expiry_date && \Carbon\Carbon::parse($b->expiry_date)->isPast();
                            $days = $b->expiry_date ? now()->diffInDays(\Carbon\Carbon::parse($b->expiry_date), false) : 0;
                            $tiedCapital = $b->current_quantity * $b->cost_price;
                        @endphp
                        <tr class="hover:bg-slate-50/75 transition-colors">
                            <td class="font-mono text-xs font-bold text-slate-800">
                                {{ $b->batch_number }}
                            </td>
                            <td>
                                <div class="font-medium text-slate-800">{{ $b->product?->name }}</div>
                                <div class="text-xs text-slate-400 font-mono">{{ $b->product?->sku }}</div>
                            </td>
                            <td class="text-xs text-slate-600">{{ $b->warehouse?->name ?? 'Central' }}</td>
                            <td class="text-xs font-medium {{ $isPast ? 'text-rose-600' : 'text-slate-800' }}">
                                {{ $b->expiry_date ? \Carbon\Carbon::parse($b->expiry_date)->format('d M Y') : '—' }}
                            </td>
                            <td class="text-center font-bold {{ $isPast ? 'text-rose-700' : ($days <= 30 ? 'text-rose-600' : 'text-amber-600') }}">
                                {{ $isPast ? 'EXPIRED' : $days . ' days' }}
                            </td>
                            <td class="text-center font-black text-slate-800">{{ $b->current_quantity }}</td>
                            <td class="text-right text-xs font-semibold text-slate-700">₹{{ number_format($tiedCapital, 2) }}</td>
                            <td class="text-right whitespace-nowrap">
                                <a href="{{ route('inventory.ledger', ['product_id' => $b->product_id]) }}" class="btn btn-secondary text-xs py-1 px-2.5">
                                    Ledger
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-12 text-slate-400 text-sm">
                                <div class="max-w-sm mx-auto space-y-2">
                                    <div class="text-emerald-700 font-medium">✓ All Clear!</div>
                                    <p class="text-xs text-slate-500">No active batches meet the selected expiry alert filter.</p>
                                </div>
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
</div>
@endsection
