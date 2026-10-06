<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\PurchaseService;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    public function __construct(
        protected PurchaseService $purchaseService
    ) {}

    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'warehouse', 'items.product', 'creator']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($supplierId = $request->input('supplier_id')) {
            $query->where('supplier_id', $supplierId);
        }

        $orders = $query->latest('po_date')->paginate(15)->withQueryString();
        $suppliers = Supplier::all();
        $warehouses = Warehouse::all();

        return view('purchase.orders', compact('orders', 'suppliers', 'warehouses'));
    }

    public function create()
    {
        $suppliers = Supplier::where('status', 'active')->get();
        $warehouses = Warehouse::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();

        return view('purchase.create-po', compact('suppliers', 'warehouses', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'po_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.rate' => 'required|numeric|min:0',
            'items.*.tax_percent' => 'nullable|numeric|min:0',
        ]);

        $po = $this->purchaseService->createPurchaseOrder($validated, $validated['items']);

        return redirect()->route('purchase-orders.show', $po->id)
            ->with('success', "Purchase Order #{$po->po_number} created successfully.");
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'warehouse', 'items.product', 'goodsReceivedNotes.items', 'creator']);
        return view('purchase.show-po', compact('purchaseOrder'));
    }
}
