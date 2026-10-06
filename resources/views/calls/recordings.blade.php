@extends('layouts.app')

@section('title', 'Call Recordings Player')
@section('subtitle', 'Protected audio recordings archive with direct HTML5 audio playback')

@section('content')
<div class="space-y-4">
    <!-- Notice Banner -->
    <div class="p-4 bg-slate-900 text-slate-200 rounded-xl border border-slate-800 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <span class="p-2 rounded-lg bg-teal-500/20 text-teal-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z"></path></svg>
            </span>
            <div>
                <h3 class="text-sm font-bold text-white">Local Audio Storage & Protected Playback</h3>
                <p class="text-xs text-slate-400">All call recordings are stored securely under <code class="text-teal-400">/storage/app/call-recordings/</code> and streamed with role authorization.</p>
            </div>
        </div>
        <a href="{{ route('calls.create') }}" class="btn-primary text-xs py-1.5 px-3 whitespace-nowrap">+ Upload Call</a>
    </div>

    <!-- Recordings Table with Audio Players -->
    <div class="card-elevated overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4 font-semibold">Contact</th>
                        <th class="py-3 px-4 font-semibold">Executive</th>
                        <th class="py-3 px-4 font-semibold">Call Date</th>
                        <th class="py-3 px-4 font-semibold">Direction & Outcome</th>
                        <th class="py-3 px-4 font-semibold">Duration</th>
                        <th class="py-3 px-4 font-semibold">▶ Play Recording</th>
                        <th class="py-3 px-4 font-semibold">File Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($recordings as $rec)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4">
                                @if($rec->customer)
                                    <a href="{{ route('customers.show', $rec->customer_id) }}" class="font-bold text-teal-700 hover:underline">
                                        {{ $rec->customer->name }}
                                    </a>
                                    <div class="text-[10px] text-slate-400">{{ $rec->customer->mobile }}</div>
                                @elseif($rec->salesCall?->lead)
                                    <a href="{{ route('leads.show', $rec->salesCall->lead_id) }}" class="font-bold text-blue-700 hover:underline">
                                        {{ $rec->salesCall->lead->name }}
                                    </a>
                                    <div class="text-[10px] text-slate-400">Lead Contact</div>
                                @else
                                    <span class="text-slate-400">Contact #{{ $rec->sales_call_id }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 font-medium text-slate-800">
                                {{ $rec->user?->name ?? 'Staff' }}
                            </td>
                            <td class="py-3 px-4 text-slate-500 font-mono">
                                {{ $rec->created_at->format('d M Y, H:i') }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 text-[10px] font-semibold rounded {{ ($rec->salesCall?->direction ?? 'outgoing') === 'incoming' ? 'bg-blue-50 text-blue-700' : 'bg-teal-50 text-teal-700' }}">
                                    {{ ucfirst($rec->salesCall?->direction ?? 'outgoing') }}
                                </span>
                                <div class="text-[10px] text-slate-500 font-medium mt-0.5">
                                    {{ $rec->salesCall?->outcome ?? 'Connected' }}
                                </div>
                            </td>
                            <td class="py-3 px-4 font-mono font-bold text-slate-800">
                                {{ $rec->duration_formatted }}
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2">
                                    <audio controls class="h-8 w-52 rounded shadow-xs" preload="none">
                                        <source src="{{ route('call-recordings.play', $rec->id) }}" type="{{ $rec->mime_type ?: 'audio/wav' }}">
                                        Audio player not supported.
                                    </audio>
                                    <a href="{{ route('call-recordings.play', $rec->id) }}" target="_blank" download title="Download Audio" class="text-slate-400 hover:text-teal-700 p-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    </a>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-slate-400 text-[10px] font-mono">
                                {{ number_format($rec->file_size / 1024, 1) }} KB
                                <div class="text-[9px] truncate max-w-[120px]">{{ $rec->file_name }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">No call recordings stored yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($recordings->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $recordings->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
