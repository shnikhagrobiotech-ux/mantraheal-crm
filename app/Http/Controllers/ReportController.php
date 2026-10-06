<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function sales(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $reports = $this->reportService->getSalesReports($startDate, $endDate);

        if ($request->input('export') === 'csv') {
            $headers = ['Date', 'Orders', 'Revenue (INR)'];
            $rows = [];
            foreach ($reports['daily_trend'] as $item) {
                $rows[] = [$item->date, $item->total_orders, $item->total_revenue];
            }
            return $this->reportService->exportCsv($headers, $rows, 'mantraheal_sales_report_' . date('Y-m-d') . '.csv');
        }

        return view('reports.sales', compact('reports', 'startDate', 'endDate'));
    }

    public function customers(Request $request)
    {
        $reports = $this->reportService->getCustomerReports();

        if ($request->input('export') === 'csv') {
            $headers = ['Customer Name', 'Mobile', 'Orders', 'Total Spend (INR)', 'AOV'];
            $rows = [];
            foreach ($reports['top_customers'] as $c) {
                $rows[] = [$c->name, $c->mobile, $c->total_orders, $c->total_spend, $c->average_order_value];
            }
            return $this->reportService->exportCsv($headers, $rows, 'mantraheal_customer_report_' . date('Y-m-d') . '.csv');
        }

        return view('reports.customers', compact('reports'));
    }

    public function calls(Request $request)
    {
        $reports = $this->reportService->getCallReports();
        return view('reports.calls', compact('reports'));
    }

    public function inventory(Request $request)
    {
        $reports = $this->reportService->getInventoryReports();
        return view('reports.inventory', compact('reports'));
    }

    public function rto(Request $request)
    {
        $reports = $this->reportService->getRtoReports();

        if ($request->input('export') === 'csv') {
            $headers = ['Courier', 'RTO Count', 'Amount (INR)'];
            $rows = [];
            foreach ($reports['by_courier'] as $c) {
                $rows[] = [$c->courier_name, $c->count, $c->total];
            }
            return $this->reportService->exportCsv($headers, $rows, 'mantraheal_rto_report_' . date('Y-m-d') . '.csv');
        }

        return view('reports.rto', compact('reports'));
    }
}
