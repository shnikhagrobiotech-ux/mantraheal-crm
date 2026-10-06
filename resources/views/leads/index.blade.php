@extends('layouts.app')

@section('title', 'Lead Management')
@section('subtitle', 'Track inquiries, stages, conversion and sales executive assignments')

@section('content')
<div class="space-y-4">
    <!-- Header & Action Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <!-- Search & Filter Form -->
        <form method="GET" action="{{ route('leads.index') }}" class="flex flex-wrap items-center gap-2 flex-1">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search leads by name, mobile, city..."
                   class="px-3 py-2 border border-slate-300 rounded-lg text-xs w-full sm:w-64 focus:ring-2 focus:ring-teal-500 focus:outline-none">

            <select name="stage" class="px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none">
                <option value="">All Stages</option>
                @foreach($stages as $st)
                    <option value="{{ $st }}" {{ request('stage') == $st ? 'selected' : '' }}>{{ $st }}</option>
                @endforeach
            </select>

            <select name="source" class="px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none">
                <option value="">All Sources</option>
                @foreach(config('mantraheal.lead_sources', ['Website', 'Meta Ads', 'Shopify', 'WhatsApp']) as $src)
                    <option value="{{ $src }}" {{ request('source') == $src ? 'selected' : '' }}>{{ $src }}</option>
                @endforeach
            </select>

            <button type="submit" class="btn-secondary text-xs py-2 px-3">Filter</button>
            @if(request()->anyFilled(['search', 'stage', 'source']))
                <a href="{{ route('leads.index') }}" class="text-xs text-slate-500 hover:text-slate-700 py-2">Clear</a>
            @endif
        </form>

        <div class="flex items-center space-x-2">
            <!-- View Mode Switcher -->
            <div class="bg-slate-200 p-0.5 rounded-lg flex items-center text-xs">
                <a href="{{ route('leads.index', array_merge(request()->query(), ['view' => 'list'])) }}"
                   class="px-2.5 py-1.5 rounded-md font-medium {{ request('view', 'list') === 'list' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    List
                </a>
                <a href="{{ route('leads.index', array_merge(request()->query(), ['view' => 'kanban'])) }}"
                   class="px-2.5 py-1.5 rounded-md font-medium {{ request('view') === 'kanban' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    Kanban
                </a>
            </div>

            <a href="{{ route('leads.import') }}" class="btn-secondary text-xs py-2 px-3 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Import CSV
            </a>
            <a href="{{ route('meta.sync.index') }}" class="btn bg-blue-600 hover:bg-blue-700 text-white text-xs py-2 px-3 rounded-lg flex items-center gap-1.5 font-bold shadow-sm transition">
                <span class="w-2 h-2 rounded-full bg-blue-300 animate-pulse"></span>
                Meta Ads Sync
            </a>
            <a href="{{ route('leads.export') }}" class="btn-secondary text-xs py-2 px-3">Export CSV</a>
            <a href="{{ route('leads.create') }}" class="btn-primary text-xs py-2 px-3">+ New Lead</a>
        </div>
    </div>

    <!-- Leads Table -->
    <div class="card-elevated overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4 font-semibold">Lead Info</th>
                        <th class="py-3 px-4 font-semibold">Contact Details</th>
                        <th class="py-3 px-4 font-semibold">Source</th>
                        <th class="py-3 px-4 font-semibold">Stage</th>
                        <th class="py-3 px-4 font-semibold">Est Value</th>
                        <th class="py-3 px-4 font-semibold">Assigned Executive</th>
                        <th class="py-3 px-4 font-semibold">Next Follow-up</th>
                        <th class="py-3 px-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($leads as $lead)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4">
                                <a href="{{ route('leads.show', $lead->id) }}" class="font-bold text-teal-700 hover:underline">
                                    {{ $lead->name }}
                                </a>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $lead->lead_code }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <button type="button" @click="$dispatch('open-dialer', { phone: '{{ $lead->mobile }}', name: '{{ addslashes($lead->name) }}', id: {{ $lead->id }}, type: 'lead', city: '{{ addslashes($lead->city ?? '') }}', badge: 'Lead ({{ $lead->stage }})' })" class="font-medium text-slate-800 hover:text-teal-700 flex items-center gap-1 cursor-pointer text-left" title="Click to Call Lead">
                                    <svg class="w-3.5 h-3.5 text-teal-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    <span>{{ $lead->mobile }}</span>
                                </button>
                                <div class="text-[10px] text-slate-400">{{ $lead->city ?? 'City N/A' }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 text-[10px] rounded bg-slate-100 text-slate-700 border border-slate-200 font-medium">
                                    {{ $lead->source }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                @php
                                    $stageColor = match($lead->stage) {
                                        'New' => 'bg-sky-50 text-sky-700 border-sky-200',
                                        'Contacted' => 'bg-blue-50 text-blue-700 border-blue-200',
                                        'Interested' => 'bg-teal-50 text-teal-700 border-teal-200',
                                        'Follow-up' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'Order Confirmed' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                        'Won' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'Lost' => 'bg-rose-50 text-rose-700 border-rose-200',
                                        default => 'bg-slate-100 text-slate-700 border-slate-200',
                                    };
                                @endphp
                                <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full border {{ $stageColor }}">
                                    {{ $lead->stage }}
                                </span>
                                @if($lead->lost_reason)
                                    <div class="text-[9px] text-rose-500 font-medium mt-0.5">Reason: {{ $lead->lost_reason }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-4 font-semibold text-slate-800">
                                ₹{{ number_format($lead->estimated_value, 2) }}
                            </td>
                            <td class="py-3 px-4 text-slate-600">
                                {{ $lead->assignedUser?->name ?? 'Unassigned' }}
                            </td>
                            <td class="py-3 px-4 text-slate-500">
                                {{ $lead->next_followup_at ? $lead->next_followup_at->format('d M Y') : 'None' }}
                            </td>
                            <td class="py-3 px-4 text-right space-x-2">
                                <a href="{{ route('leads.show', $lead->id) }}" class="text-teal-600 hover:underline font-medium">Manage</a>
                                @if(!$lead->converted_to_customer_id)
                                    <form method="POST" action="{{ route('leads.convert', $lead->id) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-emerald-600 hover:underline font-semibold"
                                                onclick="return confirm('Convert this lead to a customer profile?')">
                                            Convert &rarr;
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('customers.show', $lead->converted_to_customer_id) }}" class="text-emerald-700 font-semibold">
                                        View Customer &rarr;
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">No leads found in pipeline.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($leads->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $leads->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
