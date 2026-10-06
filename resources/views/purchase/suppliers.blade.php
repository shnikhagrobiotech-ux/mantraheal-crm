@extends('layouts.app')

@section('title', 'Suppliers & Raw Material Vendors — MantraHeal CRM')

@section('content')
<div class="space-y-6" x-data="{ showCreateModal: false }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xs text-slate-500">Purchase</span>
                <span class="text-slate-300">/</span>
                <span class="text-xs text-slate-500">Suppliers</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Suppliers & Herb Cultivators</h1>
            <p class="text-sm text-slate-500">Manage raw herb suppliers, packaging vendors, and manufacturing partners</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary text-sm">
                Purchase Orders
            </a>
            <button type="button" @click="showCreateModal = true" class="btn btn-primary text-sm inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Supplier
            </button>
        </div>
    </div>

    <!-- Search -->
    <div class="card p-4">
        <form method="GET" action="{{ route('suppliers.index') }}" class="flex gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by supplier name, code, phone, or GSTIN..." class="form-control text-sm flex-1">
            <button type="submit" class="btn btn-primary text-sm">Search</button>
            <a href="{{ route('suppliers.index') }}" class="btn btn-secondary text-sm">Reset</a>
        </form>
    </div>

    <!-- Suppliers Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($suppliers as $supplier)
            <div class="card p-5 space-y-4 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="font-bold text-slate-800 text-base">
                            <a href="{{ route('suppliers.show', $supplier) }}" class="hover:text-emerald-700">
                                {{ $supplier->name }}
                            </a>
                        </h3>
                        <div class="text-xs text-slate-400 font-mono">{{ $supplier->supplier_code }}</div>
                    </div>
                    <span class="badge badge-success text-xs capitalize">{{ $supplier->status }}</span>
                </div>

                <div class="space-y-2 text-xs text-slate-600 border-t border-slate-100 pt-3">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Contact Person:</span>
                        <span class="font-medium text-slate-700">{{ $supplier->contact_person ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Mobile / WhatsApp:</span>
                        <span class="font-medium text-slate-800">{{ $supplier->phone }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">GSTIN:</span>
                        <span class="font-mono text-slate-700">{{ $supplier->gstin ?? 'Unregistered' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Payment Terms:</span>
                        <span class="font-medium text-slate-700">{{ $supplier->payment_terms ?? 'Net 30' }}</span>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-500 font-medium">{{ $supplier->purchase_orders_count }} POs issued</span>
                    <a href="{{ route('suppliers.show', $supplier) }}" class="text-emerald-700 font-semibold hover:underline">
                        View Supplier Profile →
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full card p-12 text-center text-slate-500">
                <p>No suppliers registered yet.</p>
            </div>
        @endforelse
    </div>

    <!-- Create Supplier Modal -->
    <div x-show="showCreateModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 flex items-center justify-center p-4" style="display: none;">
        <div class="card max-w-lg w-full p-6 space-y-4 shadow-2xl bg-white" @click.away="showCreateModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-800">Add New Supplier / Vendor</h3>
                <button type="button" @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            
            <form action="{{ route('suppliers.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Company / Supplier Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Himalayan Herbs & Extracts Ltd" class="form-control w-full text-sm">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Contact Person</label>
                        <input type="text" name="contact_person" placeholder="Sales Manager" class="form-control w-full text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Phone / Mobile *</label>
                        <input type="text" name="phone" required placeholder="+91 98765 11223" class="form-control w-full text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Email</label>
                        <input type="email" name="email" placeholder="vendor@supplier.com" class="form-control w-full text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">GSTIN Number</label>
                        <input type="text" name="gstin" placeholder="07AAAAA0000A1Z5" class="form-control w-full text-sm font-mono uppercase">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Address *</label>
                    <textarea name="address" rows="2" required placeholder="Factory / Farm premises address..." class="form-control w-full text-sm"></textarea>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">City</label>
                        <input type="text" name="city" placeholder="Haridwar" class="form-control w-full text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">State</label>
                        <input type="text" name="state" placeholder="Uttarakhand" class="form-control w-full text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Payment Terms</label>
                        <input type="text" name="payment_terms" placeholder="Net 30 Days" class="form-control w-full text-sm">
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="showCreateModal = false" class="btn btn-secondary text-xs">Cancel</button>
                    <button type="submit" class="btn btn-primary text-xs">Register Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
