<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockBalance;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InventoryService
{
    /**
     * Records a stock movement and updates balances atomically.
     * Formula:
     * Current Stock = Opening + Purchase + Customer Return + Transfer In - Sales - Damage - Transfer Out - Adjustment
     */
    public function recordMovement(array $data): StockMovement
    {
        return DB::transaction(function () use ($data) {
            $productId = $data['product_id'];
            $variantId = $data['product_variant_id'] ?? null;
            $warehouseId = $data['warehouse_id'];
            $batchId = $data['batch_id'] ?? null;
            $type = $data['movement_type'];
            $qty = (int) $data['quantity']; // signed: positive for addition, negative for deduction

            // Get or create warehouse stock balance
            $balance = StockBalance::firstOrCreate(
                [
                    'product_id' => $productId,
                    'product_variant_id' => $variantId,
                    'warehouse_id' => $warehouseId,
                ],
                ['quantity' => 0, 'reserved_quantity' => 0]
            );

            // Determine sign based on movement type if not already applied
            $signedQuantity = $qty;
            if (in_array($type, ['sale', 'damage', 'transfer_out']) && $signedQuantity > 0) {
                $signedQuantity = -$signedQuantity;
            } elseif (in_array($type, ['opening', 'purchase', 'customer_return', 'transfer_in']) && $signedQuantity < 0) {
                $signedQuantity = abs($signedQuantity);
            }

            $balanceAfter = $balance->quantity + $signedQuantity;
            $balance->quantity = $balanceAfter;
            $balance->save();

            // If a batch is specified, update the batch's current quantity
            if ($batchId) {
                $batch = Batch::find($batchId);
                if ($batch) {
                    $batch->current_quantity = max(0, $batch->current_quantity + $signedQuantity);
                    $batch->save();
                }
            }

            $movementCode = 'MOV-' . strtoupper(Str::random(8));

            return StockMovement::create([
                'movement_code' => $movementCode,
                'product_id' => $productId,
                'product_variant_id' => $variantId,
                'batch_id' => $batchId,
                'warehouse_id' => $warehouseId,
                'movement_type' => $type,
                'quantity' => $signedQuantity,
                'balance_after' => $balanceAfter,
                'reference_type' => $data['reference_type'] ?? null,
                'reference_id' => $data['reference_id'] ?? null,
                'user_id' => $data['user_id'] ?? Auth::id(),
                'notes' => $data['notes'] ?? null,
            ]);
        });
    }

    /**
     * Deducts stock for an order using FEFO (First-Expired, First-Out)
     */
    public function deductOrderStock(Order $order, ?int $warehouseId = null): void
    {
        $warehouse = $warehouseId ? Warehouse::find($warehouseId) : Warehouse::where('is_default', true)->first() ?? Warehouse::first();
        if (!$warehouse) {
            return;
        }

        foreach ($order->items as $item) {
            // Find FEFO batch for this product in the selected warehouse
            $batch = Batch::where('product_id', $item->product_id)
                ->where('warehouse_id', $warehouse->id)
                ->where('current_quantity', '>=', $item->quantity)
                ->whereDate('expiry_date', '>=', now())
                ->orderBy('expiry_date', 'asc')
                ->first();

            if (!$batch) {
                // Fallback to any batch with quantity
                $batch = Batch::where('product_id', $item->product_id)
                    ->where('warehouse_id', $warehouse->id)
                    ->orderBy('expiry_date', 'asc')
                    ->first();
            }

            if ($batch) {
                $item->batch_id = $batch->id;
                $item->save();
            }

            $this->recordMovement([
                'product_id' => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'batch_id' => $batch?->id,
                'warehouse_id' => $warehouse->id,
                'movement_type' => 'sale',
                'quantity' => $item->quantity,
                'reference_type' => 'Order',
                'reference_id' => $order->id,
                'notes' => 'Dispatched for Order #' . $order->order_number,
            ]);
        }
    }

    /**
     * Restores stock when an order is cancelled or returned
     */
    public function restoreOrderStock(Order $order, string $reason = 'Order Cancelled', ?int $warehouseId = null): void
    {
        $warehouse = $warehouseId ? Warehouse::find($warehouseId) : Warehouse::where('is_default', true)->first() ?? Warehouse::first();
        if (!$warehouse) {
            return;
        }

        foreach ($order->items as $item) {
            $this->recordMovement([
                'product_id' => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'batch_id' => $item->batch_id,
                'warehouse_id' => $warehouse->id,
                'movement_type' => 'customer_return',
                'quantity' => $item->quantity,
                'reference_type' => 'Order',
                'reference_id' => $order->id,
                'notes' => $reason . ' - Order #' . $order->order_number,
            ]);
        }
    }
}
