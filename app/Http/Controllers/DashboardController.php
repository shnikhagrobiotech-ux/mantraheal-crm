<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Customer;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\RtoRecord;
use App\Models\SalesCall;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // 1. Sales KPIs
        $todayOrdersQuery = Order::whereDate('order_date', $today);
        $todaySales = (clone $todayOrdersQuery)->whereIn('order_status', ['Delivered', 'Shipped', 'Confirmed', 'Processing', 'Packed'])->sum('grand_total');
        $todayOrders = (clone $todayOrdersQuery)->count();

        $monthOrdersQuery = Order::whereBetween('order_date', [$startOfMonth, $endOfMonth]);
        $monthlySales = (clone $monthOrdersQuery)->whereIn('order_status', ['Delivered', 'Shipped', 'Confirmed', 'Processing', 'Packed'])->sum('grand_total');
        $monthlyOrders = (clone $monthOrdersQuery)->count();

        $totalRevenue = Order::whereIn('order_status', ['Delivered', 'Shipped', 'Confirmed', 'Processing', 'Packed'])->sum('grand_total');
        $allOrdersCount = Order::count();
        $averageOrderValue = $allOrdersCount > 0 ? round($totalRevenue / $allOrdersCount, 2) : 0;

        $newCustomers = Customer::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
        $repeatCustomers = Customer::where('total_orders', '>', 1)->count();

        // 2. Order KPIs
        $orderKpis = [
            'pending' => Order::where('order_status', 'New')->count(),
            'confirmed' => Order::where('order_status', 'Confirmed')->count(),
            'processing' => Order::whereIn('order_status', ['Processing', 'Packed'])->count(),
            'shipped' => Order::whereIn('order_status', ['Shipped', 'Out for Delivery'])->count(),
            'delivered' => Order::where('order_status', 'Delivered')->count(),
            'cancelled' => Order::where('order_status', 'Cancelled')->count(),
            'returned' => Order::where('order_status', 'Returned')->count(),
            'rto' => Order::where('order_status', 'RTO')->count(),
        ];

        // 3. Sales Team KPIs
        $callsToday = SalesCall::whereDate('call_datetime', $today)->count();
        $connectedCallsToday = SalesCall::whereDate('call_datetime', $today)
            ->whereIn('outcome', ['Connected', 'Interested', 'Order Taken', 'Follow-up Required'])
            ->count();
        $followupsToday = FollowUp::today()->count();
        $overdueFollowups = FollowUp::overdue()->count();
        $totalLeads = Lead::count();
        $convertedLeads = Lead::where('stage', 'Won')->orWhereNotNull('converted_to_customer_id')->count();

        // 4. Inventory KPIs
        $totalProducts = Product::count();
        $products = Product::with('stockBalances')->get();
        $lowStockCount = 0;
        $outOfStockCount = 0;
        $inventoryValue = 0;

        foreach ($products as $p) {
            $stk = $p->total_stock;
            if ($stk <= 0) {
                $outOfStockCount++;
            } elseif ($stk <= $p->reorder_level) {
                $lowStockCount++;
            }
            $inventoryValue += ($stk * $p->purchase_price);
        }

        $expiringBatches = Batch::expiringIn60Days()->count();

        // 5. Chart Data: Last 14 Days Sales Trend
        $last14Days = collect(range(13, 0))->map(function ($daysAgo) {
            return Carbon::today()->subDays($daysAgo)->toDateString();
        });

        $dailyStats = Order::select(
            DB::raw('DATE(order_date) as day'),
            DB::raw('COUNT(*) as order_count'),
            DB::raw('SUM(grand_total) as revenue')
        )
            ->whereDate('order_date', '>=', Carbon::today()->subDays(14))
            ->groupBy(DB::raw('DATE(order_date)'))
            ->pluck('revenue', 'day');

        $dailyOrderCounts = Order::select(
            DB::raw('DATE(order_date) as day'),
            DB::raw('COUNT(*) as order_count')
        )
            ->whereDate('order_date', '>=', Carbon::today()->subDays(14))
            ->groupBy(DB::raw('DATE(order_date)'))
            ->pluck('order_count', 'day');

        $chartLabels = [];
        $chartSales = [];
        $chartOrders = [];

        foreach ($last14Days as $date) {
            $chartLabels[] = Carbon::parse($date)->format('d M');
            $chartSales[] = (float) ($dailyStats[$date] ?? 0);
            $chartOrders[] = (int) ($dailyOrderCounts[$date] ?? 0);
        }

        // Channel Breakdown
        $channelStats = Order::select('channel', DB::raw('COUNT(*) as count'), DB::raw('SUM(grand_total) as total'))
            ->groupBy('channel')
            ->get();

        // Lead Pipeline Distribution
        $pipelineStats = Lead::select('stage', DB::raw('COUNT(*) as count'))
            ->groupBy('stage')
            ->get();

        // Top 5 Products by Sales
        $topProducts = OrderItem::select('product_name', DB::raw('SUM(quantity) as qty'), DB::raw('SUM(total_price) as revenue'))
            ->groupBy('product_name')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();

        // Recent Orders
        $recentOrders = Order::with('customer')->latest('order_date')->limit(6)->get();

        // Recent Calls
        $recentCalls = SalesCall::with(['customer', 'user'])->latest('call_datetime')->limit(5)->get();

        return view('dashboard.index', compact(
            'todaySales',
            'todayOrders',
            'monthlySales',
            'monthlyOrders',
            'averageOrderValue',
            'newCustomers',
            'repeatCustomers',
            'orderKpis',
            'callsToday',
            'connectedCallsToday',
            'followupsToday',
            'overdueFollowups',
            'totalLeads',
            'convertedLeads',
            'totalProducts',
            'lowStockCount',
            'outOfStockCount',
            'expiringBatches',
            'inventoryValue',
            'chartLabels',
            'chartSales',
            'chartOrders',
            'channelStats',
            'pipelineStats',
            'topProducts',
            'recentOrders',
            'recentCalls'
        ));
    }
}
