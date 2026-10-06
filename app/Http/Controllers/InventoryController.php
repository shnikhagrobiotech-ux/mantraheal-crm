<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Batch;
use App\Models\Product;
use App\Models\StockBalance;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Services\InventoryService;
use App\Services\ReportService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected ReportService $reportService
    ) {}

    public function index(Request $request)
    {
        $warehouseId = $request->input('warehouse_id');
        $query = Product::with(['category', 'stockBalances.warehouse', 'batches']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $products = $query->paginate(15)->withQueryString();
        $warehouses = Warehouse::where('is_active', true)->get();

        return view('inventory.index', compact('products', 'warehouses', 'warehouseId'));
    }

    public function ledger(Request $request)
    {
        $query = StockMovement::with(['product', 'warehouse', 'batch', 'user']);

        if ($productId = $request->input('product_id')) {
            $query->where('product_id', $productId);
        }

        if ($warehouseId = $request->input('warehouse_id')) {
            $query->where('warehouse_id', $warehouseId);
        }

        if ($type = $request->input('movement_type')) {
            $query->where('movement_type', $type);
        }

        $movements = $query->latest('created_at')->paginate(20)->withQueryString();
        $products = Product::orderBy('name')->get();
        $warehouses = Warehouse::all();
        $types = ['opening', 'purchase', 'customer_return', 'transfer_in', 'sale', 'damage', 'transfer_out', 'adjustment'];

        return view('inventory.ledger', compact('movements', 'products', 'warehouses', 'types'));
    }

    public function adjust(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'movement_type' => 'required|in:adjustment,opening,damage',
            'quantity' => 'required|integer|not_in:0',
            'notes' => 'required|string|max:255',
        ]);

        $movement = $this->inventoryService->recordMovement([
            'product_id' => $validated['product_id'],
            'warehouse_id' => $validated['warehouse_id'],
            'movement_type' => $validated['movement_type'],
            'quantity' => $validated['quantity'],
            'reference_type' => 'ManualAdjustment',
            'notes' => $validated['notes'],
        ]);

        AuditLog::log('stock_adjusted', $movement, "Stock adjustment for {$movement->product->name} in {$movement->warehouse->name}: {$movement->quantity} units");

        return back()->with('success', 'Stock ledger updated successfully.');
    }
}
