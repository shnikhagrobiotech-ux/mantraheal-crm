@extends('layouts.app')

@section('title', 'Customer Support & Complaints — MantraHeal CRM')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xs text-slate-500">Customer Success</span>
                <span class="text-slate-300">/</span>
                <span class="text-xs text-slate-500">Tickets & Complaints</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Support Tickets & Complaints</h1>
            <p class="text-sm text-slate-500">Resolve customer grievances, dosage inquiries, delivery queries, and product feedback</p>
        </div>
        <a href="{{ route('support.create') }}" class="btn btn-primary text-sm inline-flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Open New Ticket
        </a>
    </div>

    <!-- Filters -->
    <div class="card p-4">
        <form method="GET" action="{{ route('support.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <select name="status" class="form-control text-sm w-full">
                    <option value="">All Statuses</option>
                    <option value="Open" {{ request('status') == 'Open' ? 'selected' : '' }}>Open</option>
                    <option value="In Progress" {{ request('status') == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="Resolved" {{ request('status') == 'Resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="Closed" {{ request('status') == 'Closed' ? 'selected' : '' }}>Closed</option>
                </select>
            </div>
            <div>
                <select name="priority" class="form-control text-sm w-full">
                    <option value="">All Priorities</option>
                    <option value="Urgent" {{ request('priority') == 'Urgent' ? 'selected' : '' }}>Urgent</option>
                    <option value="High" {{ request('priority') == 'High' ? 'selected' : '' }}>High</option>
                    <option value="Medium" {{ request('priority') == 'Medium' ? 'selected' : '' }}>Medium</option>
                    <option value="Low" {{ request('priority') == 'Low' ? 'selected' : '' }}>Low</option>
                </select>
            </div>
            <div>
                <select name="category" class="form-control text-sm w-full">
                    <option value="">All Categories</option>
                    <option value="Delivery Delay" {{ request('category') == 'Delivery Delay' ? 'selected' : '' }}>Delivery Delay</option>
                    <option value="Damaged Product" {{ request('category') == 'Damaged Product' ? 'selected' : '' }}>Damaged Product</option>
                    <option value="Dosage Inquiry" {{ request('category') == 'Dosage Inquiry' ? 'selected' : '' }}>Dosage & Consultation</option>
                    <option value="Payment Issue" {{ request('category') == 'Payment Issue' ? 'selected' : '' }}>Payment Issue</option>
                    <option value="Other" {{ request('category') == 'Other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="btn btn-primary text-sm py-2 px-4 flex-1">Filter</button>
                <a href="{{ route('support.index') }}" class="btn btn-secondary text-sm py-2 px-3">Reset</a>
            </div>
        </form>
    </div>

    <!-- Tickets Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table w-full text-sm">
                <thead>
                    <tr>
                        <th>Ticket #</th>
                        <th>Created</th>
                        <th>Customer</th>
                        <th>Subject & Category</th>
                        <th>Order #</th>
                        <th>Priority</th>
                        <th>Assigned Agent</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($tickets as $ticket)
                        <tr class="hover:bg-slate-50/75 transition-colors">
                            <td class="font-mono text-xs font-bold text-emerald-800 whitespace-nowrap">
                                <a href="{{ route('support.show', $ticket) }}" class="hover:underline">
                                    {{ $ticket->ticket_number }}
                                </a>
                            </td>
                            <td class="text-xs text-slate-500 whitespace-nowrap">
                                {{ $ticket->created_at->format('d M Y') }}
                            </td>
                            <td class="whitespace-nowrap">
                                <div class="font-medium text-slate-800">{{ $ticket->customer?->name }}</div>
                                <div class="text-xs text-slate-400">{{ $ticket->customer?->phone }}</div>
                            </td>
                            <td>
                                <div class="font-semibold text-slate-800">{{ $ticket->subject }}</div>
                                <span class="badge bg-slate-100 text-slate-700 text-[10px]">{{ $ticket->category }}</span>
                            </td>
                            <td class="font-mono text-xs whitespace-nowrap">
                                @if($ticket->order)
                                    <a href="{{ route('orders.show', $ticket->order) }}" class="text-emerald-700 hover:underline">
                                        {{ $ticket->order->order_number }}
                                    </a>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $prioBadge = match($ticket->priority) {
                                        'Urgent' => 'badge-danger',
                                        'High' => 'badge-warning',
                                        'Medium' => 'badge-info',
                                        default => 'badge-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $prioBadge }} text-[10px]">{{ $ticket->priority }}</span>
                            </td>
                            <td class="text-xs text-slate-600 whitespace-nowrap">
                                {{ $ticket->assignedUser?->name ?? 'Unassigned' }}
                            </td>
                            <td>
                                @php
                                    $statBadge = match($ticket->status) {
                                        'Resolved', 'Closed' => 'badge-success',
                                        'In Progress' => 'badge-primary',
                                        default => 'badge-warning'
                                    };
                                @endphp
                                <span class="badge {{ $statBadge }} text-[10px]">{{ $ticket->status }}</span>
                            </td>
                            <td class="text-right whitespace-nowrap">
                                <a href="{{ route('support.show', $ticket) }}" class="btn btn-secondary text-xs py-1 px-2.5">
                                    View Thread
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-12 text-slate-400 text-sm">
                                No support tickets or complaints recorded.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tickets->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $tickets->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
