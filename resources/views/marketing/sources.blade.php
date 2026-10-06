@extends('layouts.app')

@section('title', 'Marketing Attribution & Campaigns — MantraHeal CRM')

@section('content')
<div class="space-y-6" x-data="{ showSourceModal: false, showCampaignModal: false }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xs text-slate-500">Growth & Acquisition</span>
                <span class="text-slate-300">/</span>
                <span class="text-xs text-slate-500">Attribution</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Marketing Attribution & ROI</h1>
            <p class="text-sm text-slate-500">Track omnichannel acquisition performance across Meta Ads, Google Ads, Organic, and Offline</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" @click="showSourceModal = true" class="btn btn-secondary text-sm inline-flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Source
            </button>
            <button type="button" @click="showCampaignModal = true" class="btn btn-primary text-sm inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                Create Campaign
            </button>
        </div>
    </div>

    <!-- Attribution Performance Table -->
    <div class="card overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold text-slate-800 text-sm">Acquisition Source Conversion Funnel</h2>
            <span class="text-xs text-slate-500">Lead → Order Conversion Metrics</span>
        </div>
        <div class="overflow-x-auto">
            <table class="table w-full text-sm">
                <thead>
                    <tr>
                        <th>Marketing Source</th>
                        <th>Channel Type</th>
                        <th class="text-center">Leads Generated</th>
                        <th class="text-center">Direct Orders</th>
                        <th class="text-right">Generated Revenue (₹)</th>
                        <th class="text-center">Lead Conversion %</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($attributionData as $data)
                        <tr class="hover:bg-slate-50/75 transition-colors">
                            <td class="font-semibold text-slate-800">
                                {{ $data['source']->name }}
                            </td>
                            <td>
                                <span class="badge bg-slate-100 text-slate-700 text-xs capitalize">
                                    {{ $data['source']->channel_type }}
                                </span>
                            </td>
                            <td class="text-center font-medium text-slate-700">
                                {{ $data['leads'] }}
                            </td>
                            <td class="text-center font-medium text-slate-700">
                                {{ $data['orders'] }}
                            </td>
                            <td class="text-right font-bold text-slate-800">
                                ₹{{ number_format($data['revenue'], 2) }}
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $data['conversion_rate'] >= 20 ? 'badge-success' : ($data['conversion_rate'] > 0 ? 'badge-warning' : 'bg-slate-100 text-slate-500') }} text-xs font-bold">
                                    {{ $data['conversion_rate'] }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-slate-400 text-xs">
                                No marketing source metrics recorded.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Active Marketing Campaigns -->
    <div class="card overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold text-slate-800 text-sm">Ad Campaigns & Budgets</h2>
            <span class="text-xs text-slate-500">{{ $campaigns->count() }} active initiatives</span>
        </div>
        <div class="overflow-x-auto">
            <table class="table w-full text-sm">
                <thead>
                    <tr>
                        <th>Campaign Name</th>
                        <th>Source Channel</th>
                        <th>Code</th>
                        <th class="text-right">Allocated Budget (₹)</th>
                        <th>Duration Window</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($campaigns as $camp)
                        <tr class="hover:bg-slate-50/75 transition-colors">
                            <td class="font-semibold text-slate-800">
                                {{ $camp->name }}
                            </td>
                            <td class="text-xs text-slate-600">
                                {{ $camp->source?->name }}
                            </td>
                            <td class="font-mono text-xs text-slate-500">
                                {{ $camp->code }}
                            </td>
                            <td class="text-right font-semibold text-slate-800">
                                ₹{{ number_format($camp->budget, 2) }}
                            </td>
                            <td class="text-xs text-slate-500">
                                {{ $camp->start_date ? $camp->start_date->format('d M') : 'Ongoing' }}
                                @if($camp->end_date)
                                    - {{ $camp->end_date->format('d M Y') }}
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-success text-[10px]">Active</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-slate-400 text-xs">
                                No marketing campaigns configured yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Source Modal -->
    <div x-show="showSourceModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 flex items-center justify-center p-4" style="display: none;">
        <div class="card max-w-md w-full p-6 space-y-4 shadow-2xl bg-white" @click.away="showSourceModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-800">Add Lead Source</h3>
                <button type="button" @click="showSourceModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <form action="{{ route('marketing.sources.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Source Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Meta Ads, Google Search, Instagram DM" class="form-control w-full text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Channel Type *</label>
                    <select name="channel_type" required class="form-control w-full text-sm">
                        <option value="Paid Digital">Paid Digital (Meta, Google)</option>
                        <option value="Organic Social">Organic Social</option>
                        <option value="Direct Commerce">Direct Website / Shopify</option>
                        <option value="Messaging">WhatsApp / SMS</option>
                        <option value="Offline">Offline / Clinic / Retail</option>
                    </select>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showSourceModal = false" class="btn btn-secondary text-xs">Cancel</button>
                    <button type="submit" class="btn btn-primary text-xs">Save Source</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Create Campaign Modal -->
    <div x-show="showCampaignModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 flex items-center justify-center p-4" style="display: none;">
        <div class="card max-w-md w-full p-6 space-y-4 shadow-2xl bg-white" @click.away="showCampaignModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-800">Create Marketing Campaign</h3>
                <button type="button" @click="showCampaignModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <form action="{{ route('marketing.campaigns.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Campaign Title *</label>
                    <input type="text" name="name" required placeholder="e.g. Diwali Immunity Booster Sale" class="form-control w-full text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Marketing Source *</label>
                    <select name="marketing_source_id" required class="form-control w-full text-sm">
                        @foreach($sources as $src)
                            <option value="{{ $src->id }}">{{ $src->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Budget Allocation (₹) *</label>
                    <input type="number" step="0.01" name="budget" required value="25000" class="form-control w-full text-sm">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Start Date</label>
                        <input type="date" name="start_date" value="{{ date('Y-m-d') }}" class="form-control w-full text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">End Date</label>
                        <input type="date" name="end_date" value="{{ date('Y-m-d', strtotime('+30 days')) }}" class="form-control w-full text-sm">
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showCampaignModal = false" class="btn btn-secondary text-xs">Cancel</button>
                    <button type="submit" class="btn btn-primary text-xs">Launch Campaign</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
