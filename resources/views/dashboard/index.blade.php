@extends('layouts.app')

@section('title', 'Executive Dashboard')
@section('subtitle', 'Real-time sales, order lifecycle, sales team productivity & multi-warehouse inventory')

@section('content')
<div class="space-y-6">

    <!-- 1. SALES KPIS -->
    <div>
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center">
                <span class="w-2.5 h-2.5 rounded-full bg-teal-500 mr-2"></span>
                Sales & Revenue Performance
            </h2>
            <span class="text-xs text-slate-400">Values in INR (₹)</span>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Today's Sales -->
            <div class="card-elevated p-4 border-l-4 border-teal-500">
                <div class="text-xs font-medium text-slate-500">Today's Sales</div>
                <div class="text-2xl font-bold text-slate-900 mt-1">₹{{ number_format($todaySales, 2) }}</div>
                <div class="text-xs text-teal-600 font-medium mt-1 flex items-center">
                    <span>{{ $todayOrders }} orders placed today</span>
                </div>
            </div>

            <!-- Monthly Sales -->
            <div class="card-elevated p-4 border-l-4 border-blue-500">
                <div class="text-xs font-medium text-slate-500">Monthly Sales ({{ now()->format('M Y') }})</div>
                <div class="text-2xl font-bold text-slate-900 mt-1">₹{{ number_format($monthlySales, 2) }}</div>
                <div class="text-xs text-blue-600 font-medium mt-1">
                    {{ $monthlyOrders }} orders this month
                </div>
            </div>

            <!-- Average Order Value -->
            <div class="card-elevated p-4 border-l-4 border-indigo-500">
                <div class="text-xs font-medium text-slate-500">Average Order Value (AOV)</div>
                <div class="text-2xl font-bold text-slate-900 mt-1">₹{{ number_format($averageOrderValue, 2) }}</div>
                <div class="text-xs text-slate-500 mt-1">Per transaction average</div>
            </div>

            <!-- New vs Repeat Customers -->
            <div class="card-elevated p-4 border-l-4 border-emerald-500">
                <div class="text-xs font-medium text-slate-500">Customer Dynamics</div>
                <div class="flex items-baseline space-x-2 mt-1">
                    <span class="text-2xl font-bold text-slate-900">{{ $repeatCustomers }}</span>
                    <span class="text-xs text-emerald-600 font-semibold">Repeat ({{ $newCustomers }} new)</span>
                </div>
                <div class="text-xs text-slate-400 mt-1">High retention wellness base</div>
            </div>
        </div>
    </div>

    <!-- 2. ORDER LIFECYCLE PIPELINE KPIS -->
    <div>
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center">
                <span class="w-2.5 h-2.5 rounded-full bg-blue-500 mr-2"></span>
                Order Status Pipeline
            </h2>
            <a href="{{ route('orders.index') }}" class="text-xs text-teal-600 hover:underline">View All Orders &rarr;</a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3">
            <a href="{{ route('orders.index', ['status' => 'New']) }}" class="card-elevated p-3 text-center hover:border-sky-400 transition">
                <div class="text-xl font-bold text-sky-600">{{ $orderKpis['pending'] }}</div>
                <div class="text-xs font-medium text-slate-600 mt-1">New / Pending</div>
            </a>
            <a href="{{ route('orders.index', ['status' => 'Confirmed']) }}" class="card-elevated p-3 text-center hover:border-emerald-400 transition">
                <div class="text-xl font-bold text-emerald-600">{{ $orderKpis['confirmed'] }}</div>
                <div class="text-xs font-medium text-slate-600 mt-1">Confirmed</div>
            </a>
            <a href="{{ route('orders.index', ['status' => 'Processing']) }}" class="card-elevated p-3 text-center hover:border-amber-400 transition">
                <div class="text-xl font-bold text-amber-600">{{ $orderKpis['processing'] }}</div>
                <div class="text-xs font-medium text-slate-600 mt-1">Processing</div>
            </a>
            <a href="{{ route('orders.index', ['status' => 'Shipped']) }}" class="card-elevated p-3 text-center hover:border-indigo-400 transition">
                <div class="text-xl font-bold text-indigo-600">{{ $orderKpis['shipped'] }}</div>
                <div class="text-xs font-medium text-slate-600 mt-1">Shipped</div>
            </a>
            <a href="{{ route('orders.index', ['status' => 'Delivered']) }}" class="card-elevated p-3 text-center hover:border-teal-400 transition">
                <div class="text-xl font-bold text-teal-600">{{ $orderKpis['delivered'] }}</div>
                <div class="text-xs font-medium text-slate-600 mt-1">Delivered</div>
            </a>
            <a href="{{ route('orders.index', ['status' => 'Cancelled']) }}" class="card-elevated p-3 text-center hover:border-rose-400 transition">
                <div class="text-xl font-bold text-rose-600">{{ $orderKpis['cancelled'] }}</div>
                <div class="text-xs font-medium text-slate-600 mt-1">Cancelled</div>
            </a>
            <a href="{{ route('returns.index') }}" class="card-elevated p-3 text-center hover:border-orange-400 transition">
                <div class="text-xl font-bold text-orange-600">{{ $orderKpis['returned'] }}</div>
                <div class="text-xs font-medium text-slate-600 mt-1">Returned</div>
            </a>
            <a href="{{ route('rto.index') }}" class="card-elevated p-3 text-center hover:border-pink-400 transition">
                <div class="text-xl font-bold text-pink-600">{{ $orderKpis['rto'] }}</div>
                <div class="text-xs font-medium text-slate-600 mt-1">RTO</div>
            </a>
        </div>
    </div>

    <!-- 3. SALES TEAM & INVENTORY SUMMARY CARDS -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Sales Team Productivity -->
        <div class="card-elevated p-5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <h3 class="text-sm font-bold text-slate-800 flex items-center">
                    <svg class="w-4 h-4 mr-2 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                    Sales Team Operations
                </h3>
                <a href="{{ route('calls.index') }}" class="text-xs text-teal-600 hover:underline">View Calls &rarr;</a>
            </div>
            <div class="grid grid-cols-3 gap-4 text-center">
                <div class="p-3 bg-slate-50 rounded-lg">
                    <div class="text-xl font-bold text-slate-900">{{ $callsToday }}</div>
                    <div class="text-xs text-slate-500 mt-0.5">Calls Today</div>
                    <div class="text-xs text-teal-600 font-semibold mt-1">{{ $connectedCallsToday }} Connected</div>
                </div>
                <div class="p-3 bg-slate-50 rounded-lg">
                    <div class="text-xl font-bold text-slate-900">{{ $followupsToday }}</div>
                    <div class="text-xs text-slate-500 mt-0.5">Follow-ups Today</div>
                    <div class="text-xs {{ $overdueFollowups > 0 ? 'text-rose-600 font-bold' : 'text-slate-400' }} mt-1">
                        {{ $overdueFollowups }} Overdue
                    </div>
                </div>
                <div class="p-3 bg-slate-50 rounded-lg">
                    <div class="text-xl font-bold text-slate-900">{{ $totalLeads }}</div>
                    <div class="text-xs text-slate-500 mt-0.5">Active Leads</div>
                    <div class="text-xs text-emerald-600 font-semibold mt-1">{{ $convertedLeads }} Won / Converted</div>
                </div>
            </div>
        </div>

        <!-- Inventory & Batches -->
        <div class="card-elevated p-5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <h3 class="text-sm font-bold text-slate-800 flex items-center">
                    <svg class="w-4 h-4 mr-2 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    Multi-Warehouse Inventory
                </h3>
                <a href="{{ route('inventory.index') }}" class="text-xs text-teal-600 hover:underline">Stock Ledger &rarr;</a>
            </div>
            <div class="grid grid-cols-4 gap-3 text-center">
                <div class="p-3 bg-slate-50 rounded-lg">
                    <div class="text-lg font-bold text-slate-900">{{ $totalProducts }}</div>
                    <div class="text-xs text-slate-500 mt-0.5">SKUs</div>
                </div>
                <div class="p-3 bg-slate-50 rounded-lg">
                    <div class="text-lg font-bold {{ $lowStockCount > 0 ? 'text-amber-600' : 'text-slate-900' }}">{{ $lowStockCount }}</div>
                    <div class="text-xs text-slate-500 mt-0.5">Low Stock</div>
                </div>
                <div class="p-3 bg-slate-50 rounded-lg">
                    <div class="text-lg font-bold {{ $outOfStockCount > 0 ? 'text-rose-600' : 'text-slate-900' }}">{{ $outOfStockCount }}</div>
                    <div class="text-xs text-slate-500 mt-0.5">Out of Stock</div>
                </div>
                <div class="p-3 bg-slate-50 rounded-lg">
                    <div class="text-lg font-bold {{ $expiringBatches > 0 ? 'text-orange-600' : 'text-slate-900' }}">{{ $expiringBatches }}</div>
                    <div class="text-xs text-slate-500 mt-0.5">Expiring 60d</div>
                </div>
            </div>
            <div class="mt-3 text-right text-xs text-slate-500">
                Total Stock Valuation: <strong class="text-slate-800 font-semibold">₹{{ number_format($inventoryValue, 2) }}</strong>
            </div>
        </div>
    </div>

    <!-- 4. CHARTS SECTION -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Sales & Orders Trend (2 cols) -->
        <div class="card-elevated p-5 lg:col-span-2">
            <h3 class="text-sm font-bold text-slate-800 mb-4">Sales & Orders Trend (Last 14 Days)</h3>
            <div class="h-64 relative">
                <canvas id="salesTrendChart"></canvas>
            </div>
        </div>

        <!-- Sales by Channel (1 col) -->
        <div class="card-elevated p-5">
            <h3 class="text-sm font-bold text-slate-800 mb-4">Orders by Channel</h3>
            <div class="h-64 relative">
                <canvas id="channelChart"></canvas>
            </div>
        </div>
    </div>

    <!-- 5. RECENT ORDERS & CALLS TABLES -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Orders -->
        <div class="card-elevated p-5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-3">
                <h3 class="text-sm font-bold text-slate-800">Recent Orders</h3>
                <a href="{{ route('orders.index') }}" class="text-xs text-teal-600 hover:underline">View All &rarr;</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 border-b border-slate-100">
                            <th class="py-2">Order #</th>
                            <th class="py-2">Customer</th>
                            <th class="py-2">Amount</th>
                            <th class="py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($recentOrders as $order)
                            <tr class="hover:bg-slate-50">
                                <td class="py-2.5 font-semibold text-teal-600">
                                    <a href="{{ route('orders.show', $order->id) }}">{{ $order->order_number }}</a>
                                </td>
                                <td class="py-2.5">
                                    <div class="font-medium text-slate-800">{{ $order->customer?->name ?? 'Guest' }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $order->channel }}</div>
                                </td>
                                <td class="py-2.5 font-medium">₹{{ number_format($order->grand_total, 2) }}</td>
                                <td class="py-2.5">
                                    <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full {{ $order->status_badge_class }}">
                                        {{ $order->order_status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-4 text-center text-slate-400">No orders recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Sales Calls -->
        <div class="card-elevated p-5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-3">
                <h3 class="text-sm font-bold text-slate-800">Recent Sales Calls</h3>
                <a href="{{ route('calls.index') }}" class="text-xs text-teal-600 hover:underline">View All &rarr;</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 border-b border-slate-100">
                            <th class="py-2">Contact</th>
                            <th class="py-2">Executive</th>
                            <th class="py-2">Outcome</th>
                            <th class="py-2">Duration</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($recentCalls as $call)
                            <tr class="hover:bg-slate-50">
                                <td class="py-2.5">
                                    <div class="font-medium text-slate-800">{{ $call->customer?->name ?? $call->lead?->name ?? 'Customer' }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $call->call_datetime->format('d M H:i') }}</div>
                                </td>
                                <td class="py-2.5 text-slate-600">{{ $call->user?->name ?? 'Staff' }}</td>
                                <td class="py-2.5">
                                    <span class="px-2 py-0.5 text-[10px] font-medium rounded bg-teal-50 text-teal-700 border border-teal-200">
                                        {{ $call->outcome }}
                                    </span>
                                </td>
                                <td class="py-2.5 text-slate-500">{{ $call->duration_formatted }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-4 text-center text-slate-400">No sales calls logged yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Sales Trend Chart
    const trendCtx = document.getElementById('salesTrendChart')?.getContext('2d');
    if (trendCtx) {
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartLabels) !!},
                datasets: [
                    {
                        label: 'Revenue (₹)',
                        data: {!! json_encode($chartSales) !!},
                        borderColor: '#0d9488',
                        backgroundColor: 'rgba(13, 148, 136, 0.1)',
                        fill: true,
                        tension: 0.3,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Orders Count',
                        data: {!! json_encode($chartOrders) !!},
                        borderColor: '#3b82f6',
                        backgroundColor: '#3b82f6',
                        tension: 0.3,
                        borderDash: [5, 5],
                        yAxisID: 'y1',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        ticks: { callback: val => '₹' + val }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        ticks: { precision: 0 }
                    }
                }
            }
        });
    }

    // 2. Channel Distribution Doughnut Chart
    const channelCtx = document.getElementById('channelChart')?.getContext('2d');
    if (channelCtx) {
        const channelLabels = {!! json_encode($channelStats->pluck('channel')) !!};
        const channelCounts = {!! json_encode($channelStats->pluck('count')) !!};

        new Chart(channelCtx, {
            type: 'doughnut',
            data: {
                labels: channelLabels.length ? channelLabels : ['Website', 'Shopify', 'Meta Ads', 'WhatsApp'],
                datasets: [{
                    data: channelCounts.length ? channelCounts : [15, 8, 12, 5],
                    backgroundColor: ['#0d9488', '#0284c7', '#8b5cf6', '#10b981', '#f59e0b', '#ef4444'],
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }
});
</script>
@endsection
