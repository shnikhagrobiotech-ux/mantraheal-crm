<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\Customer;
use App\Models\FollowUp;
use App\Models\Order;
use App\Models\Product;
use App\Models\RtoRecord;
use App\Models\SalesCall;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportService
{
    public function getSalesReports(?string $startDate = null, ?string $endDate = null): array
    {
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::now()->endOfDay();

        $orders = Order::whereBetween('order_date', [$start, $end])->get();

        $totalSales = $orders->whereIn('order_status', ['Delivered', 'Shipped', 'Confirmed', 'Processing'])->sum('grand_total');
        $totalOrders = $orders->count();
        $deliveredOrders = $orders->where('order_status', 'Delivered')->count();
        $aov = $totalOrders > 0 ? round($totalSales / $totalOrders, 2) : 0;

        // Channel wise
        $byChannel = $orders->groupBy('channel')->map(function ($group) {
            return [
                'count' => $group->count(),
                'revenue' => $group->sum('grand_total'),
            ];
        });

        // State wise
        $byState = $orders->groupBy('shipping_state')->map(function ($group) {
            return [
                'count' => $group->count(),
                'revenue' => $group->sum('grand_total'),
            ];
        });

        // Daily trend
        $daily = Order::select(
            DB::raw('DATE(order_date) as date'),
            DB::raw('COUNT(*) as total_orders'),
            DB::raw('SUM(grand_total) as total_revenue')
        )
            ->whereBetween('order_date', [$start, $end])
            ->groupBy(DB::raw('DATE(order_date)'))
            ->orderBy('date', 'asc')
            ->get();

        return [
            'total_sales' => $totalSales,
            'total_orders' => $totalOrders,
            'delivered_orders' => $deliveredOrders,
            'aov' => $aov,
            'by_channel' => $byChannel,
            'by_state' => $byState,
            'daily_trend' => $daily,
        ];
    }

    public function getCustomerReports(): array
    {
        $totalCustomers = Customer::count();
        $repeatCustomers = Customer::where('total_orders', '>', 1)->count();
        $newCustomers = Customer::where('total_orders', '<=', 1)->count();
        $totalSpend = Customer::sum('total_spend');
        $overallAov = $totalCustomers > 0 ? round($totalSpend / max(1, Customer::sum('total_orders')), 2) : 0;
        $repeatRate = $totalCustomers > 0 ? round(($repeatCustomers / $totalCustomers) * 100, 1) : 0;

        $topCustomers = Customer::orderByDesc('total_spend')->limit(10)->get();

        return [
            'total_customers' => $totalCustomers,
            'repeat_customers' => $repeatCustomers,
            'new_customers' => $newCustomers,
            'total_spend' => $totalSpend,
            'overall_aov' => $overallAov,
            'repeat_rate' => $repeatRate,
            'top_customers' => $topCustomers,
        ];
    }

    public function getCallReports(): array
    {
        $totalCalls = SalesCall::count();
        $connectedCalls = SalesCall::whereIn('outcome', ['Connected', 'Interested', 'Order Taken', 'Follow-up Required'])->count();
        $totalDuration = SalesCall::sum('duration_seconds');
        $avgDuration = $totalCalls > 0 ? round($totalDuration / $totalCalls) : 0;

        $byOutcome = SalesCall::select('outcome', DB::raw('COUNT(*) as count'))
            ->groupBy('outcome')
            ->get();

        $byEmployee = User::where('role_slug', 'sales_executive')
            ->withCount(['salesCalls', 'leads', 'orders'])
            ->get();

        return [
            'total_calls' => $totalCalls,
            'connected_calls' => $connectedCalls,
            'total_duration' => $totalDuration,
            'avg_duration' => sprintf('%02d:%02d', floor($avgDuration / 60), $avgDuration % 60),
            'by_outcome' => $byOutcome,
            'by_employee' => $byEmployee,
        ];
    }

    public function getInventoryReports(): array
    {
        $products = Product::with('stockBalances')->get();
        $totalProducts = $products->count();
        $lowStockCount = 0;
        $outOfStockCount = 0;
        $totalValuation = 0;

        foreach ($products as $p) {
            $stock = $p->total_stock;
            if ($stock <= 0) {
                $outOfStockCount++;
            } elseif ($stock <= $p->reorder_level) {
                $lowStockCount++;
            }
            $totalValuation += ($stock * $p->purchase_price);
        }

        $expiringBatches = Batch::expiringIn60Days()->with('product')->get();
        $expiredBatches = Batch::expired()->with('product')->get();

        return [
            'total_products' => $totalProducts,
            'low_stock_count' => $lowStockCount,
            'out_of_stock_count' => $outOfStockCount,
            'total_valuation' => $totalValuation,
            'expiring_batches' => $expiringBatches,
            'expired_batches' => $expiredBatches,
        ];
    }

    public function getRtoReports(): array
    {
        $totalRtos = RtoRecord::count();
        $totalRtoAmount = RtoRecord::sum('total_amount');
        $totalShipped = Order::whereIn('order_status', ['Shipped', 'Out for Delivery', 'Delivered', 'RTO', 'Returned'])->count();
        $rtoRate = $totalShipped > 0 ? round(($totalRtos / $totalShipped) * 100, 2) : 0;

        $byCourier = RtoRecord::select('courier_name', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as amount'))
            ->groupBy('courier_name')
            ->get();

        $byState = RtoRecord::select('state', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as amount'))
            ->groupBy('state')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        $byReason = RtoRecord::select('reason', DB::raw('COUNT(*) as count'))
            ->groupBy('reason')
            ->get();

        return [
            'total_rtos' => $totalRtos,
            'total_rto_amount' => $totalRtoAmount,
            'rto_rate' => $rtoRate,
            'by_courier' => $byCourier,
            'by_state' => $byState,
            'by_reason' => $byReason,
        ];
    }

    public function exportCsv(array $headers, array $rows, string $filename = 'export.csv'): StreamedResponse
    {
        $callback = function () use ($headers, $rows) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            foreach ($rows as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ]);
    }
}
