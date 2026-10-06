<div class="overflow-x-auto">
    <table class="w-full text-xs text-left">
        <thead>
            <tr class="border-b border-slate-200 text-slate-500 uppercase text-[10px] font-bold bg-slate-50">
                <th class="py-3 px-3">Courier & AWB Code</th>
                <th class="py-3 px-3">Order #</th>
                <th class="py-3 px-3">Destination & Recipient</th>
                <th class="py-3 px-3">Shipped Date</th>
                <th class="py-3 px-3">Expected Delivery</th>
                <th class="py-3 px-3">Milestone Status</th>
                <th class="py-3 px-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($shipments as $s)
                <tr class="hover:bg-slate-50/80 transition">
                    <td class="py-3 px-3">
                        <div class="font-bold text-slate-900 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                            {{ $s->courier_name }}
                        </div>
                        <div class="font-mono text-xs text-indigo-700 font-bold mt-0.5">{{ $s->tracking_number }}</div>
                    </td>
                    <td class="py-3 px-3">
                        <a href="{{ route('orders.show', $s->order) }}" class="font-mono font-bold text-teal-800 hover:underline">
                            {{ $s->order?->order_number }}
                        </a>
                        <div class="text-[10px] text-slate-500">₹{{ number_format($s->order?->grand_total, 2) }}</div>
                    </td>
                    <td class="py-3 px-3">
                        <div class="font-semibold text-slate-900">{{ $s->order?->shipping_name ?? $s->order?->customer?->name }}</div>
                        <div class="text-[10px] text-slate-500">{{ $s->order?->shipping_city }}, {{ $s->order?->shipping_state }} - {{ $s->order?->shipping_pincode }}</div>
                    </td>
                    <td class="py-3 px-3 font-mono text-slate-500">
                        {{ $s->shipped_date ? $s->shipped_date->format('d M Y') : 'Pending' }}
                    </td>
                    <td class="py-3 px-3 font-mono text-slate-500">
                        {{ $s->expected_delivery_date ? $s->expected_delivery_date->format('d M Y') : 'In 3 Days' }}
                    </td>
                    <td class="py-3 px-3">
                        @php
                            $badgeClass = match($s->status) {
                                'Delivered' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                'Out for Delivery' => 'bg-teal-100 text-teal-800 border-teal-200',
                                'In Transit', 'Shipped' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                                'RTO', 'NDR', 'Returned' => 'bg-rose-100 text-rose-800 border-rose-200',
                                default => 'bg-slate-100 text-slate-800 border-slate-200'
                            };
                        @endphp
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $badgeClass }}">
                            {{ $s->status }}
                        </span>
                    </td>
                    <td class="py-3 px-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('deliveries.shipping-label', $s) }}" target="_blank" class="btn btn-secondary text-[11px] px-2.5 py-1 inline-flex items-center gap-1 shadow-xs" title="Print Shipping Label">
                                <svg class="w-3.5 h-3.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                <span>Label</span>
                            </a>

                            <!-- Status Dropdown Form -->
                            <form action="{{ route('deliveries.update-status', $s) }}" method="POST" class="inline">
                                @csrf
                                <select name="status" onchange="this.form.submit()" class="text-[10px] bg-slate-100 border border-slate-300 rounded p-1 font-bold text-slate-700 cursor-pointer">
                                    <option value="" disabled selected>Update...</option>
                                    <option value="In Transit">In Transit</option>
                                    <option value="Out for Delivery">Out for Delivery</option>
                                    <option value="Delivered">Delivered</option>
                                    <option value="RTO">RTO / Return</option>
                                </select>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="py-12 text-center text-slate-400">
                        {{ $emptyText ?? 'No shipments recorded in this category.' }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($shipments->hasPages())
    <div class="p-3 border-t border-slate-100">
        {{ $shipments->links() }}
    </div>
@endif
