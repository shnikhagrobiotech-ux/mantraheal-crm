@extends('layouts.app')

@section('title', 'Edit Customer')
@section('subtitle', 'Update customer profile #' . $customer->customer_code)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="card-elevated p-6">
        <form method="POST" action="{{ route('customers.update', $customer->id) }}" class="space-y-6">
            @csrf
            @method('PUT')
            <div>
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-3">Primary Information</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Full Name *</label>
                        <input type="text" name="name" value="{{ old('name', $customer->name) }}" required
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Mobile Phone *</label>
                        <input type="text" name="mobile" value="{{ old('mobile', $customer->mobile) }}" required
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">WhatsApp Number</label>
                        <input type="text" name="whatsapp" value="{{ old('whatsapp', $customer->whatsapp) }}"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mt-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Email Address</label>
                        <input type="email" name="email" value="{{ old('email', $customer->email) }}"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Source</label>
                        <select name="customer_source" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none">
                            @foreach(config('mantraheal.lead_sources', ['Website', 'Shopify', 'Meta Ads', 'WhatsApp', 'Phone']) as $src)
                                <option value="{{ $src }}" {{ $customer->customer_source === $src ? 'selected' : '' }}>{{ $src }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Type</label>
                        <select name="customer_type" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none">
                            <option value="Retail" {{ $customer->customer_type === 'Retail' ? 'selected' : '' }}>Retail</option>
                            <option value="VIP" {{ $customer->customer_type === 'VIP' ? 'selected' : '' }}>VIP</option>
                            <option value="Wholesale" {{ $customer->customer_type === 'Wholesale' ? 'selected' : '' }}>Wholesale</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Status</label>
                        <select name="status" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none">
                            <option value="active" {{ $customer->status === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ $customer->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Assigned Sales Executive</label>
                        <select name="assigned_user_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none">
                            <option value="">-- Unassigned --</option>
                            @foreach($salesExecutives as $exec)
                                <option value="{{ $exec->id }}" {{ $customer->assigned_user_id === $exec->id ? 'selected' : '' }}>
                                    {{ $exec->name }} ({{ $exec->designation ?? 'Sales' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Tags</label>
                        <input type="text" name="tags" value="{{ old('tags', $customer->tags) }}"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Notes</label>
                    <textarea name="notes" rows="3"
                              class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-teal-500 focus:outline-none">{{ old('notes', $customer->notes) }}</textarea>
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end space-x-3">
                <a href="{{ route('customers.show', $customer->id) }}" class="btn-secondary text-xs">Cancel</a>
                <button type="submit" class="btn-primary text-xs py-2 px-5">Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection
