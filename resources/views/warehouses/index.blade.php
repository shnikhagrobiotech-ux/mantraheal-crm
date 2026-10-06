@extends('layouts.app')

@section('title', 'Warehouses & Fulfilment Depots — MantraHeal CRM')

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
                <span class="text-xs text-slate-500">Warehouses</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Fulfillment Depots & Warehouses</h1>
            <p class="text-sm text-slate-500">Manage multi-city stock locations, regional dispatch hubs, and central depot</p>
        </div>
        <button type="button" @click="showCreateModal = true" class="btn btn-primary inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Warehouse
        </button>
    </div>

    <!-- Warehouses Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($warehouses as $wh)
            <div class="card p-6 space-y-4 hover:shadow-md transition-shadow relative">
                @if($wh->is_default)
                    <div class="absolute top-4 right-4">
                        <span class="badge badge-success text-[10px] uppercase font-bold tracking-wider">Default Depot</span>
                    </div>
                @endif

                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-800 flex items-center justify-center font-bold text-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 text-lg leading-tight">
                            <a href="{{ route('warehouses.show', $wh) }}" class="hover:text-emerald-700">
                                {{ $wh->name }}
                            </a>
                        </h3>
                        <div class="text-xs text-slate-400 font-mono">{{ $wh->code }}</div>
                    </div>
                </div>

                <div class="space-y-2 text-xs text-slate-600 border-t border-slate-100 pt-3">
                    <div class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-slate-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>{{ $wh->address }}, {{ $wh->city }}, {{ $wh->state }} - {{ $wh->pincode }}</span>
                    </div>
                    @if($wh->contact_person || $wh->phone)
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span>{{ $wh->contact_person ?? 'Warehouse Manager' }} ({{ $wh->phone ?? 'N/A' }})</span>
                        </div>
                    @endif
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                    <div>
                        <span class="text-xs text-slate-500">Tracked SKUs:</span>
                        <span class="font-bold text-slate-800 text-sm ml-1">{{ $wh->stock_balances_count }}</span>
                    </div>
                    <a href="{{ route('warehouses.show', $wh) }}" class="btn btn-secondary text-xs py-1 px-2.5">
                        View Stock →
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full card p-12 text-center text-slate-500">
                <p>No warehouses registered yet.</p>
            </div>
        @endforelse
    </div>

    <!-- Add Warehouse Modal -->
    <div x-show="showCreateModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 flex items-center justify-center p-4" style="display: none;">
        <div class="card max-w-lg w-full p-6 space-y-4 shadow-2xl bg-white" @click.away="showCreateModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-800">Add New Warehouse / Depot</h3>
                <button type="button" @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <form action="{{ route('warehouses.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Warehouse Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Mumbai Regional Fulfillment Depot" class="form-control w-full text-sm">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Contact Manager</label>
                        <input type="text" name="contact_person" placeholder="Warehouse Incharge" class="form-control w-full text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Phone Number</label>
                        <input type="text" name="phone" placeholder="+91 98765 00000" class="form-control w-full text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Email</label>
                    <input type="email" name="email" placeholder="depot.mumbai@mantraheal.com" class="form-control w-full text-sm">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Physical Address *</label>
                    <textarea name="address" rows="2" required placeholder="Industrial Area, Shed No. 12..." class="form-control w-full text-sm"></textarea>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">City *</label>
                        <input type="text" name="city" required placeholder="Mumbai" class="form-control w-full text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">State *</label>
                        <input type="text" name="state" required placeholder="Maharashtra" class="form-control w-full text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Pincode *</label>
                        <input type="text" name="pincode" required placeholder="400001" class="form-control w-full text-sm">
                    </div>
                </div>

                <div class="pt-2">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_default" value="1" class="w-4 h-4 text-emerald-700 rounded border-slate-300">
                        <span class="text-xs font-medium text-slate-700">Set as Primary Central Distribution Warehouse</span>
                    </label>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="showCreateModal = false" class="btn btn-secondary text-xs">Cancel</button>
                    <button type="submit" class="btn btn-primary text-xs">Save Warehouse</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
