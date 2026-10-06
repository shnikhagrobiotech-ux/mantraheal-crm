@extends('layouts.app')

@section('title', 'Follow-up Scheduler')
@section('subtitle', 'Track scheduled customer check-ins, repeat reminders, and overdue alerts')

@section('content')
<div class="space-y-4">
    <!-- Header Tabs & Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200 pb-2">
        <div class="flex flex-wrap items-center gap-2 text-xs font-semibold">
            <a href="{{ route('followups.index', ['tab' => 'today']) }}"
               class="px-3 py-2 rounded-lg transition {{ $tab === 'today' ? 'bg-teal-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                Today ({{ $counts['today'] }})
            </a>
            <a href="{{ route('followups.index', ['tab' => 'overdue']) }}"
               class="px-3 py-2 rounded-lg transition {{ $tab === 'overdue' ? 'bg-rose-600 text-white shadow-sm' : 'text-rose-600 hover:bg-rose-50' }}">
                Overdue ({{ $counts['overdue'] }})
            </a>
            <a href="{{ route('followups.index', ['tab' => 'tomorrow']) }}"
               class="px-3 py-2 rounded-lg transition {{ $tab === 'tomorrow' ? 'bg-teal-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                Tomorrow ({{ $counts['tomorrow'] }})
            </a>
            <a href="{{ route('followups.index', ['tab' => 'upcoming']) }}"
               class="px-3 py-2 rounded-lg transition {{ $tab === 'upcoming' ? 'bg-teal-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                Upcoming ({{ $counts['upcoming'] }})
            </a>
            <a href="{{ route('followups.index', ['tab' => 'completed']) }}"
               class="px-3 py-2 rounded-lg transition {{ $tab === 'completed' ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                Completed ({{ $counts['completed'] }})
            </a>
        </div>
    </div>

    <!-- Follow-ups Table -->
    <div class="card-elevated overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4 font-semibold">Contact / Prospect</th>
                        <th class="py-3 px-4 font-semibold">Due Date & Time</th>
                        <th class="py-3 px-4 font-semibold">Reason</th>
                        <th class="py-3 px-4 font-semibold">Priority</th>
                        <th class="py-3 px-4 font-semibold">Assigned Executive</th>
                        <th class="py-3 px-4 font-semibold">Status</th>
                        <th class="py-3 px-4 font-semibold text-right">Quick Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($followups as $f)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4">
                                @if($f->customer)
                                    <a href="{{ route('customers.show', $f->customer_id) }}" class="font-bold text-teal-700 hover:underline">
                                        {{ $f->customer->name }}
                                    </a>
                                    <div class="text-[10px] text-slate-400">{{ $f->customer->mobile }} (Customer)</div>
                                @elseif($f->lead)
                                    <a href="{{ route('leads.show', $f->lead_id) }}" class="font-bold text-blue-700 hover:underline">
                                        {{ $f->lead->name }}
                                    </a>
                                    <div class="text-[10px] text-slate-400">{{ $f->lead->mobile }} (Lead)</div>
                                @else
                                    <span class="text-slate-400">General Reminder</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 font-mono font-medium {{ $f->due_date->isPast() && $f->status === 'Pending' ? 'text-rose-600 font-bold' : 'text-slate-800' }}">
                                {{ $f->due_date->format('d M Y') }}
                                @if($f->due_time)
                                    <span class="text-slate-400 text-[10px] block">{{ $f->due_time }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-medium text-slate-800">{{ $f->reason }}</div>
                                @if($f->notes)
                                    <div class="text-[10px] text-slate-400 truncate max-w-xs">{{ $f->notes }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 text-[10px] rounded font-semibold {{ $f->priority === 'High' ? 'bg-rose-50 text-rose-700 border border-rose-200' : ($f->priority === 'Medium' ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-600') }}">
                                    {{ $f->priority }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-600">{{ $f->user?->name ?? 'Staff' }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 text-[10px] rounded-full font-semibold {{ $f->status === 'Completed' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ $f->status }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right space-x-2">
                                @if($f->status === 'Pending')
                                    <form method="POST" action="{{ route('followups.complete', $f->id) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-xs text-emerald-600 hover:underline font-semibold">
                                            ✓ Complete
                                        </button>
                                    </form>
                                    @if($f->customer_id)
                                        <a href="{{ route('calls.create', ['customer_id' => $f->customer_id]) }}" class="text-xs text-teal-600 hover:underline font-semibold">
                                            Call
                                        </a>
                                    @elseif($f->lead_id)
                                        <a href="{{ route('calls.create', ['lead_id' => $f->lead_id]) }}" class="text-xs text-teal-600 hover:underline font-semibold">
                                            Call
                                        </a>
                                    @endif
                                @else
                                    <span class="text-slate-400 font-mono text-[11px]">
                                        {{ $f->completed_at ? $f->completed_at->format('d M') : 'Done' }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">No follow-ups found in this view.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($followups->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $followups->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
