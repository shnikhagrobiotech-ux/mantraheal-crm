@extends('layouts.app')

@section('title', 'Customers')
@section('subtitle', 'Complete directory of MantraHeal customers with 360 profiles')

@section('content')
<div class="space-y-4">
    <!-- Header & Action Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <!-- Search & Filter Form -->
        <form method="GET" action="{{ route('customers.index') }}" class="flex flex-wrap items-center gap-2 flex-1">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, mobile, code, email..."
                   class="px-3 py-2 border border-slate-300 rounded-lg text-xs w-full sm:w-64 focus:ring-2 focus:ring-teal-500 focus:outline-none">

            <select name="type" class="px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none">
                <option value="">All Types</option>
                <option value="Retail" {{ request('type') == 'Retail' ? 'selected' : '' }}>Retail</option>
                <option value="Wholesale" {{ request('type') == 'Wholesale' ? 'selected' : '' }}>Wholesale</option>
                <option value="VIP" {{ request('type') == 'VIP' ? 'selected' : '' }}>VIP</option>
            </select>

            <select name="status" class="px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none">
                <option value="">All Statuses</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>

            <button type="submit" class="btn-secondary text-xs py-2 px-3">Filter</button>
            @if(request()->anyFilled(['search', 'type', 'status', 'assigned_user_id']))
                <a href="{{ route('customers.index') }}" class="text-xs text-slate-500 hover:text-slate-700 py-2">Clear</a>
            @endif
        </form>

        <div class="flex items-center space-x-2">
            <a href="{{ route('customers.export') }}" class="btn-secondary text-xs py-2 px-3">
                <svg class="w-4 h-4 mr-1 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Export CSV
            </a>
            <a href="{{ route('customers.create') }}" class="btn-primary text-xs py-2 px-3">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                New Customer
            </a>
        </div>
    </div>

    <!-- Customers Table -->
    <div class="card-elevated overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4 font-semibold">Customer</th>
                        <th class="py-3 px-4 font-semibold">Contact Details</th>
                        <th class="py-3 px-4 font-semibold">City & State</th>
                        <th class="py-3 px-4 font-semibold">Total Orders</th>
                        <th class="py-3 px-4 font-semibold">Spend & AOV</th>
                        <th class="py-3 px-4 font-semibold">Source</th>
                        <th class="py-3 px-4 font-semibold">Assigned Rep</th>
                        <th class="py-3 px-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($customers as $customer)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4">
                                <a href="{{ route('customers.show', $customer->id) }}" class="font-bold text-teal-700 hover:underline">
                                    {{ $customer->name }}
                                </a>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $customer->customer_code }}</div>
                                <span class="inline-block mt-0.5 px-1.5 py-0.5 text-[9px] font-semibold rounded bg-slate-100 text-slate-600">
                                    {{ $customer->customer_type }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <button type="button" @click="$dispatch('open-dialer', { phone: '{{ $customer->mobile }}', name: '{{ addslashes($customer->name) }}', id: {{ $customer->id }}, type: 'customer', city: '{{ addslashes($customer->defaultAddress?->city ?? $customer->city ?? '') }}', badge: 'Customer' })" class="font-medium text-slate-800 hover:text-teal-700 flex items-center gap-1 cursor-pointer text-left" title="Click to Call Customer">
                                    <svg class="w-3.5 h-3.5 text-teal-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                    <span>{{ $customer->mobile }}</span>
                                </button>
                                @if($customer->email)
                                    <div class="text-[11px] text-slate-400 truncate max-w-[140px]">{{ $customer->email }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-slate-600">
                                {{ $customer->defaultAddress?->city ?? 'N/A' }}, {{ $customer->defaultAddress?->state ?? '' }}
                            </td>
                            <td class="py-3 px-4 font-semibold">
                                <span class="{{ $customer->total_orders > 1 ? 'text-emerald-700 font-bold' : 'text-slate-800' }}">
                                    {{ $customer->total_orders }}
                                </span>
                                @if($customer->total_orders > 1)
                                    <span class="text-[9px] block text-emerald-600 font-medium">Repeat Buyer</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-800">₹{{ number_format($customer->total_spend, 2) }}</div>
                                <div class="text-[10px] text-slate-400">AOV: ₹{{ number_format($customer->average_order_value, 2) }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 text-[10px] rounded bg-teal-50 text-teal-700 border border-teal-100 font-medium">
                                    {{ $customer->customer_source }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-600">
                                {{ $customer->assignedUser?->name ?? 'Unassigned' }}
                            </td>
                            <td class="py-3 px-4 text-right space-x-2">
                                <a href="{{ route('customers.show', $customer->id) }}" class="text-teal-600 hover:text-teal-800 font-medium">Customer 360 &rarr;</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">No customer profiles found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($customers->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $customers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
