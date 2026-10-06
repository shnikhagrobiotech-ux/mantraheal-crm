<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\Warehouse;
use App\Services\ReturnRefundService;
use Illuminate\Http\Request;

class ReturnController extends Controller
{
    public function __construct(
        protected ReturnRefundService $returnRefundService
    ) {}

    public function index(Request $request)
    {
        $query = OrderReturn::with(['order', 'customer', 'items.product', 'processor']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($qcStatus = $request->input('qc_status')) {
            $query->where('qc_status', $qcStatus);
        }

        $returns = $query->latest('return_date')->paginate(15)->withQueryString();
        $statuses = [
            'Return Requested',
            'Approved',
            'Pickup',
            'Received',
            'QC',
            'Refund / Replacement',
            'Rejected'
        ];

        return view('returns.index', compact('returns', 'statuses'));
    }

    public function create(Request $request)
    {
        $orderId = $request->input('order_id');
        $order = Order::with(['customer', 'items.product'])->findOrFail($orderId);
        $warehouses = Warehouse::where('is_active', true)->get();
        $reasons = config('mantraheal.return_reasons');

        return view('returns.create', compact('order', 'warehouses', 'reasons'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'return_date' => 'required|date',
            'reason' => 'required|string',
            'refund_action' => 'required|in:Refund,Replacement,Store Credit',
            'restocking_warehouse_id' => 'nullable|exists:warehouses,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.order_item_id' => 'required|exists:order_items,id',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.condition_notes' => 'nullable|string',
        ]);

        $order = Order::findOrFail($validated['order_id']);
        $return = $this->returnRefundService->createReturn($order, $validated, $validated['items']);

        return redirect()->route('returns.show', $return->id)->with('success', "Return request #{$return->return_number} created.");
    }

    public function show(OrderReturn $return)
    {
        $return->load(['order.items', 'customer', 'items.product', 'items.orderItem', 'refund', 'warehouse', 'processor']);
        $warehouses = Warehouse::all();
        return view('returns.show', compact('return', 'warehouses'));
    }

    public function updateQc(Request $request, OrderReturn $return)
    {
        $validated = $request->validate([
            'qc_status' => 'required|in:Passed,Failed',
            'restocking_warehouse_id' => 'nullable|exists:warehouses,id',
            'notes' => 'nullable|string',
        ]);

        $this->returnRefundService->processQc(
            $return,
            $validated['qc_status'],
            $validated['restocking_warehouse_id'] ?? null,
            $validated['notes'] ?? null
        );

        return back()->with('success', "QC status updated to {$validated['qc_status']}. Stock updated if approved.");
    }
}
