<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Batch;
use App\Models\GoodsReceivedNote;
use App\Models\GrnItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseService
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    public function createPurchaseOrder(array $poData, array $items): PurchaseOrder
    {
        return DB::transaction(function () use ($poData, $items) {
            $poNumber = 'PO-' . strtoupper(Str::random(6));

            $subtotal = 0;
            $taxAmount = 0;

            $po = PurchaseOrder::create([
                'po_number' => $poNumber,
                'supplier_id' => $poData['supplier_id'],
                'warehouse_id' => $poData['warehouse_id'],
                'po_date' => $poData['po_date'] ?? now(),
                'expected_delivery_date' => $poData['expected_delivery_date'] ?? null,
                'subtotal' => 0,
                'tax_amount' => 0,
                'total_amount' => 0,
                'status' => 'Ordered',
                'notes' => $poData['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            foreach ($items as $item) {
                $qty = (int) $item['quantity'];
                $rate = (float) $item['rate'];
                $taxPercent = (float) ($item['tax_percent'] ?? 12.00);

                $lineSubtotal = $qty * $rate;
                $lineTax = round(($lineSubtotal * $taxPercent) / 100, 2);
                $lineTotal = $lineSubtotal + $lineTax;

                $subtotal += $lineSubtotal;
                $taxAmount += $lineTax;

                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'quantity' => $qty,
                    'rate' => $rate,
                    'tax_percent' => $taxPercent,
                    'tax_amount' => $lineTax,
                    'total_amount' => $lineTotal,
                    'received_quantity' => 0,
                ]);
            }

            $totalAmount = $subtotal + $taxAmount;
            $po->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
            ]);

            AuditLog::log('created', $po, "Purchase Order #{$po->po_number} created with total ₹" . number_format($totalAmount, 2));

            return $po;
        });
    }

    public function processGrn(PurchaseOrder $po, array $grnData, array $items): GoodsReceivedNote
    {
        return DB::transaction(function () use ($po, $grnData, $items) {
            $grnNumber = 'GRN-' . strtoupper(Str::random(6));

            $grn = GoodsReceivedNote::create([
                'grn_number' => $grnNumber,
                'purchase_order_id' => $po->id,
                'supplier_id' => $po->supplier_id,
                'warehouse_id' => $po->warehouse_id,
                'grn_date' => $grnData['grn_date'] ?? now(),
                'invoice_number' => $grnData['invoice_number'] ?? null,
                'invoice_date' => $grnData['invoice_date'] ?? null,
                'remarks' => $grnData['remarks'] ?? null,
                'received_by' => Auth::id(),
                'status' => 'Approved',
            ]);

            $allReceived = true;

            foreach ($items as $item) {
                $poItem = PurchaseOrderItem::find($item['purchase_order_item_id'] ?? 0);
                $acceptedQty = (int) ($item['accepted_quantity'] ?? 0);
                $damagedQty = (int) ($item['damaged_quantity'] ?? 0);
                $receivedQty = (int) ($item['received_quantity'] ?? ($acceptedQty + $damagedQty));

                $batchNumber = $item['batch_number'] ?? ('BATCH-' . date('ymd') . '-' . Str::random(4));
                $mfgDate = $item['manufacturing_date'] ?? now()->subDays(10)->toDateString();
                $expiryDate = $item['expiry_date'] ?? now()->addYears(2)->toDateString();
                $unitCost = (float) ($poItem?->rate ?? 0);

                GrnItem::create([
                    'goods_received_note_id' => $grn->id,
                    'purchase_order_item_id' => $poItem?->id,
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'ordered_quantity' => $poItem?->quantity ?? $receivedQty,
                    'received_quantity' => $receivedQty,
                    'damaged_quantity' => $damagedQty,
                    'accepted_quantity' => $acceptedQty,
                    'batch_number' => $batchNumber,
                    'manufacturing_date' => $mfgDate,
                    'expiry_date' => $expiryDate,
                    'unit_cost' => $unitCost,
                ]);

                if ($poItem) {
                    $poItem->received_quantity += $acceptedQty;
                    $poItem->save();
                    if ($poItem->received_quantity < $poItem->quantity) {
                        $allReceived = false;
                    }
                }

                // If accepted quantity > 0, create Batch and update Stock Ledger!
                if ($acceptedQty > 0) {
                    $batch = Batch::firstOrCreate(
                        [
                            'batch_number' => $batchNumber,
                            'product_id' => $item['product_id'],
                            'warehouse_id' => $po->warehouse_id,
                        ],
                        [
                            'product_variant_id' => $item['product_variant_id'] ?? null,
                            'manufacturing_date' => $mfgDate,
                            'expiry_date' => $expiryDate,
                            'cost_price' => $unitCost,
                            'mrp' => $poItem?->product?->mrp ?? ($unitCost * 2),
                            'selling_price' => $poItem?->product?->selling_price ?? ($unitCost * 1.5),
                            'initial_quantity' => $acceptedQty,
                            'current_quantity' => 0, // will be incremented by recordMovement
                        ]
                    );

                    $this->inventoryService->recordMovement([
                        'product_id' => $item['product_id'],
                        'product_variant_id' => $item['product_variant_id'] ?? null,
                        'batch_id' => $batch->id,
                        'warehouse_id' => $po->warehouse_id,
                        'movement_type' => 'purchase',
                        'quantity' => $acceptedQty,
                        'reference_type' => 'GoodsReceivedNote',
                        'reference_id' => $grn->id,
                        'notes' => "GRN #{$grn->grn_number} for PO #{$po->po_number}",
                    ]);
                }
            }

            $po->status = $allReceived ? 'Received' : 'Partial';
            $po->save();

            AuditLog::log('grn_approved', $grn, "GRN #{$grn->grn_number} approved for PO #{$po->po_number}. Stock ledger updated.");

            return $grn;
        });
    }
}
