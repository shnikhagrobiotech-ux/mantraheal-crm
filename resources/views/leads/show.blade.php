@extends('layouts.app')

@section('title', 'Lead - ' . $lead->name)
@section('subtitle', 'Pipeline stage tracking and qualification for lead #' . $lead->lead_code)

@section('content')
<div class="space-y-6">

    <!-- Top Summary Card -->
    <div class="card-elevated p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="flex items-start space-x-4">
                <div class="w-14 h-14 rounded-2xl bg-teal-600 text-white flex items-center justify-center font-bold text-xl shadow-md">
                    {{ substr($lead->name, 0, 2) }}
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <h2 class="text-xl font-bold text-slate-900">{{ $lead->name }}</h2>
                        <span class="px-2 py-0.5 text-xs font-semibold rounded bg-slate-100 text-slate-700">
                            {{ $lead->source }}
                        </span>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500 mt-1">
                        <span class="font-mono text-slate-600">{{ $lead->lead_code }}</span>
                        <span>•</span>
                        <span class="font-medium text-slate-800">{{ $lead->mobile }}</span>
                        @if($lead->email)
                            <span>•</span>
                            <span>{{ $lead->email }}</span>
                        @endif
                        @if($lead->city)
                            <span>•</span>
                            <span>{{ $lead->city }}, {{ $lead->state }}</span>
                        @endif
                    </div>
                    <div class="text-xs text-slate-500 mt-1">
                        Assigned To: <strong class="text-slate-700">{{ $lead->assignedUser?->name ?? 'Unassigned' }}</strong> |
                        Est Value: <strong class="text-teal-700">₹{{ number_format($lead->estimated_value, 2) }}</strong>
                    </div>
                </div>
            </div>

            <!-- Action buttons -->
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('calls.create', ['lead_id' => $lead->id]) }}" class="btn-primary text-xs py-2 px-3">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                    Call Lead
                </a>

                @if(!$lead->converted_to_customer_id)
                    <form method="POST" action="{{ route('leads.convert', $lead->id) }}">
                        @csrf
                        <button type="submit" class="btn-secondary text-xs py-2 px-3 bg-emerald-50 text-emerald-700 border-emerald-300 hover:bg-emerald-100"
                                onclick="return confirm('Convert this lead to a Customer 360 profile?')">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Convert to Customer
                        </button>
                    </form>
                @else
                    <a href="{{ route('customers.show', $lead->converted_to_customer_id) }}" class="btn-secondary text-xs py-2 px-3 text-emerald-700 font-bold">
                        View Customer Profile &rarr;
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Pipeline Stage & Notes Update Form -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Stage Transition Box -->
        <div class="card-elevated p-5 lg:col-span-1">
            <h3 class="text-sm font-bold text-slate-800 mb-3">Update Pipeline Stage</h3>
            <form method="POST" action="{{ route('leads.update', $lead->id) }}" class="space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="name" value="{{ $lead->name }}">
                <input type="hidden" name="mobile" value="{{ $lead->mobile }}">
                <input type="hidden" name="source" value="{{ $lead->source }}">

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Current Stage</label>
                    <select name="stage" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white font-medium"
                            onchange="document.getElementById('lostReasonDiv').style.display = this.value === 'Lost' ? 'block' : 'none';">
                        @foreach($stages as $st)
                            <option value="{{ $st }}" {{ $lead->stage === $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="lostReasonDiv" style="display: {{ $lead->stage === 'Lost' ? 'block' : 'none' }};">
                    <label class="block text-xs font-semibold text-rose-600 mb-1">Lost Reason *</label>
                    <select name="lost_reason" class="w-full px-3 py-2 border border-rose-300 rounded-lg text-xs bg-white">
                        <option value="">-- Select Reason --</option>
                        @foreach($lostReasons as $reason)
                            <option value="{{ $reason }}" {{ $lead->lost_reason === $reason ? 'selected' : '' }}>{{ $reason }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Assigned Executive</label>
                    <select name="assigned_user_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white">
                        <option value="">-- Unassigned --</option>
                        @foreach($salesExecutives as $exec)
                            <option value="{{ $exec->id }}" {{ $lead->assigned_user_id === $exec->id ? 'selected' : '' }}>
                                {{ $exec->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Next Follow-up Date</label>
                    <input type="datetime-local" name="next_followup_at"
                           value="{{ $lead->next_followup_at ? $lead->next_followup_at->format('Y-m-d\TH:i') : '' }}"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs">
                </div>

                <button type="submit" class="btn-primary text-xs py-2 w-full justify-center">Save Stage Updates</button>
            </form>
        </div>

        <!-- Activity History & Quick Note -->
        <div class="card-elevated p-5 lg:col-span-2 space-y-4">
            <div>
                <h3 class="text-sm font-bold text-slate-800 mb-2">Add Timeline Note</h3>
                <form method="POST" action="{{ route('leads.note', $lead->id) }}" class="flex space-x-2">
                    @csrf
                    <input type="text" name="note" required placeholder="Write internal sales note, customer inquiry details..."
                           class="flex-1 px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    <button type="submit" class="btn-primary text-xs py-2 px-4 flex-shrink-0">Add Note</button>
                </form>
            </div>

            <div class="border-t border-slate-100 pt-4">
                <h4 class="text-xs font-bold text-slate-600 uppercase tracking-wider mb-3">Activity & Stage History</h4>
                <div class="space-y-3 max-h-96 overflow-y-auto pr-1">
                    @forelse($lead->activities as $act)
                        <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg text-xs">
                            <div class="flex items-center justify-between text-slate-500 mb-1">
                                <span class="font-bold text-slate-700 capitalize">{{ $act->type }}</span>
                                <span class="font-mono text-[10px]">{{ $act->created_at->format('d M Y, h:i A') }}</span>
                            </div>
                            <div class="text-slate-800">{{ $act->description }}</div>
                            <div class="text-[10px] text-slate-400 mt-1">Logged by: {{ $act->user?->name ?? 'System' }}</div>
                        </div>
                    @empty
                        <div class="text-slate-400 text-xs text-center py-4">No activities logged yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
