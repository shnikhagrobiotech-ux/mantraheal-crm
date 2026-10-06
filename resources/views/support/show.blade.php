@extends('layouts.app')

@section('title', "Ticket #{$support->ticket_number} — MantraHeal CRM")

@section('content')
<div class="space-y-6" x-data="{ isInternalNote: false }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('support.index') }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Support Tickets
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-xs text-slate-500 font-mono">{{ $support->ticket_number }}</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">{{ $support->subject }}</h1>
                @php
                    $statBadge = match($support->status) {
                        'Resolved', 'Closed' => 'badge-success',
                        'In Progress' => 'badge-primary',
                        default => 'badge-warning'
                    };
                @endphp
                <span class="badge {{ $statBadge }} text-xs">{{ $support->status }}</span>
                <span class="badge bg-slate-100 text-slate-700 text-xs">{{ $support->category }}</span>
            </div>
        </div>

        <!-- Status updater form -->
        <form action="{{ route('support.status', $support) }}" method="POST" class="flex items-center gap-2">
            @csrf
            <select name="status" class="form-control text-xs py-1.5 font-semibold">
                <option value="Open" {{ $support->status === 'Open' ? 'selected' : '' }}>Open</option>
                <option value="In Progress" {{ $support->status === 'In Progress' ? 'selected' : '' }}>In Progress</option>
                <option value="Resolved" {{ $support->status === 'Resolved' ? 'selected' : '' }}>Resolved</option>
                <option value="Closed" {{ $support->status === 'Closed' ? 'selected' : '' }}>Closed</option>
            </select>
            <button type="submit" class="btn btn-secondary text-xs py-1.5 px-3">Update Status</button>
        </form>
    </div>

    <!-- Ticket Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Conversation Thread -->
        <div class="lg:col-span-2 space-y-6">
            <div class="card p-6 space-y-6">
                <h2 class="font-bold text-slate-800 text-sm border-b border-slate-100 pb-3">Conversation & Activity Log</h2>

                <!-- Messages Thread -->
                <div class="space-y-4">
                    @forelse($support->messages as $msg)
                        <div class="p-4 rounded-xl text-sm {{ $msg->is_internal_note ? 'bg-amber-50/70 border border-amber-200/80 text-amber-950' : 'bg-slate-50 border border-slate-200/80 text-slate-800' }}">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-xs">{{ $msg->user?->name ?? 'MantraHeal Support' }}</span>
                                    @if($msg->is_internal_note)
                                        <span class="badge bg-amber-200 text-amber-900 text-[10px] font-semibold">Internal Team Note</span>
                                    @endif
                                </div>
                                <span class="text-[11px] text-slate-400">{{ $msg->created_at->format('d M Y, h:i A') }}</span>
                            </div>
                            <div class="text-xs leading-relaxed whitespace-pre-wrap">{{ $msg->message }}</div>
                        </div>
                    @empty
                        <div class="text-center py-6 text-slate-400 text-xs">No responses logged yet.</div>
                    @endforelse
                </div>

                <!-- Add Response Box -->
                <div class="pt-4 border-t border-slate-100">
                    <form action="{{ route('support.message', $support) }}" method="POST" class="space-y-3">
                        @csrf
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-semibold text-slate-600">Reply to Customer / Add Internal Memo</label>
                            <label class="inline-flex items-center gap-1.5 text-xs text-amber-800 cursor-pointer">
                                <input type="checkbox" name="is_internal_note" value="1" x-model="isInternalNote" class="rounded text-amber-600">
                                <span>Save as Internal Staff Note</span>
                            </label>
                        </div>
                        <textarea name="message" rows="3" required placeholder="Write message to customer or internal handover notes..." class="form-control w-full text-sm"></textarea>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <label class="text-xs text-slate-500">Change Status To:</label>
                                <select name="status" class="form-control text-xs py-1">
                                    <option value="">Keep Current Status</option>
                                    <option value="In Progress">In Progress</option>
                                    <option value="Resolved">Resolved</option>
                                    <option value="Closed">Closed</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary text-xs py-2 px-4 shadow-sm">
                                Send Message / Post Note
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Side: Customer & Ticket Meta -->
        <div class="space-y-6">
            <!-- Customer Card -->
            <div class="card p-5 space-y-3 text-sm">
                <h3 class="font-bold text-slate-800 text-sm border-b border-slate-100 pb-2">Customer Information</h3>
                @if($support->customer)
                    <div class="space-y-1.5 text-xs">
                        <div class="font-bold text-slate-800 text-sm">{{ $support->customer->name }}</div>
                        <div class="text-slate-500">Phone: {{ $support->customer->phone }}</div>
                        <div class="text-slate-500">Email: {{ $support->customer->email ?? '—' }}</div>
                        <div class="text-slate-500">City: {{ $support->customer->city }}, {{ $support->customer->state }}</div>
                        <div class="pt-2">
                            <a href="{{ route('customers.show', $support->customer) }}" class="text-emerald-700 font-semibold hover:underline">
                                Customer 360° Profile →
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Order Link Card -->
            @if($support->order)
                <div class="card p-5 space-y-3 text-sm">
                    <h3 class="font-bold text-slate-800 text-sm border-b border-slate-100 pb-2">Associated Order</h3>
                    <div class="space-y-1.5 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Order Number:</span>
                            <a href="{{ route('orders.show', $support->order) }}" class="font-mono font-bold text-emerald-700 hover:underline">
                                {{ $support->order->order_number }}
                            </a>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Total Amount:</span>
                            <span class="font-bold text-slate-800">₹{{ number_format($support->order->total_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Order Status:</span>
                            <span class="capitalize font-medium text-slate-700">{{ $support->order->status }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Courier:</span>
                            <span>{{ $support->order->courier_name ?? '—' }}</span>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Ticket Meta -->
            <div class="card p-5 space-y-3 text-sm">
                <h3 class="font-bold text-slate-800 text-sm border-b border-slate-100 pb-2">Ticket Properties</h3>
                <div class="space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Priority:</span>
                        <span class="font-bold text-slate-800">{{ $support->priority }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Category:</span>
                        <span class="text-slate-700">{{ $support->category }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Assigned Agent:</span>
                        <span class="font-medium text-slate-800">{{ $support->assignedUser?->name ?? 'Unassigned' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Created Date:</span>
                        <span class="text-slate-700">{{ $support->created_at->format('d M Y') }}</span>
                    </div>
                    @if($support->resolved_at)
                        <div class="flex justify-between text-emerald-700">
                            <span>Resolved On:</span>
                            <span>{{ $support->resolved_at->format('d M Y, h:i A') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
