@extends('layouts.app')

@section('title', 'Delivery & Logistics Dispatch Panel — MantraHeal CRM')

@section('content')
<div class="space-y-6" x-data="{
    activeTab: '{{ $currentTab }}',
    showBookModal: false,
    selectedOrder: null,
    courierName: 'Delhivery',
    weightGrams: 500,
    openBookModal(order) {
        this.selectedOrder = order;
        this.showBookModal = true;
    }
}">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xs text-slate-500 font-medium">Logistics & Fulfillment</span>
                <span class="text-slate-300">/</span>
                <span class="text-xs text-indigo-700 font-semibold">Courier Dispatch Panel</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2.5">
                <svg class="w-7 h-7 text-indigo-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                Delivery & Dispatch Logistics Panel
            </h1>
            <p class="text-sm text-slate-500">Manage order packaging, AWB generation, courier allocation, shipping labels, and real-time transit tracking</p>
        </div>

        <div class="flex items-center gap-3">
            <form action="{{ route('deliveries.sync-tracking') }}" method="POST">
                @csrf
                <button type="submit" class="btn bg-indigo-700 hover:bg-indigo-600 text-white font-bold text-xs px-4 py-2 rounded-lg shadow-sm flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Sync Courier Milestones
                </button>
            </form>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success') }}</span>
            </div>
            <button @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
        </div>
    @endif

    <!-- Pipeline KPI Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
        <button type="button" @click="activeTab = 'ready'" class="card p-4 text-left transition hover:border-indigo-400" :class="activeTab === 'ready' ? 'ring-2 ring-indigo-500 bg-indigo-50/40' : ''">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Ready to Ship</div>
            <div class="mt-1 text-2xl font-black text-amber-600">{{ $counts['ready'] }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Need courier booking</div>
        </button>

        <button type="button" @click="activeTab = 'in_transit'" class="card p-4 text-left transition hover:border-indigo-400" :class="activeTab === 'in_transit' ? 'ring-2 ring-indigo-500 bg-indigo-50/40' : ''">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">In Transit</div>
            <div class="mt-1 text-2xl font-black text-indigo-600">{{ $counts['in_transit'] }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Dispatched on road</div>
        </button>

        <button type="button" @click="activeTab = 'out_for_delivery'" class="card p-4 text-left transition hover:border-indigo-400" :class="activeTab === 'out_for_delivery' ? 'ring-2 ring-indigo-500 bg-indigo-50/40' : ''">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Out for Delivery</div>
            <div class="mt-1 text-2xl font-black text-teal-600">{{ $counts['out_for_delivery'] }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Last mile attempt</div>
        </button>

        <button type="button" @click="activeTab = 'delivered'" class="card p-4 text-left transition hover:border-indigo-400" :class="activeTab === 'delivered' ? 'ring-2 ring-indigo-500 bg-indigo-50/40' : ''">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Delivered</div>
            <div class="mt-1 text-2xl font-black text-emerald-600">{{ $counts['delivered'] }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Successful drops</div>
        </button>

        <button type="button" @click="activeTab = 'rto'" class="card p-4 text-left transition hover:border-indigo-400" :class="activeTab === 'rto' ? 'ring-2 ring-indigo-500 bg-indigo-50/40' : ''">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">NDR / RTO</div>
            <div class="mt-1 text-2xl font-black text-rose-600">{{ $counts['rto'] }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Failed or returning</div>
        </button>
    </div>

    <!-- Main Container Card -->
    <div class="card p-6 space-y-4">
        
        <!-- Navigation Tabs Bar -->
        <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-3">
            <button type="button" @click="activeTab = 'ready'" :class="activeTab === 'ready' ? 'bg-indigo-700 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-2">
                <span>📦 Ready for Dispatch</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="activeTab === 'ready' ? 'bg-indigo-900 text-white' : 'bg-slate-200 text-slate-700'">{{ $counts['ready'] }}</span>
            </button>

            <button type="button" @click="activeTab = 'in_transit'" :class="activeTab === 'in_transit' ? 'bg-indigo-700 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-2">
                <span>🚚 In Transit</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="activeTab === 'in_transit' ? 'bg-indigo-900 text-white' : 'bg-slate-200 text-slate-700'">{{ $counts['in_transit'] }}</span>
            </button>

            <button type="button" @click="activeTab = 'out_for_delivery'" :class="activeTab === 'out_for_delivery' ? 'bg-indigo-700 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-2">
                <span>⚡ Out for Delivery</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="activeTab === 'out_for_delivery' ? 'bg-indigo-900 text-white' : 'bg-slate-200 text-slate-700'">{{ $counts['out_for_delivery'] }}</span>
            </button>

            <button type="button" @click="activeTab = 'delivered'" :class="activeTab === 'delivered' ? 'bg-indigo-700 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-2">
                <span>✓ Delivered Orders</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="activeTab === 'delivered' ? 'bg-indigo-900 text-white' : 'bg-slate-200 text-slate-700'">{{ $counts['delivered'] }}</span>
            </button>

            <button type="button" @click="activeTab = 'rto'" :class="activeTab === 'rto' ? 'bg-indigo-700 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-2">
                <span>⚠️ NDR / RTO</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="activeTab === 'rto' ? 'bg-indigo-900 text-white' : 'bg-slate-200 text-slate-700'">{{ $counts['rto'] }}</span>
            </button>
        </div>

        <!-- TAB 1: Ready for Dispatch -->
        <div x-show="activeTab === 'ready'">
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-500 uppercase text-[10px] font-bold bg-slate-50">
                            <th class="py-3 px-3">Order Number</th>
                            <th class="py-3 px-3">Customer & Contact</th>
                            <th class="py-3 px-3">Destination Pincode</th>
                            <th class="py-3 px-3">Amount & Payment</th>
                            <th class="py-3 px-3">Order Date</th>
                            <th class="py-3 px-3 text-right">Dispatch Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($readyOrders as $order)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-3">
                                    <a href="{{ route('orders.show', $order) }}" class="font-mono font-bold text-teal-800 hover:underline">
                                        {{ $order->order_number }}
                                    </a>
                                    <div class="text-[10px] text-slate-400">{{ $order->channel }}</div>
                                </td>
                                <td class="py-3 px-3">
                                    <div class="font-semibold text-slate-900">{{ $order->customer?->name ?? $order->shipping_name }}</div>
                                    <div class="text-[10px] text-slate-500 font-mono">{{ $order->customer?->phone ?? $order->shipping_phone }}</div>
                                </td>
                                <td class="py-3 px-3">
                                    <div class="font-medium text-slate-800">{{ $order->shipping_city }}, {{ $order->shipping_state }}</div>
                                    <div class="text-[10px] font-mono text-slate-500 font-bold">PIN: {{ $order->shipping_pincode }}</div>
                                </td>
                                <td class="py-3 px-3">
                                    <div class="font-bold text-slate-900">₹{{ number_format($order->grand_total, 2) }}</div>
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase {{ $order->payment_method === 'COD' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                                        {{ $order->payment_method }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 font-mono text-slate-500">
                                    {{ $order->order_date->format('d M Y') }}
                                </td>
                                <td class="py-3 px-3 text-right">
                                    <button type="button" 
                                            @click="openBookModal({ id: {{ $order->id }}, number: '{{ $order->order_number }}', customer: '{{ addslashes($order->customer?->name ?? $order->shipping_name) }}', destination: '{{ addslashes($order->shipping_city) }}, {{ $order->shipping_pincode }}', amount: '{{ $order->grand_total }}' })"
                                            class="btn bg-indigo-700 hover:bg-indigo-600 text-white font-bold text-xs px-3.5 py-1.5 rounded-lg shadow-sm cursor-pointer">
                                        Book Courier
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400">
                                    No pending orders ready for dispatch. All confirmed orders have been assigned couriers!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($readyOrders->hasPages())
                <div class="p-3 border-t border-slate-100">
                    {{ $readyOrders->links() }}
                </div>
            @endif
        </div>

        <!-- TAB 2: In Transit Shipments -->
        <div x-show="activeTab === 'in_transit'">
            @include('deliveries._shipment_table', ['shipments' => $inTransitShipments, 'emptyText' => 'No active shipments currently in transit.'])
        </div>

        <!-- TAB 3: Out for Delivery -->
        <div x-show="activeTab === 'out_for_delivery'">
            @include('deliveries._shipment_table', ['shipments' => $outForDeliveryShipments, 'emptyText' => 'No shipments currently out for delivery today.'])
        </div>

        <!-- TAB 4: Delivered -->
        <div x-show="activeTab === 'delivered'">
            @include('deliveries._shipment_table', ['shipments' => $deliveredShipments, 'emptyText' => 'No delivered shipments logged yet.'])
        </div>

        <!-- TAB 5: NDR / RTO -->
        <div x-show="activeTab === 'rto'">
            @include('deliveries._shipment_table', ['shipments' => $rtoShipments, 'emptyText' => 'Zero RTO or delivery exception records found. Excellent fulfillment rate!'])
        </div>

    </div>

    <!-- Booking Courier Modal Dialog -->
    <div x-show="showBookModal" 
         x-transition.opacity 
         class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-xs flex items-center justify-center p-4" 
         style="display: none;">
        
        <div @click.away="showBookModal = false" class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-md w-full p-6 space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm">AWB</span>
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">Book Logistics Shipment</h3>
                        <p class="text-xs text-slate-500" x-text="`Order: ${selectedOrder?.number}`"></p>
                    </div>
                </div>
                <button type="button" @click="showBookModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <!-- Order Summary Preview -->
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 text-xs space-y-1">
                <div class="flex justify-between">
                    <span class="text-slate-500">Customer:</span>
                    <span class="font-bold text-slate-800" x-text="selectedOrder?.customer"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Destination:</span>
                    <span class="font-bold text-slate-800" x-text="selectedOrder?.destination"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Invoice Amount:</span>
                    <span class="font-bold text-teal-800" x-text="`₹${selectedOrder?.amount}`"></span>
                </div>
            </div>

            <!-- Booking Form -->
            <form action="{{ route('deliveries.book-courier') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <input type="hidden" name="order_id" :value="selectedOrder?.id">

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Select Courier Partner *</label>
                    <select name="courier_name" x-model="courierName" required class="form-control w-full text-xs font-bold">
                        @foreach($couriers as $cr)
                            <option value="{{ $cr }}">{{ $cr }} Express</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Package Weight (Grams)</label>
                    <input type="number" name="weight_grams" x-model="weightGrams" min="100" step="50" class="form-control w-full text-xs font-mono">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Special Handling Instructions / Notes</label>
                    <input type="text" name="notes" placeholder="Fragile Ayurvedic Glass Bottle, Handle with care" class="form-control w-full text-xs">
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="showBookModal = false" class="btn btn-secondary text-xs px-4 py-2">Cancel</button>
                    <button type="submit" class="btn bg-indigo-700 hover:bg-indigo-600 text-white text-xs font-bold px-5 py-2 rounded-lg shadow cursor-pointer">
                        Confirm & Generate AWB
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
