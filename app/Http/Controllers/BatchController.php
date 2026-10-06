<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Batch;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BatchController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    public function index(Request $request)
    {
        $query = Batch::with(['product', 'warehouse']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('batch_number', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($warehouseId = $request->input('warehouse_id')) {
            $query->where('warehouse_id', $warehouseId);
        }

        if ($productId = $request->input('product_id')) {
            $query->where('product_id', $productId);
        }

        $batches = $query->orderBy('expiry_date', 'asc')->paginate(15)->withQueryString();
        $warehouses = Warehouse::all();
        $products = Product::orderBy('name')->get();

        return view('batches.index', compact('batches', 'warehouses', 'products'));
    }

    public function expiryAlerts(Request $request)
    {
        $filter = $request->input('filter', '30'); // expired, 30, 60, 90

        $query = Batch::with(['product', 'warehouse'])->where('current_quantity', '>', 0);

        switch ($filter) {
            case 'expired':
                $query->expired();
                break;
            case '60':
                $query->whereBetween('expiry_date', [Carbon::today(), Carbon::today()->addDays(60)]);
                break;
            case '90':
                $query->whereBetween('expiry_date', [Carbon::today(), Carbon::today()->addDays(90)]);
                break;
            case '30':
            default:
                $query->expiringIn30Days();
                break;
        }

        $batches = $query->orderBy('expiry_date', 'asc')->paginate(20)->withQueryString();

        $counts = [
            'expired' => Batch::expired()->where('current_quantity', '>', 0)->count(),
            '30' => Batch::expiringIn30Days()->where('current_quantity', '>', 0)->count(),
            '60' => Batch::whereBetween('expiry_date', [Carbon::today(), Carbon::today()->addDays(60)])->where('current_quantity', '>', 0)->count(),
            '90' => Batch::whereBetween('expiry_date', [Carbon::today(), Carbon::today()->addDays(90)])->where('current_quantity', '>', 0)->count(),
        ];

        return view('batches.expiry-alerts', compact('batches', 'filter', 'counts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'batch_number' => 'required|string|max:50',
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'manufacturing_date' => 'required|date',
            'expiry_date' => 'required|date|after:manufacturing_date',
            'initial_quantity' => 'required|integer|min:1',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        $batch = Batch::create([
            'batch_number' => $validated['batch_number'],
            'product_id' => $product->id,
            'warehouse_id' => $validated['warehouse_id'],
            'manufacturing_date' => $validated['manufacturing_date'],
            'expiry_date' => $validated['expiry_date'],
            'cost_price' => $validated['cost_price'],
            'mrp' => $product->mrp,
            'selling_price' => $validated['selling_price'],
            'initial_quantity' => $validated['initial_quantity'],
            'current_quantity' => 0, // updated via recordMovement
        ]);

        $this->inventoryService->recordMovement([
            'product_id' => $product->id,
            'batch_id' => $batch->id,
            'warehouse_id' => $validated['warehouse_id'],
            'movement_type' => 'opening',
            'quantity' => $validated['initial_quantity'],
            'reference_type' => 'BatchCreation',
            'notes' => "Initial batch stock for #{$batch->batch_number}",
        ]);

        AuditLog::log('batch_created', $batch, "Batch #{$batch->batch_number} created for {$product->name}");

        return back()->with('success', 'Batch created and stock ledger updated.');
    }
}
