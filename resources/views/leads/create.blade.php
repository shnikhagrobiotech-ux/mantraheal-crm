@extends('layouts.app')

@section('title', 'Create Lead')
@section('subtitle', 'Log a new inquiry or marketing lead')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="card-elevated p-6">
        <form method="POST" action="{{ route('leads.store') }}" class="space-y-6">
            @csrf
            <div>
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-3">Lead Contact Information</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Prospect Full Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Mobile Number *</label>
                        <input type="text" name="mobile" value="{{ old('mobile') }}" required placeholder="10-digit mobile"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">WhatsApp Number</label>
                        <input type="text" name="whatsapp" value="{{ old('whatsapp') }}"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">City</label>
                        <input type="text" name="city" value="{{ old('city') }}" placeholder="e.g. Mumbai, Delhi, Bengaluru"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">State</label>
                        <input type="text" name="state" value="{{ old('state') }}" placeholder="e.g. Maharashtra, Delhi"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    </div>
                </div>
            </div>

            <!-- Pipeline Parameters -->
            <div class="border-t border-slate-100 pt-5">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-3">Pipeline & Attribution</h3>
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Lead Source *</label>
                        <select name="source" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none">
                            @foreach(config('mantraheal.lead_sources', ['Website', 'Meta Ads', 'Shopify', 'WhatsApp', 'Phone']) as $src)
                                <option value="{{ $src }}">{{ $src }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Initial Stage</label>
                        <select name="stage" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none">
                            @foreach(config('mantraheal.lead_stages', ['New', 'Contacted', 'Interested', 'Follow-up']) as $st)
                                <option value="{{ $st }}">{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Estimated Value (₹)</label>
                        <input type="number" step="0.01" name="estimated_value" value="{{ old('estimated_value', 1499) }}"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Assign Executive</label>
                        <select name="assigned_user_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none">
                            <option value="">-- Unassigned --</option>
                            @foreach($salesExecutives as $exec)
                                <option value="{{ $exec->id }}" {{ auth()->id() === $exec->id ? 'selected' : '' }}>
                                    {{ $exec->name }} ({{ $exec->designation ?? 'Sales' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Next Follow-up Date/Time</label>
                        <input type="datetime-local" name="next_followup_at" value="{{ old('next_followup_at') }}"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Inquiry Notes / Health Concern</label>
                        <textarea name="notes" rows="2" placeholder="e.g. Inquiring about Ayurvedic immunity booster or joint care pack..."
                                  class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-teal-500 focus:outline-none">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end space-x-3">
                <a href="{{ route('leads.index') }}" class="btn-secondary text-xs">Cancel</a>
                <button type="submit" class="btn-primary text-xs py-2 px-5">Save Lead to Pipeline</button>
            </div>
        </form>
    </div>
</div>
@endsection
