<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\Refund;
use App\Models\ReturnItem;
use App\Models\RtoRecord;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReturnRefundService
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    public function createReturn(Order $order, array $data, array $items): OrderReturn
    {
        return DB::transaction(function () use ($order, $data, $items) {
            $returnNumber = 'RET-' . strtoupper(Str::random(6));

            $return = OrderReturn::create([
                'return_number' => $returnNumber,
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'return_date' => $data['return_date'] ?? now(),
                'reason' => $data['reason'] ?? 'Product issue',
                'status' => 'Return Requested',
                'qc_status' => 'Pending',
                'restocking_warehouse_id' => $data['restocking_warehouse_id'] ?? null,
                'refund_action' => $data['refund_action'] ?? 'Refund',
                'notes' => $data['notes'] ?? null,
                'processed_by' => Auth::id(),
            ]);

            foreach ($items as $item) {
                ReturnItem::create([
                    'order_return_id' => $return->id,
                    'order_item_id' => $item['order_item_id'],
                    'product_id' => $item['product_id'],
                    'quantity' => (int) $item['quantity'],
                    'reason' => $item['reason'] ?? $return->reason,
                    'condition_notes' => $item['condition_notes'] ?? null,
                ]);
            }

            $order->update(['order_status' => 'Returned']);
            $order->customer->recalculateMetrics();

            AuditLog::log('return_created', $return, "Return request #{$return->return_number} created for Order #{$order->order_number}");

            return $return;
        });
    }

    public function processQc(OrderReturn $return, string $qcStatus, ?int $warehouseId = null, ?string $notes = null): OrderReturn
    {
        return DB::transaction(function () use ($return, $qcStatus, $warehouseId, $notes) {
            $return->qc_status = $qcStatus;
            $return->status = $qcStatus === 'Passed' ? 'QC' : 'Rejected';
            $return->notes = ($return->notes ? $return->notes . "\n" : "") . "[QC " . now()->format('d M Y') . "] " . $notes;

            if ($qcStatus === 'Passed') {
                $wh = $warehouseId ? Warehouse::find($warehouseId) : ($return->restocking_warehouse_id ? Warehouse::find($return->restocking_warehouse_id) : Warehouse::where('is_default', true)->first());
                
                if ($wh) {
                    $return->restocking_warehouse_id = $wh->id;
                    // Restock each returned item back into inventory ledger
                    foreach ($return->items as $item) {
                        $this->inventoryService->recordMovement([
                            'product_id' => $item->product_id,
                            'warehouse_id' => $wh->id,
                            'movement_type' => 'customer_return',
                            'quantity' => $item->quantity,
                            'reference_type' => 'OrderReturn',
                            'reference_id' => $return->id,
                            'notes' => "Restocked from approved return #{$return->return_number}",
                        ]);
                    }
                }
            }

            $return->save();

            AuditLog::log('return_qc_updated', $return, "Return #{$return->return_number} QC marked as {$qcStatus}");

            return $return;
        });
    }

    public function processRefund(OrderReturn $return, array $refundData): Refund
    {
        return DB::transaction(function () use ($return, $refundData) {
            $refundNumber = 'REF-' . strtoupper(Str::random(6));

            $refund = Refund::create([
                'refund_number' => $refundNumber,
                'order_id' => $return->order_id,
                'order_return_id' => $return->id,
                'customer_id' => $return->customer_id,
                'amount' => (float) $refundData['amount'],
                'refund_method' => $refundData['refund_method'] ?? 'Original Payment Method',
                'transaction_reference' => $refundData['transaction_reference'] ?? ('REF-' . Str::random(8)),
                'status' => 'Processed',
                'notes' => $refundData['notes'] ?? null,
                'processed_by' => Auth::id(),
                'processed_at' => now(),
            ]);

            $return->update(['status' => 'Refund / Replacement']);
            $return->order->update(['payment_status' => 'Refunded']);
            $return->customer->recalculateMetrics();

            AuditLog::log('refund_processed', $refund, "Refund ₹{$refund->amount} processed for Return #{$return->return_number}");

            return $refund;
        });
    }

    public function processRto(Order $order, array $rtoData): RtoRecord
    {
        return DB::transaction(function () use ($order, $rtoData) {
            $rtoCode = 'RTO-' . strtoupper(Str::random(6));

            $rto = RtoRecord::create([
                'rto_code' => $rtoCode,
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'courier_name' => $rtoData['courier_name'] ?? ($order->courier_name ?? 'Delhivery'),
                'tracking_number' => $rtoData['tracking_number'] ?? ($order->tracking_number ?? 'AWB-' . Str::random(8)),
                'rto_initiated_date' => $rtoData['rto_initiated_date'] ?? now()->toDateString(),
                'rto_delivered_date' => $rtoData['rto_delivered_date'] ?? null,
                'reason' => $rtoData['reason'] ?? 'Customer Refused COD',
                'state' => $order->shipping_state ?? 'Delhi',
                'city' => $order->shipping_city ?? 'New Delhi',
                'sales_channel' => $order->channel ?? 'Website',
                'total_amount' => $order->grand_total,
                'status' => $rtoData['status'] ?? 'In Transit',
                'received_warehouse_id' => $rtoData['received_warehouse_id'] ?? null,
                'notes' => $rtoData['notes'] ?? null,
            ]);

            $order->update([
                'order_status' => 'RTO',
                'fulfillment_status' => 'RTO',
            ]);
            $order->customer->recalculateMetrics();

            AuditLog::log('rto_recorded', $rto, "RTO recorded for Order #{$order->order_number}. Reason: {$rto->reason}");

            return $rto;
        });
    }
}
