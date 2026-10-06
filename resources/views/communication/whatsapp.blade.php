@extends('layouts.app')

@section('title', 'WhatsApp Business Hub — MantraHeal CRM')

@section('content')
<div class="space-y-6" x-data="{ showSendModal: false, selectedTemplate: '', recipientType: 'customer' }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xs text-slate-500">Omnichannel Communication</span>
                <span class="text-slate-300">/</span>
                <span class="text-xs text-slate-500">WhatsApp</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">WhatsApp Business API Center</h1>
            <p class="text-sm text-slate-500">Send order updates, tracking alerts, and wellness consultations via WhatsApp Cloud API</p>
        </div>
        <div>
            <button type="button" @click="showSendModal = true" class="btn btn-primary text-sm inline-flex items-center gap-2 bg-emerald-700 hover:bg-emerald-800">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766 0-3.187-2.59-5.771-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.288.043.088.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.353.101.174.449.741.964 1.2.662.59 1.221.774 1.394.86.173.086.275.073.376-.044.101-.116.433-.506.549-.68.116-.173.231-.144.39-.086s1.011.477 1.184.564.289.13.332.203c.043.071.043.418-.101.823z"/></svg>
                Compose WhatsApp Message
            </button>
        </div>
    </div>

    <!-- Pre-Approved Templates Grid -->
    <div class="card p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h2 class="font-bold text-slate-800 text-sm">Pre-Approved Notification Templates</h2>
                <p class="text-xs text-slate-500">Standard transactional templates configured for automated order lifecycle triggers</p>
            </div>
            <span class="badge bg-emerald-50 text-emerald-800 text-xs font-semibold">{{ $templates->count() }} Templates Active</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($templates as $tmpl)
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200/80 space-y-2.5 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="badge bg-white border border-slate-200 text-slate-700 text-[10px] font-mono">{{ $tmpl->slug }}</span>
                            <span class="badge badge-success text-[10px]">Verified</span>
                        </div>
                        <h4 class="font-bold text-slate-800 text-sm mt-2">{{ $tmpl->name }}</h4>
                        <p class="text-xs text-slate-600 mt-1 line-clamp-3 leading-relaxed">
                            {{ $tmpl->content }}
                        </p>
                    </div>
                    <div class="pt-2 border-t border-slate-200/60 flex justify-between items-center text-xs">
                        <span class="text-slate-400 capitalize">{{ $tmpl->category ?? 'Transactional' }}</span>
                        <button type="button" @click="selectedTemplate = '{{ $tmpl->slug }}'; showSendModal = true" class="text-emerald-700 font-bold hover:underline">
                            Send Template →
                        </button>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-8 text-center text-slate-400 text-xs">
                    No pre-configured templates found.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Communication Log Table -->
    <div class="card overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold text-slate-800 text-sm">Dispatched WhatsApp Log & Delivery Status</h2>
            <span class="text-xs text-slate-500">{{ $logs->total() }} messages sent</span>
        </div>
        <div class="overflow-x-auto">
            <table class="table w-full text-sm">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Recipient</th>
                        <th>Mobile Number</th>
                        <th>Template / Message Content</th>
                        <th>Sent By</th>
                        <th>Delivery Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/75 transition-colors">
                            <td class="text-xs text-slate-500 whitespace-nowrap font-mono">
                                {{ $log->created_at->format('d M Y, h:i A') }}
                            </td>
                            <td class="whitespace-nowrap">
                                @if($log->customer)
                                    <div class="font-medium text-slate-800">
                                        <a href="{{ route('customers.show', $log->customer) }}" class="hover:text-emerald-700">
                                            {{ $log->customer->name }}
                                        </a>
                                    </div>
                                    <span class="badge bg-emerald-50 text-emerald-800 text-[10px]">Customer</span>
                                @elseif($log->lead)
                                    <div class="font-medium text-slate-800">
                                        <a href="{{ route('leads.show', $log->lead) }}" class="hover:text-emerald-700">
                                            {{ $log->lead->name }}
                                        </a>
                                    </div>
                                    <span class="badge bg-blue-50 text-blue-800 text-[10px]">Lead</span>
                                @else
                                    <span class="text-slate-400">Direct Recipient</span>
                                @endif
                            </td>
                            <td class="font-mono text-xs text-slate-700 whitespace-nowrap">
                                {{ $log->phone_number ?? $log->customer?->phone ?? $log->lead?->phone }}
                            </td>
                            <td class="text-xs text-slate-600 max-w-md">
                                @if($log->template)
                                    <span class="badge bg-slate-100 text-slate-700 text-[10px] mr-1">{{ $log->template->name }}</span>
                                @endif
                                <span class="truncate inline-block max-w-xs align-bottom">{{ $log->message ?? $log->template?->content }}</span>
                            </td>
                            <td class="text-xs text-slate-500 whitespace-nowrap">
                                {{ $log->user?->name ?? 'System Automated' }}
                            </td>
                            <td class="whitespace-nowrap">
                                @php
                                    $stBadge = match($log->status) {
                                        'delivered', 'read' => 'badge-success',
                                        'sent' => 'badge-info',
                                        'failed' => 'badge-danger',
                                        default => 'badge-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $stBadge }} text-[10px] capitalize">
                                    {{ $log->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-12 text-slate-400 text-sm">
                                No WhatsApp communication records dispatched yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

    <!-- Send WhatsApp Modal -->
    <div x-show="showSendModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 flex items-center justify-center p-4" style="display: none;">
        <div class="card max-w-lg w-full p-6 space-y-4 shadow-2xl bg-white" @click.away="showSendModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-800">Dispatch WhatsApp Message</h3>
                <button type="button" @click="showSendModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            
            <form action="{{ route('communication.whatsapp.send') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Target Recipient Type *</label>
                    <div class="flex gap-4">
                        <label class="inline-flex items-center gap-2 cursor-pointer text-sm">
                            <input type="radio" name="recipient_type" value="customer" x-model="recipientType" class="text-emerald-700">
                            <span>Existing Customer</span>
                        </label>
                        <label class="inline-flex items-center gap-2 cursor-pointer text-sm">
                            <input type="radio" name="recipient_type" value="lead" x-model="recipientType" class="text-emerald-700">
                            <span>Sales Lead</span>
                        </label>
                    </div>
                </div>

                <div x-show="recipientType === 'customer'">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Choose Customer *</label>
                    <select name="recipient_id" class="form-control w-full text-sm" :required="recipientType === 'customer'">
                        <option value="">-- Select Customer --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->name }} ({{ $c->phone }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div x-show="recipientType === 'lead'" style="display: none;">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Choose Lead *</label>
                    <select name="recipient_id" class="form-control w-full text-sm" :required="recipientType === 'lead'">
                        <option value="">-- Select Lead --</option>
                        @foreach($leads as $l)
                            <option value="{{ $l->id }}">{{ $l->name }} ({{ $l->phone }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Message Template</label>
                    <select name="template_slug" x-model="selectedTemplate" class="form-control w-full text-sm">
                        <option value="">-- Custom Message (Free Text) --</option>
                        @foreach($templates as $tmpl)
                            <option value="{{ $tmpl->slug }}">{{ $tmpl->name }} ({{ $tmpl->slug }})</option>
                        @endforeach
                    </select>
                </div>

                <div x-show="!selectedTemplate">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Custom Message Text *</label>
                    <textarea name="custom_message" rows="3" placeholder="Type direct WhatsApp update or consultation note..." class="form-control w-full text-sm"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="showSendModal = false" class="btn btn-secondary text-xs">Cancel</button>
                    <button type="submit" class="btn btn-primary text-xs bg-emerald-700 hover:bg-emerald-800">
                        Dispatch via WhatsApp
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
