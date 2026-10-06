@extends('layouts.app')

@section('title', 'Goods Received Notes (GRN) — MantraHeal CRM')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xs text-slate-500">Procurement</span>
                <span class="text-slate-300">/</span>
                <span class="text-xs text-slate-500">GRN</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Goods Received Notes (GRN)</h1>
            <p class="text-sm text-slate-500">Warehouse physical intake records, QC damage inspection & batch creation</p>
        </div>
        <div>
            <a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary text-sm">
                View Purchase Orders
            </a>
        </div>
    </div>

    <!-- GRN Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table w-full text-sm">
                <thead>
                    <tr>
                        <th>GRN Number</th>
                        <th>Intake Date</th>
                        <th>PO Reference</th>
                        <th>Supplier</th>
                        <th>Warehouse Depot</th>
                        <th>Vendor Invoice #</th>
                        <th class="text-center">Accepted Qty</th>
                        <th>Received By</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($grns as $grn)
                        <tr class="hover:bg-slate-50/75 transition-colors">
                            <td class="font-mono text-xs font-bold text-emerald-800 whitespace-nowrap">
                                {{ $grn->grn_number }}
                            </td>
                            <td class="text-xs text-slate-500 whitespace-nowrap">
                                {{ $grn->grn_date->format('d M Y') }}
                            </td>
                            <td class="font-mono text-xs whitespace-nowrap">
                                @if($grn->purchaseOrder)
                                    <a href="{{ route('purchase-orders.show', $grn->purchaseOrder) }}" class="text-emerald-700 hover:underline">
                                        {{ $grn->purchaseOrder->po_number }}
                                    </a>
                                @else
                                    <span class="text-slate-400">Direct Intake</span>
                                @endif
                            </td>
                            <td>
                                <div class="font-medium text-slate-800">{{ $grn->supplier?->name }}</div>
                            </td>
                            <td class="text-xs text-slate-600 whitespace-nowrap">
                                {{ $grn->warehouse?->name ?? 'Central Depot' }}
                            </td>
                            <td class="text-xs font-mono text-slate-700 whitespace-nowrap">
                                {{ $grn->invoice_number ?? '—' }}
                            </td>
                            <td class="text-center font-bold text-emerald-800">
                                {{ $grn->items->sum('accepted_quantity') }}
                            </td>
                            <td class="text-xs text-slate-500 whitespace-nowrap">
                                {{ $grn->receiver?->name ?? 'Storekeeper' }}
                            </td>
                            <td class="text-xs text-slate-500 max-w-xs truncate" title="{{ $grn->remarks }}">
                                {{ $grn->remarks ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-12 text-slate-400 text-sm">
                                No goods received notes logged yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($grns->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $grns->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
