@extends('layouts.app')

@section('title', 'Lead Pipeline Kanban')
@section('subtitle', 'Visual sales stages for MantraHeal incoming inquiries and prospects')

@section('content')
<div class="space-y-4">
    <!-- Header & Action Toolbar -->
    <div class="flex items-center justify-between">
        <div class="text-xs text-slate-500 font-medium">
            Drag & drop stages simulation • Total pipeline prospects
        </div>
        <div class="flex items-center space-x-2">
            <div class="bg-slate-200 p-0.5 rounded-lg flex items-center text-xs">
                <a href="{{ route('leads.index', ['view' => 'list']) }}" class="px-2.5 py-1.5 rounded-md font-medium text-slate-600 hover:text-slate-900">List</a>
                <span class="px-2.5 py-1.5 rounded-md font-medium bg-white text-slate-800 shadow-sm">Kanban</span>
            </div>
            <a href="{{ route('leads.create') }}" class="btn-primary text-xs py-1.5 px-3">+ New Lead</a>
        </div>
    </div>

    <!-- Horizontal Kanban Columns Container -->
    <div class="flex space-x-4 overflow-x-auto pb-6">
        @foreach($stages as $stage)
            @php
                $stageLeads = $kanbanLeads[$stage] ?? collect();
            @endphp
            <div class="w-72 flex-shrink-0 bg-slate-100 rounded-xl p-3 border border-slate-200 flex flex-col max-h-[calc(100vh-210px)]">
                <!-- Stage Header -->
                <div class="flex items-center justify-between mb-3 px-1">
                    <div class="flex items-center space-x-2">
                        <span class="font-bold text-xs text-slate-800 uppercase tracking-wider">{{ $stage }}</span>
                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-white text-slate-700 shadow-sm border border-slate-200">
                            {{ $stageLeads->count() }}
                        </span>
                    </div>
                    <span class="text-[11px] font-semibold text-teal-700">
                        ₹{{ number_format($stageLeads->sum('estimated_value'), 0) }}
                    </span>
                </div>

                <!-- Cards Container -->
                <div class="flex-1 overflow-y-auto space-y-3 pr-1">
                    @forelse($stageLeads as $lead)
                        <div class="card-elevated p-3 bg-white hover:border-teal-500 transition cursor-pointer">
                            <div class="flex items-start justify-between">
                                <a href="{{ route('leads.show', $lead->id) }}" class="font-bold text-xs text-slate-800 hover:text-teal-600">
                                    {{ $lead->name }}
                                </a>
                                <span class="text-[9px] font-mono text-slate-400">{{ $lead->lead_code }}</span>
                            </div>

                            <div class="text-[11px] text-slate-500 mt-1 flex items-center justify-between">
                                <span>{{ $lead->mobile }}</span>
                                <span class="text-teal-700 font-bold">₹{{ number_format($lead->estimated_value, 0) }}</span>
                            </div>

                            <div class="text-[10px] text-slate-400 mt-2 flex items-center justify-between border-t border-slate-50 pt-2">
                                <span class="truncate max-w-[120px]">{{ $lead->source }}</span>
                                <span class="text-slate-600 font-medium truncate max-w-[100px]">{{ $lead->assignedUser?->name ?? 'Unassigned' }}</span>
                            </div>

                            @if(!$lead->converted_to_customer_id)
                                <form method="POST" action="{{ route('leads.convert', $lead->id) }}" class="mt-2 text-right">
                                    @csrf
                                    <button type="submit" class="text-[10px] text-emerald-600 hover:underline font-semibold"
                                            onclick="return confirm('Convert lead to customer?')">
                                        Convert to Customer &rarr;
                                    </button>
                                </form>
                            @endif
                        </div>
                    @empty
                        <div class="h-24 border-2 border-dashed border-slate-200 rounded-lg flex items-center justify-center text-xs text-slate-400">
                            No leads
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
