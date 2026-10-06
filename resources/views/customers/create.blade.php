@extends('layouts.app')

@section('title', 'Create Customer')
@section('subtitle', 'Add new customer to MantraHeal system')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="card-elevated p-6">
        <form method="POST" action="{{ route('customers.store') }}" class="space-y-6">
            @csrf
            <div>
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-3">Primary Information</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Full Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Mobile Phone *</label>
                        <input type="text" name="mobile" value="{{ old('mobile') }}" required placeholder="10-digit mobile"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">WhatsApp Number</label>
                        <input type="text" name="whatsapp" value="{{ old('whatsapp') }}" placeholder="If different from mobile"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Email Address</label>
                        <input type="email" name="email" value="{{ old('email') }}"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Customer Source</label>
                        <select name="customer_source" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none">
                            @foreach(config('mantraheal.lead_sources', ['Website', 'Shopify', 'Meta Ads', 'WhatsApp', 'Phone']) as $src)
                                <option value="{{ $src }}">{{ $src }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Customer Type</label>
                        <select name="customer_type" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none">
                            <option value="Retail">Retail</option>
                            <option value="VIP">VIP</option>
                            <option value="Wholesale">Wholesale</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Shipping Address -->
            <div class="border-t border-slate-100 pt-5">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-3">Shipping Address</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Street Address</label>
                        <input type="text" name="address_line1" value="{{ old('address_line1') }}" placeholder="House/Flat number, Street name"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">City</label>
                        <input type="text" name="city" value="{{ old('city', 'New Delhi') }}"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">State</label>
                        <input type="text" name="state" value="{{ old('state', 'Delhi') }}"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Pincode</label>
                        <input type="text" name="pincode" value="{{ old('pincode', '110001') }}"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Assigned Sales Executive</label>
                        <select name="assigned_user_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none">
                            <option value="">-- Unassigned --</option>
                            @foreach($salesExecutives as $exec)
                                <option value="{{ $exec->id }}">{{ $exec->name }} ({{ $exec->designation ?? 'Sales' }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Notes & Tags -->
            <div class="border-t border-slate-100 pt-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Customer Tags</label>
                        <input type="text" name="tags" value="{{ old('tags') }}" placeholder="e.g. Ayurveda Enthusiast, High Spender"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Internal Notes</label>
                        <textarea name="notes" rows="2" placeholder="Specific customer health preferences, delivery instructions..."
                                  class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-teal-500 focus:outline-none">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end space-x-3">
                <a href="{{ route('customers.index') }}" class="btn-secondary text-xs">Cancel</a>
                <button type="submit" class="btn-primary text-xs py-2 px-5">Save Customer Profile</button>
            </div>
        </form>
    </div>
</div>
@endsection
