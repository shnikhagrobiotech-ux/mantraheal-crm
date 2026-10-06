@extends('layouts.app')

@section('title', 'Customer 360 - ' . $customer->name)
@section('subtitle', 'Unified profile, order history, communication logs & activity timeline')

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'timeline' }">

    <!-- Top Customer Summary Card -->
    <div class="card-elevated p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <!-- Customer Avatar & Identity -->
            <div class="flex items-start space-x-4">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-teal-600 to-emerald-700 text-white flex items-center justify-center font-bold text-2xl shadow-lg flex-shrink-0">
                    {{ substr($customer->name, 0, 2) }}
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <h2 class="text-xl font-bold text-slate-900">{{ $customer->name }}</h2>
                        <span class="px-2 py-0.5 text-xs font-semibold rounded bg-teal-50 text-teal-700 border border-teal-200">
                            {{ $customer->customer_type }}
                        </span>
                        <span class="px-2 py-0.5 text-xs font-semibold rounded {{ $customer->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                            {{ ucfirst($customer->status) }}
                        </span>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500 mt-1">
                        <span class="font-mono text-slate-600">{{ $customer->customer_code }}</span>
                        <span>•</span>
                        <span class="flex items-center text-slate-700 font-medium">
                            <svg class="w-3.5 h-3.5 text-teal-600 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                            {{ $customer->mobile }}
                        </span>
                        @if($customer->whatsapp)
                            <span>•</span>
                            <span class="flex items-center text-emerald-700 font-medium">
                                WA: {{ $customer->whatsapp }}
                            </span>
                        @endif
                        @if($customer->email)
                            <span>•</span>
                            <span>{{ $customer->email }}</span>
                        @endif
                    </div>
                    <div class="text-xs text-slate-500 mt-1">
                        Source: <span class="font-medium text-slate-700">{{ $customer->customer_source }}</span> |
                        Assigned To: <span class="font-medium text-slate-700">{{ $customer->assignedUser?->name ?? 'Unassigned' }}</span> |
                        Default Address: <span class="text-slate-600">{{ $customer->defaultAddress?->full_address ?? 'No address saved' }}</span>
                    </div>
                </div>
            </div>

            <!-- Quick Action Buttons -->
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('calls.create', ['customer_id' => $customer->id]) }}" class="btn-primary text-xs py-2 px-3">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                    Log Call
                </a>
                <a href="{{ route('orders.create', ['customer_id' => $customer->id]) }}" class="btn-secondary text-xs py-2 px-3">
                    <svg class="w-4 h-4 mr-1 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    Create Order
                </a>
                <a href="{{ route('customers.edit', $customer->id) }}" class="btn-secondary text-xs py-2 px-3">
                    Edit Profile
                </a>
            </div>
        </div>

        <!-- Calculated Customer Metrics (Section 7) -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mt-6 pt-5 border-t border-slate-100 text-center">
            <div class="p-3 bg-slate-50 rounded-lg">
                <div class="text-xs text-slate-500 font-medium">Total Orders</div>
                <div class="text-xl font-bold text-slate-900 mt-0.5">{{ $customer->total_orders }}</div>
                <div class="text-[10px] text-teal-600 font-semibold">{{ $customer->total_orders > 1 ? 'Repeat Customer' : 'First Order' }}</div>
            </div>
            <div class="p-3 bg-slate-50 rounded-lg">
                <div class="text-xs text-slate-500 font-medium">Lifetime Spend</div>
                <div class="text-xl font-bold text-teal-700 mt-0.5">₹{{ number_format($customer->total_spend, 2) }}</div>
                <div class="text-[10px] text-slate-400">Total Purchase Value</div>
            </div>
            <div class="p-3 bg-slate-50 rounded-lg">
                <div class="text-xs text-slate-500 font-medium">Avg Order Value</div>
                <div class="text-xl font-bold text-slate-900 mt-0.5">₹{{ number_format($customer->average_order_value, 2) }}</div>
                <div class="text-[10px] text-slate-400">AOV Metric</div>
            </div>
            <div class="p-3 bg-slate-50 rounded-lg">
                <div class="text-xs text-slate-500 font-medium">First Order</div>
                <div class="text-sm font-semibold text-slate-800 mt-1">{{ $customer->first_order_at ? $customer->first_order_at->format('d M Y') : 'None' }}</div>
                <div class="text-[10px] text-slate-400">Acquisition Date</div>
            </div>
            <div class="p-3 bg-slate-50 rounded-lg">
                <div class="text-xs text-slate-500 font-medium">Last Order</div>
                <div class="text-sm font-semibold text-slate-800 mt-1">{{ $customer->last_order_at ? $customer->last_order_at->format('d M Y') : 'None' }}</div>
                <div class="text-[10px] text-slate-400">Recent Purchase</div>
            </div>
            <div class="p-3 bg-slate-50 rounded-lg">
                <div class="text-xs text-slate-500 font-medium">Outstanding</div>
                <div class="text-xl font-bold {{ $customer->outstanding_amount > 0 ? 'text-rose-600' : 'text-slate-900' }} mt-0.5">
                    ₹{{ number_format($customer->outstanding_amount, 2) }}
                </div>
                <div class="text-[10px] text-slate-400">Unsettled Balance</div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-slate-200 flex space-x-6 text-xs font-semibold">
        <button @click="activeTab = 'timeline'" :class="activeTab === 'timeline' ? 'border-teal-600 text-teal-600 border-b-2 py-3' : 'text-slate-500 hover:text-slate-700 py-3'">
            Activity Timeline ({{ $timeline->count() }})
        </button>
        <button @click="activeTab = 'orders'" :class="activeTab === 'orders' ? 'border-teal-600 text-teal-600 border-b-2 py-3' : 'text-slate-500 hover:text-slate-700 py-3'">
            Orders ({{ $customer->orders->count() }})
        </button>
        <button @click="activeTab = 'calls'" :class="activeTab === 'calls' ? 'border-teal-600 text-teal-600 border-b-2 py-3' : 'text-slate-500 hover:text-slate-700 py-3'">
            Calls & Recordings ({{ $customer->salesCalls->count() }})
        </button>
        <button @click="activeTab = 'followups'" :class="activeTab === 'followups' ? 'border-teal-600 text-teal-600 border-b-2 py-3' : 'text-slate-500 hover:text-slate-700 py-3'">
            Follow-ups ({{ $customer->followups->count() }})
        </button>
        <button @click="activeTab = 'whatsapp'" :class="activeTab === 'whatsapp' ? 'border-teal-600 text-teal-600 border-b-2 py-3' : 'text-slate-500 hover:text-slate-700 py-3'">
            WhatsApp Communication ({{ $customer->communicationLogs->count() }})
        </button>
        <button @click="activeTab = 'returns'" :class="activeTab === 'returns' ? 'border-teal-600 text-teal-600 border-b-2 py-3' : 'text-slate-500 hover:text-slate-700 py-3'">
            Returns & QC ({{ $customer->returns->count() }})
        </button>
        <button @click="activeTab = 'tickets'" :class="activeTab === 'tickets' ? 'border-teal-600 text-teal-600 border-b-2 py-3' : 'text-slate-500 hover:text-slate-700 py-3'">
            Support Complaints ({{ $customer->tickets->count() }})
        </button>
    </div>

    <!-- TAB 1: UNIFIED CHRONOLOGICAL ACTIVITY TIMELINE (Section 8) -->
    <div x-show="activeTab === 'timeline'" class="card-elevated p-6">
        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-6 flex items-center">
            <svg class="w-4 h-4 mr-2 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Customer Journey Timeline
        </h3>

        <div class="space-y-6">
            @forelse($timeline as $event)
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                            <div class="flex items-center space-x-2">
                                <span class="font-bold text-sm text-slate-800">{{ $event['title'] }}</span>
                                <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full {{ $event['badge'] }}">
                                    {{ ucfirst(str_replace('_', ' ', $event['type'])) }}
                                </span>
                            </div>
                            <span class="text-xs font-mono text-slate-400">
                                {{ \Carbon\Carbon::parse($event['datetime'])->format('d M Y, h:i A') }}
                            </span>
                        </div>

                        <p class="text-xs text-slate-600 mt-2">{{ $event['description'] }}</p>

                        <!-- If this event has a Call Recording audio player -->
                        @if(!empty($event['has_audio']) && !empty($event['audio_url']))
                            <div class="mt-3 audio-player-container">
                                <span class="text-xs font-semibold text-teal-700 flex items-center">
                                    <svg class="w-4 h-4 mr-1 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Audio Recording:
                                </span>
                                <audio controls preload="none">
                                    <source src="{{ $event['audio_url'] }}" type="audio/wav">
                                    Your browser does not support audio playback.
                                </audio>
                            </div>
                        @endif

                        <div class="mt-2 text-[11px] text-slate-400 flex items-center justify-between">
                            <span>Actor: <strong class="text-slate-600 font-medium">{{ $event['actor'] }}</strong></span>
                            @if(!empty($event['link']))
                                <a href="{{ $event['link'] }}" class="text-teal-600 hover:underline font-medium">View Details &rarr;</a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-slate-400 text-xs">
                    No timeline events recorded for this customer yet.
                </div>
            @endforelse
        </div>
    </div>

    <!-- TAB 2: ORDERS -->
    <div x-show="activeTab === 'orders'" class="card-elevated overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800">Customer Orders History</h3>
            <a href="{{ route('orders.create', ['customer_id' => $customer->id]) }}" class="btn-primary text-xs py-1.5 px-3">+ New Order</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Order #</th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Items</th>
                        <th class="py-3 px-4">Subtotal</th>
                        <th class="py-3 px-4">GST (₹)</th>
                        <th class="py-3 px-4">Total</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Payment</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($customer->orders as $ord)
                        <tr class="hover:bg-slate-50">
                            <td class="py-3 px-4 font-bold text-teal-600">
                                <a href="{{ route('orders.show', $ord->id) }}">{{ $ord->order_number }}</a>
                            </td>
                            <td class="py-3 px-4 text-slate-500">{{ $ord->order_date->format('d M Y') }}</td>
                            <td class="py-3 px-4">{{ $ord->items->count() }} items</td>
                            <td class="py-3 px-4">₹{{ number_format($ord->subtotal, 2) }}</td>
                            <td class="py-3 px-4">₹{{ number_format($ord->total_tax, 2) }}</td>
                            <td class="py-3 px-4 font-bold text-slate-900">₹{{ number_format($ord->grand_total, 2) }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 text-[10px] rounded-full font-semibold {{ $ord->status_badge_class }}">
                                    {{ $ord->order_status }}
                                </span>
                            </td>
                            <td class="py-3 px-4 font-medium text-slate-700">{{ $ord->payment_status }}</td>
                            <td class="py-3 px-4 text-right space-x-2">
                                <a href="{{ route('orders.show', $ord->id) }}" class="text-teal-600 hover:underline">View</a>
                                <a href="{{ route('orders.invoice', $ord->id) }}" target="_blank" class="text-slate-600 hover:underline">Invoice</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-6 text-center text-slate-400">No orders placed by this customer yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 3: SALES CALLS & CALL RECORDINGS (Section 11) -->
    <div x-show="activeTab === 'calls'" class="space-y-4">
        <div class="card-elevated p-4 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800">Sales Calls & Audio Recordings</h3>
            <a href="{{ route('calls.create', ['customer_id' => $customer->id]) }}" class="btn-primary text-xs py-1.5 px-3">+ Log Call</a>
        </div>

        <div class="grid grid-cols-1 gap-4">
            @forelse($customer->salesCalls as $call)
                <div class="card-elevated p-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-slate-100 pb-3">
                        <div class="flex items-center space-x-3">
                            <span class="p-2 rounded-lg {{ $call->direction === 'incoming' ? 'bg-blue-50 text-blue-600' : 'bg-teal-50 text-teal-600' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                            </span>
                            <div>
                                <div class="font-bold text-sm text-slate-800 capitalize">{{ $call->direction }} Call — {{ $call->outcome }}</div>
                                <div class="text-xs text-slate-400">Executive: {{ $call->user?->name ?? 'Staff' }} | Duration: {{ $call->duration_formatted }}</div>
                            </div>
                        </div>
                        <div class="text-xs text-slate-500 font-mono">
                            {{ $call->call_datetime->format('d M Y, h:i A') }}
                        </div>
                    </div>

                    @if($call->notes)
                        <div class="mt-3 text-xs text-slate-600 bg-slate-50 p-2.5 rounded-lg">
                            <strong class="text-slate-700">Notes:</strong> {{ $call->notes }}
                        </div>
                    @endif

                    <!-- Protected Audio Player -->
                    @if($call->recording)
                        <div class="mt-3 audio-player-container">
                            <span class="text-xs font-semibold text-teal-700 flex items-center">
                                ▶ Play Recording:
                            </span>
                            <audio controls preload="none">
                                <source src="{{ route('call-recordings.play', $call->recording->id) }}" type="{{ $call->recording->mime_type ?: 'audio/wav' }}">
                                Audio format not supported.
                            </audio>
                        </div>
                    @endif
                </div>
            @empty
                <div class="card-elevated p-8 text-center text-slate-400 text-xs">
                    No sales calls logged with this customer yet.
                </div>
            @endforelse
        </div>
    </div>

    <!-- TAB 4: FOLLOW-UPS -->
    <div x-show="activeTab === 'followups'" class="space-y-4">
        <!-- Add Follow-up Form -->
        <div class="card-elevated p-4">
            <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">Schedule Follow-up</h4>
            <form method="POST" action="{{ route('followups.store') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                @csrf
                <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Due Date</label>
                    <input type="date" name="due_date" value="{{ now()->addDay()->toDateString() }}" required
                           class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-teal-500">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Reason</label>
                    <input type="text" name="reason" placeholder="e.g. Order Confirmation, Dosage Guide" required
                           class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-teal-500">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Priority</label>
                    <select name="priority" class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs bg-white">
                        <option value="Low">Low</option>
                        <option value="Medium" selected>Medium</option>
                        <option value="High">High</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="btn-primary text-xs py-1.5 px-4 w-full">Schedule</button>
                </div>
            </form>
        </div>

        <!-- Follow-ups List -->
        <div class="card-elevated overflow-hidden">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="py-2.5 px-4">Due Date</th>
                        <th class="py-2.5 px-4">Reason</th>
                        <th class="py-2.5 px-4">Priority</th>
                        <th class="py-2.5 px-4">Status</th>
                        <th class="py-2.5 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($customer->followups as $f)
                        <tr>
                            <td class="py-2.5 px-4 font-medium {{ $f->due_date->isPast() && $f->status === 'Pending' ? 'text-rose-600 font-bold' : '' }}">
                                {{ $f->due_date->format('d M Y') }}
                            </td>
                            <td class="py-2.5 px-4">{{ $f->reason }}</td>
                            <td class="py-2.5 px-4">
                                <span class="px-2 py-0.5 text-[10px] rounded font-semibold {{ $f->priority === 'High' ? 'bg-rose-50 text-rose-700' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $f->priority }}
                                </span>
                            </td>
                            <td class="py-2.5 px-4">
                                <span class="px-2 py-0.5 text-[10px] rounded-full font-semibold {{ $f->status === 'Completed' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ $f->status }}
                                </span>
                            </td>
                            <td class="py-2.5 px-4 text-right">
                                @if($f->status === 'Pending')
                                    <form method="POST" action="{{ route('followups.complete', $f->id) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-emerald-600 hover:underline font-semibold text-xs">Mark Done</button>
                                    </form>
                                @else
                                    <span class="text-slate-400">Completed</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-4 text-center text-slate-400">No follow-ups for this customer.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 5: WHATSAPP COMMUNICATION -->
    <div x-show="activeTab === 'whatsapp'" class="space-y-4">
        <!-- Quick WhatsApp Form -->
        <div class="card-elevated p-4">
            <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">Send WhatsApp Business Message</h4>
            <form method="POST" action="{{ route('communication.whatsapp.send') }}" class="space-y-3">
                @csrf
                <input type="hidden" name="recipient_type" value="customer">
                <input type="hidden" name="recipient_id" value="{{ $customer->id }}">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Pre-approved Template</label>
                        <select name="template_slug" class="w-full px-2.5 py-2 border border-slate-300 rounded text-xs bg-white">
                            <option value="">-- Custom Message (Direct) --</option>
                            <option value="welcome">Welcome to MantraHeal</option>
                            <option value="order_confirmation">Order Confirmation</option>
                            <option value="dispatch">Order Dispatched & Tracking</option>
                            <option value="delivery">Delivery Confirmation & Health Guide</option>
                            <option value="repeat_purchase">Ayurvedic Course Refill Reminder</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Or Type Custom Text</label>
                        <input type="text" name="custom_message" placeholder="Message content..."
                               class="w-full px-2.5 py-2 border border-slate-300 rounded text-xs">
                    </div>
                </div>
                <div class="text-right">
                    <button type="submit" class="btn-primary text-xs py-1.5 px-4">
                        Send via WhatsApp
                    </button>
                </div>
            </form>
        </div>

        <!-- WhatsApp History -->
        <div class="card-elevated p-4">
            <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">Communication Logs</h4>
            <div class="space-y-3">
                @forelse($customer->communicationLogs as $log)
                    <div class="p-3 rounded-lg border border-slate-100 bg-slate-50 flex items-start justify-between">
                        <div>
                            <div class="text-xs font-bold text-slate-800">{{ $log->subject }}</div>
                            <div class="text-xs text-slate-600 mt-1">{{ $log->message_body }}</div>
                            <div class="text-[10px] text-slate-400 mt-1">Status: {{ $log->status }} | ID: {{ $log->external_id }}</div>
                        </div>
                        <span class="text-[10px] font-mono text-slate-400">{{ $log->created_at->format('d M H:i') }}</span>
                    </div>
                @empty
                    <div class="text-center py-4 text-slate-400 text-xs">No WhatsApp logs recorded yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- TAB 6: RETURNS -->
    <div x-show="activeTab === 'returns'" class="card-elevated p-4">
        <h3 class="text-sm font-bold text-slate-800 mb-3">Customer Returns & QC</h3>
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="py-2.5 px-3">Return #</th>
                    <th class="py-2.5 px-3">Date</th>
                    <th class="py-2.5 px-3">Reason</th>
                    <th class="py-2.5 px-3">Status</th>
                    <th class="py-2.5 px-3">QC Status</th>
                    <th class="py-2.5 px-3">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                @forelse($customer->returns as $ret)
                    <tr>
                        <td class="py-2.5 px-3 font-bold text-teal-600">{{ $ret->return_number }}</td>
                        <td class="py-2.5 px-3 text-slate-500">{{ $ret->return_date->format('d M Y') }}</td>
                        <td class="py-2.5 px-3">{{ $ret->reason }}</td>
                        <td class="py-2.5 px-3">{{ $ret->status }}</td>
                        <td class="py-2.5 px-3">
                            <span class="px-2 py-0.5 text-[10px] font-semibold rounded {{ $ret->qc_status === 'Passed' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">
                                {{ $ret->qc_status }}
                            </span>
                        </td>
                        <td class="py-2.5 px-3">
                            <a href="{{ route('returns.show', $ret->id) }}" class="text-teal-600 hover:underline font-medium">View QC</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-4 text-center text-slate-400">No returns requested by this customer.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- TAB 7: SUPPORT TICKETS & COMPLAINTS -->
    <div x-show="activeTab === 'tickets'" class="card-elevated p-4">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-bold text-slate-800">Support Tickets & Complaints</h3>
            <a href="{{ route('support.create', ['customer_id' => $customer->id]) }}" class="btn-primary text-xs py-1.5 px-3">+ New Ticket</a>
        </div>
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="py-2.5 px-3">Ticket #</th>
                    <th class="py-2.5 px-3">Subject</th>
                    <th class="py-2.5 px-3">Category</th>
                    <th class="py-2.5 px-3">Priority</th>
                    <th class="py-2.5 px-3">Status</th>
                    <th class="py-2.5 px-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                @forelse($customer->tickets as $ticket)
                    <tr>
                        <td class="py-2.5 px-3 font-bold text-teal-600">
                            <a href="{{ route('support.show', $ticket->id) }}">{{ $ticket->ticket_number }}</a>
                        </td>
                        <td class="py-2.5 px-3 font-medium text-slate-800">{{ $ticket->subject }}</td>
                        <td class="py-2.5 px-3">{{ $ticket->category }}</td>
                        <td class="py-2.5 px-3">
                            <span class="px-2 py-0.5 text-[10px] font-semibold rounded {{ $ticket->priority === 'Urgent' ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-700' }}">
                                {{ $ticket->priority }}
                            </span>
                        </td>
                        <td class="py-2.5 px-3">
                            <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full {{ $ticket->status === 'Closed' ? 'bg-emerald-50 text-emerald-700' : 'bg-blue-50 text-blue-700' }}">
                                {{ $ticket->status }}
                            </span>
                        </td>
                        <td class="py-2.5 px-3 text-right">
                            <a href="{{ route('support.show', $ticket->id) }}" class="text-teal-600 hover:underline">View Ticket</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-4 text-center text-slate-400">No support tickets for this customer.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
