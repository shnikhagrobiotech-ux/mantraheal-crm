<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceivedNote;
use App\Models\PurchaseOrder;
use App\Services\PurchaseService;
use Illuminate\Http\Request;

class GoodsReceivedNoteController extends Controller
{
    public function __construct(
        protected PurchaseService $purchaseService
    ) {}

    public function index(Request $request)
    {
        $grns = GoodsReceivedNote::with(['purchaseOrder', 'supplier', 'warehouse', 'receiver', 'items.product'])
            ->latest('grn_date')
            ->paginate(15);

        return view('purchase.grn', compact('grns'));
    }

    public function create(Request $request)
    {
        $poId = $request->input('purchase_order_id');
        $po = PurchaseOrder::with(['items.product', 'supplier', 'warehouse'])->findOrFail($poId);

        return view('purchase.create-grn', compact('po'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'grn_date' => 'required|date',
            'invoice_number' => 'nullable|string|max:100',
            'invoice_date' => 'nullable|date',
            'remarks' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.purchase_order_item_id' => 'nullable|exists:purchase_order_items,id',
            'items.*.received_quantity' => 'required|integer|min:0',
            'items.*.damaged_quantity' => 'nullable|integer|min:0',
            'items.*.accepted_quantity' => 'required|integer|min:0',
            'items.*.batch_number' => 'nullable|string|max:50',
            'items.*.manufacturing_date' => 'nullable|date',
            'items.*.expiry_date' => 'nullable|date',
        ]);

        $po = PurchaseOrder::findOrFail($validated['purchase_order_id']);
        $grn = $this->purchaseService->processGrn($po, $validated, $validated['items']);

        return redirect()->route('grn.index')
            ->with('success', "GRN #{$grn->grn_number} created and inventory stock ledger successfully updated!");
    }
}
