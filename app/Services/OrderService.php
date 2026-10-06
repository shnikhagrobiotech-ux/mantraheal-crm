<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected AuditService $auditService
    ) {}

    public static function normalizeOrderStatus(?string $status): string
    {
        $statusMap = [
            'new' => 'New',
            'confirmed' => 'Confirmed',
            'processing' => 'Processing',
            'packed' => 'Packed',
            'shipped' => 'Shipped',
            'out for delivery' => 'Out for Delivery',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
            'returned' => 'Returned',
            'refunded' => 'Refunded',
            'rto' => 'RTO',
        ];
        return $statusMap[strtolower(trim($status ?? ''))] ?? 'New';
    }

    public static function normalizePaymentStatus(?string $status): string
    {
        $map = [
            'pending' => 'Pending',
            'paid' => 'Paid',
            'partial' => 'Partially Paid',
            'partially paid' => 'Partially Paid',
            'failed' => 'Failed',
            'refunded' => 'Refunded',
        ];
        return $map[strtolower(trim($status ?? ''))] ?? 'Pending';
    }

    public static function normalizePaymentMethod(?string $method): string
    {
        $map = [
            'cod' => 'COD',
            'cash on delivery' => 'COD',
            'razorpay' => 'Razorpay',
            'prepaid upi' => 'Prepaid UPI',
            'upi' => 'Prepaid UPI',
            'net banking' => 'Net Banking',
            'bank transfer' => 'Bank Transfer',
            'credit card' => 'Credit Card',
            'debit card' => 'Debit Card',
            'wallet' => 'Wallet',
            'other' => 'Other',
        ];
        return $map[strtolower(trim($method ?? ''))] ?? 'COD';
    }

    public function createOrder(array $orderData, array $items): Order
    {
        return DB::transaction(function () use ($orderData, $items) {
            $customer = Customer::findOrFail($orderData['customer_id']);

            $subtotal = 0;
            $totalTax = 0;
            $cgst = 0;
            $sgst = 0;
            $igst = 0;
            $discount = (float) ($orderData['discount_amount'] ?? 0);
            $shipping = (float) ($orderData['shipping_charge'] ?? 0);

            // Determine intra-state vs inter-state for Indian GST calculation
            // MantraHeal primary warehouse state is Delhi
            $shippingState = strtolower(trim($orderData['shipping_state'] ?? 'Delhi'));
            $isIntraState = in_array($shippingState, ['delhi', 'dl', 'nct of delhi']);

            $orderNumber = 'MH-ORD-' . strtoupper(Str::random(6));

            $order = Order::create([
                'order_number' => $orderNumber,
                'customer_id' => $customer->id,
                'order_date' => $orderData['order_date'] ?? now(),
                'channel' => $orderData['channel'] ?? 'Website',
                'shopify_order_id' => $orderData['shopify_order_id'] ?? null,
                'subtotal' => 0,
                'discount_amount' => $discount,
                'coupon_code' => $orderData['coupon_code'] ?? null,
                'cgst_amount' => 0,
                'sgst_amount' => 0,
                'igst_amount' => 0,
                'total_tax' => 0,
                'shipping_charge' => $shipping,
                'grand_total' => 0,
                'order_status' => self::normalizeOrderStatus($orderData['order_status'] ?? 'New'),
                'payment_status' => self::normalizePaymentStatus($orderData['payment_status'] ?? 'Pending'),
                'payment_method' => self::normalizePaymentMethod($orderData['payment_method'] ?? 'COD'),
                'fulfillment_status' => 'Unfulfilled',
                'shipping_name' => $orderData['shipping_name'] ?? $customer->name,
                'shipping_phone' => $orderData['shipping_phone'] ?? $customer->mobile,
                'shipping_address_line1' => $orderData['shipping_address_line1'] ?? '',
                'shipping_address_line2' => $orderData['shipping_address_line2'] ?? null,
                'shipping_city' => $orderData['shipping_city'] ?? '',
                'shipping_state' => $orderData['shipping_state'] ?? 'Delhi',
                'shipping_pincode' => $orderData['shipping_pincode'] ?? '',
                'courier_name' => $orderData['courier_name'] ?? null,
                'tracking_number' => $orderData['tracking_number'] ?? null,
                'assigned_user_id' => $orderData['assigned_user_id'] ?? $customer->assigned_user_id,
                'notes' => $orderData['notes'] ?? null,
            ]);

            foreach ($items as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $qty = (int) $itemData['quantity'];
                $unitPrice = (float) ($itemData['unit_price'] ?? $product->selling_price);
                $taxPercent = (float) ($product->gst_percent ?? 12.00);
                
                $lineSubtotal = $unitPrice * $qty;
                $lineTax = round(($lineSubtotal * $taxPercent) / 100, 2);
                $lineTotal = $lineSubtotal + $lineTax;

                $subtotal += $lineSubtotal;
                $totalTax += $lineTax;

                if ($isIntraState) {
                    $cgst += round($lineTax / 2, 2);
                    $sgst += round($lineTax / 2, 2);
                } else {
                    $igst += $lineTax;
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_variant_id' => $itemData['product_variant_id'] ?? null,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'unit_price' => $unitPrice,
                    'quantity' => $qty,
                    'tax_rate' => $taxPercent,
                    'tax_amount' => $lineTax,
                    'discount_amount' => 0,
                    'total_price' => $lineTotal,
                ]);
            }

            $grandTotal = max(0, $subtotal - $discount + $totalTax + $shipping);

            $order->update([
                'subtotal' => $subtotal,
                'cgst_amount' => $cgst,
                'sgst_amount' => $sgst,
                'igst_amount' => $igst,
                'total_tax' => $totalTax,
                'grand_total' => $grandTotal,
            ]);

            // Deduct stock if already Confirmed or Processing
            if (in_array($order->order_status, ['Confirmed', 'Processing', 'Packed', 'Shipped'])) {
                $this->inventoryService->deductOrderStock($order);
            }

            // Recalculate Customer 360 KPIs
            $customer->recalculateMetrics();

            // Record initial payment if marked as Paid
            if ($order->payment_status === 'Paid') {
                $this->recordPayment($order, [
                    'payment_method' => $order->payment_method,
                    'amount' => $order->grand_total,
                    'payment_date' => now(),
                    'status' => 'Success',
                    'transaction_reference' => 'PREPAID-' . strtoupper(Str::random(8)),
                    'notes' => 'Paid upon order placement',
                ]);
            }

            AuditLog::log('created', $order, 'Order #' . $order->order_number . ' created for customer ' . $customer->name, null, $order->toArray());

            return $order;
        });
    }

    public function updateStatus(Order $order, string $newStatus, ?string $notes = null): Order
    {
        $newStatus = self::normalizeOrderStatus($newStatus);
        return DB::transaction(function () use ($order, $newStatus, $notes) {
            $oldStatus = $order->order_status;
            if ($oldStatus === $newStatus) {
                return $order;
            }

            $oldValues = ['order_status' => $oldStatus];
            $order->order_status = $newStatus;

            if ($newStatus === 'Delivered') {
                $order->delivered_at = now();
                $order->fulfillment_status = 'Fulfilled';
            } elseif ($newStatus === 'Shipped') {
                $order->fulfillment_status = 'Shipped';
            } elseif ($newStatus === 'Cancelled') {
                $order->fulfillment_status = 'Cancelled';
                // Restore stock if previously deducted
                $this->inventoryService->restoreOrderStock($order, 'Order Cancelled');
            } elseif ($newStatus === 'Returned') {
                $order->fulfillment_status = 'Returned';
            } elseif ($newStatus === 'RTO') {
                $order->fulfillment_status = 'RTO';
            }

            if ($notes) {
                $order->notes = ($order->notes ? $order->notes . "\n" : "") . "[" . now()->format('d M Y H:i') . "] " . $notes;
            }

            $order->save();
            $order->customer->recalculateMetrics();

            AuditLog::log(
                'status_change',
                $order,
                "Order status changed: {$oldStatus} → {$newStatus}",
                $oldValues,
                ['order_status' => $newStatus]
            );

            return $order;
        });
    }

    public function recordPayment(Order $order, array $paymentData): Payment
    {
        return DB::transaction(function () use ($order, $paymentData) {
            $paymentNumber = 'PAY-' . strtoupper(Str::random(8));

            $payment = Payment::create([
                'payment_number' => $paymentNumber,
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'payment_method' => $paymentData['payment_method'] ?? $order->payment_method,
                'transaction_reference' => $paymentData['transaction_reference'] ?? null,
                'amount' => (float) $paymentData['amount'],
                'payment_date' => $paymentData['payment_date'] ?? now(),
                'status' => $paymentData['status'] ?? 'Success',
                'notes' => $paymentData['notes'] ?? null,
                'recorded_by' => Auth::id(),
            ]);

            // Reconcile total payments
            $totalPaid = $order->payments()->where('status', 'Success')->sum('amount');
            if ($totalPaid >= $order->grand_total) {
                $order->payment_status = 'Paid';
            } elseif ($totalPaid > 0) {
                $order->payment_status = 'Partially Paid';
            } else {
                $order->payment_status = 'Pending';
            }
            $order->save();

            $order->customer->recalculateMetrics();

            AuditLog::log(
                'payment_recorded',
                $payment,
                "Payment ₹{$payment->amount} recorded for Order #{$order->order_number} ({$order->payment_status})"
            );

            return $payment;
        });
    }
}
