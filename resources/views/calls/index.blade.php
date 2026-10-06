@extends('layouts.app')

@section('title', 'Sales Calls')
@section('subtitle', 'Log of incoming and outgoing calls with outcomes and audio recordings')

@section('content')
<div class="space-y-4">
    <!-- Header & Action Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <form method="GET" action="{{ route('calls.index') }}" class="flex flex-wrap items-center gap-2 flex-1">
            <select name="direction" class="px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white">
                <option value="">All Directions</option>
                <option value="outgoing" {{ request('direction') == 'outgoing' ? 'selected' : '' }}>Outgoing</option>
                <option value="incoming" {{ request('direction') == 'incoming' ? 'selected' : '' }}>Incoming</option>
            </select>

            <select name="outcome" class="px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white">
                <option value="">All Outcomes</option>
                @foreach($outcomes as $out)
                    <option value="{{ $out }}" {{ request('outcome') == $out ? 'selected' : '' }}>{{ $out }}</option>
                @endforeach
            </select>

            <select name="user_id" class="px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white">
                <option value="">All Executives</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ request('user_id') == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                @endforeach
            </select>

            <input type="date" name="date" value="{{ request('date') }}" class="px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white">

            <button type="submit" class="btn-secondary text-xs py-2 px-3">Filter</button>
            @if(request()->anyFilled(['direction', 'outcome', 'user_id', 'date']))
                <a href="{{ route('calls.index') }}" class="text-xs text-slate-500 hover:text-slate-700 py-2">Clear</a>
            @endif
        </form>

        <div class="flex items-center space-x-2">
            <a href="{{ route('call-recordings.index') }}" class="btn-secondary text-xs py-2 px-3">
                <svg class="w-4 h-4 mr-1 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z"></path></svg>
                Call Recordings Player
            </a>
            <a href="{{ route('calls.create') }}" class="btn-primary text-xs py-2 px-3">+ Log Call</a>
        </div>
    </div>

    <!-- Calls Table -->
    <div class="card-elevated overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4 font-semibold">Contact / Customer</th>
                        <th class="py-3 px-4 font-semibold">Direction</th>
                        <th class="py-3 px-4 font-semibold">Executive</th>
                        <th class="py-3 px-4 font-semibold">Call Date & Time</th>
                        <th class="py-3 px-4 font-semibold">Duration</th>
                        <th class="py-3 px-4 font-semibold">Outcome</th>
                        <th class="py-3 px-4 font-semibold">Recording</th>
                        <th class="py-3 px-4 font-semibold">Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($calls as $call)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4">
                                @if($call->customer)
                                    <a href="{{ route('customers.show', $call->customer_id) }}" class="font-bold text-teal-700 hover:underline">
                                        {{ $call->customer->name }}
                                    </a>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $call->customer->mobile }}</div>
                                @elseif($call->lead)
                                    <a href="{{ route('leads.show', $call->lead_id) }}" class="font-bold text-blue-700 hover:underline">
                                        {{ $call->lead->name }}
                                    </a>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $call->lead->mobile }} (Lead)</div>
                                @else
                                    <span class="text-slate-400">Direct Call</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 text-[10px] font-semibold rounded {{ $call->direction === 'incoming' ? 'bg-blue-50 text-blue-700' : 'bg-teal-50 text-teal-700' }}">
                                    {{ ucfirst($call->direction) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-600">{{ $call->user?->name ?? 'Staff' }}</td>
                            <td class="py-3 px-4 font-mono text-slate-500">{{ $call->call_datetime->format('d M Y, H:i') }}</td>
                            <td class="py-3 px-4 font-mono font-medium text-slate-800">{{ $call->duration_formatted }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 text-[10px] font-medium rounded bg-slate-100 text-slate-800 border border-slate-200">
                                    {{ $call->outcome }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                @if($call->recording)
                                    <div class="flex items-center gap-1.5">
                                        <audio controls class="h-8 w-44 rounded" preload="none">
                                            <source src="{{ route('call-recordings.play', $call->recording->id) }}" type="{{ $call->recording->mime_type ?: 'audio/wav' }}">
                                        </audio>
                                        <a href="{{ route('call-recordings.play', $call->recording->id) }}" target="_blank" download title="Download Audio" class="text-slate-400 hover:text-teal-700 p-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        </a>
                                    </div>
                                @else
                                    <span class="text-slate-400 text-[11px]">No audio</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-slate-500 max-w-xs truncate" title="{{ $call->notes }}">
                                {{ $call->notes ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">No calls logged matching the criteria.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($calls->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $calls->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
