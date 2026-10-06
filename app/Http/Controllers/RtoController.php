<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Order;
use App\Models\RtoRecord;
use App\Models\Warehouse;
use App\Services\ReportService;
use App\Services\ReturnRefundService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RtoController extends Controller
{
    public function __construct(
        protected ReturnRefundService $returnRefundService,
        protected ReportService $reportService
    ) {}

    public function index(Request $request)
    {
        $query = RtoRecord::with(['order.items.product', 'customer', 'warehouse']);

        if ($courier = $request->input('courier_name')) {
            $query->where('courier_name', $courier);
        }

        if ($reason = $request->input('reason')) {
            $query->where('reason', $reason);
        }

        if ($state = $request->input('state')) {
            $query->where('state', $state);
        }

        $rtos = $query->latest('rto_initiated_date')->paginate(15)->withQueryString();
        $couriers = config('mantraheal.couriers');
        $reasons = config('mantraheal.rto_reasons');
        $warehouses = Warehouse::where('is_active', true)->get();

        // High level statistics
        $totalRtos = RtoRecord::count();
        $totalRtoAmount = RtoRecord::sum('total_amount');
        $courierStats = RtoRecord::select('courier_name', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('courier_name')
            ->get();

        return view('rto.index', compact('rtos', 'couriers', 'reasons', 'warehouses', 'totalRtos', 'totalRtoAmount', 'courierStats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'courier_name' => 'required|string',
            'tracking_number' => 'required|string',
            'rto_initiated_date' => 'required|date',
            'reason' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $order = Order::findOrFail($validated['order_id']);
        $rto = $this->returnRefundService->processRto($order, $validated);

        return back()->with('success', "RTO #{$rto->rto_code} logged for Order #{$order->order_number}.");
    }

    public function update(Request $request, RtoRecord $rto)
    {
        $validated = $request->validate([
            'status' => 'required|in:In Transit,Received at Warehouse,QC Completed',
            'received_warehouse_id' => 'nullable|exists:warehouses,id',
            'rto_delivered_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $rto->update($validated);
        AuditLog::log('rto_updated', $rto, "RTO #{$rto->rto_code} status updated to {$rto->status}");

        return back()->with('success', 'RTO record updated.');
    }
}
