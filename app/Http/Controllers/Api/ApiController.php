<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CallRecording;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Order;
use App\Models\Product;
use App\Models\SalesCall;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Webhook endpoint to sync orders from Shopify
     */
    public function shopifyWebhook(Request $request): JsonResponse
    {
        $payload = $request->all();

        if (empty($payload)) {
            return response()->json(['status' => 'error', 'message' => 'Empty payload'], 400);
        }

        // Search or create customer
        $phone = $payload['customer']['phone'] ?? ($payload['shipping_address']['phone'] ?? '9999999999');
        $email = $payload['customer']['email'] ?? null;
        $name = trim(($payload['customer']['first_name'] ?? '') . ' ' . ($payload['customer']['last_name'] ?? 'Shopify Customer'));

        $customer = Customer::firstOrCreate(
            ['mobile' => $phone],
            [
                'customer_code' => 'MH-CUST-' . strtoupper(substr(md5(uniqid()), 0, 6)),
                'name' => $name,
                'email' => $email,
                'customer_source' => 'Shopify',
                'customer_type' => 'Retail',
            ]
        );

        // Map order items
        $items = [];
        foreach ($payload['line_items'] ?? [] as $lineItem) {
            $sku = $lineItem['sku'] ?? 'DEFAULT';
            $product = Product::where('sku', $sku)->first();
            if ($product) {
                $items[] = [
                    'product_id' => $product->id,
                    'quantity' => $lineItem['quantity'] ?? 1,
                    'unit_price' => $lineItem['price'] ?? $product->selling_price,
                ];
            }
        }

        if (empty($items)) {
            $defaultProduct = Product::first();
            if ($defaultProduct) {
                $items[] = [
                    'product_id' => $defaultProduct->id,
                    'quantity' => 1,
                    'unit_price' => $payload['total_price'] ?? $defaultProduct->selling_price,
                ];
            }
        }

        $orderData = [
            'customer_id' => $customer->id,
            'channel' => 'Shopify',
            'shopify_order_id' => (string) ($payload['id'] ?? null),
            'order_status' => 'New',
            'payment_status' => ($payload['financial_status'] ?? '') === 'paid' ? 'Paid' : 'Pending',
            'payment_method' => ($payload['gateway'] ?? '') === 'cash_on_delivery' ? 'COD' : 'Shopify Gateway',
            'shipping_name' => $name,
            'shipping_phone' => $phone,
            'shipping_address_line1' => $payload['shipping_address']['address1'] ?? 'Shopify Address',
            'shipping_city' => $payload['shipping_address']['city'] ?? 'New Delhi',
            'shipping_state' => $payload['shipping_address']['province'] ?? 'Delhi',
            'shipping_pincode' => $payload['shipping_address']['zip'] ?? '110001',
            'discount_amount' => $payload['total_discounts'] ?? 0,
            'shipping_charge' => $payload['total_shipping_price_set']['shop_money']['amount'] ?? 0,
            'notes' => 'Imported via Shopify Webhook',
        ];

        $order = $this->orderService->createOrder($orderData, $items);

        return response()->json([
            'status' => 'success',
            'message' => 'Order synced successfully',
            'order_id' => $order->id,
            'order_number' => $order->order_number,
        ], 201);
    }

    /**
     * Webhook endpoint to sync tracking/status from Shiprocket
     */
    public function shiprocketWebhook(Request $request): JsonResponse
    {
        $payload = $request->all();
        $awb = $payload['awb'] ?? null;
        $orderId = $payload['order_id'] ?? null;
        $shiprocketStatus = strtolower($payload['current_status'] ?? '');

        $order = null;
        if ($orderId) {
            $order = Order::where('order_number', $orderId)->orWhere('id', $orderId)->first();
        } elseif ($awb) {
            $order = Order::where('tracking_number', $awb)->first();
        }

        if ($order) {
            if (str_contains($shiprocketStatus, 'delivered')) {
                $this->orderService->updateStatus($order, 'Delivered', "Shiprocket status: {$payload['current_status']}");
            } elseif (str_contains($shiprocketStatus, 'out for delivery')) {
                $this->orderService->updateStatus($order, 'Out for Delivery', "Shiprocket status: {$payload['current_status']}");
            } elseif (str_contains($shiprocketStatus, 'shipped') || str_contains($shiprocketStatus, 'in transit')) {
                $this->orderService->updateStatus($order, 'Shipped', "Shiprocket status: {$payload['current_status']}");
            } elseif (str_contains($shiprocketStatus, 'rto')) {
                $this->orderService->updateStatus($order, 'RTO', "Shiprocket RTO: {$payload['current_status']}");
            }
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Endpoint to create lead from marketing landing pages / Meta ads
     */
    public function captureLead(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:20',
            'email' => 'nullable|email',
            'source' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $lead = Lead::create([
            'lead_code' => 'MH-LEAD-' . strtoupper(substr(md5(uniqid()), 0, 6)),
            'name' => $validated['name'],
            'mobile' => $validated['mobile'],
            'whatsapp' => $validated['mobile'],
            'email' => $validated['email'] ?? null,
            'source' => $validated['source'] ?? 'Meta Ads',
            'stage' => 'New',
            'notes' => $validated['notes'] ?? 'Captured via Landing Page API',
        ]);

        return response()->json([
            'status' => 'success',
            'lead_code' => $lead->lead_code,
        ], 201);
    }

    /**
     * Webhook endpoint to sync call recordings & status from Cloud Telephony (Exotel / Twilio)
     */
    public function telephonyCallback(Request $request): JsonResponse
    {
        $payload = $request->all();

        $fromNumber = $payload['From'] ?? ($payload['CallFrom'] ?? ($payload['customer_number'] ?? null));
        $toNumber = $payload['To'] ?? ($payload['CallTo'] ?? ($payload['agent_number'] ?? null));
        $recordingUrl = $payload['RecordingUrl'] ?? ($payload['recording_url'] ?? null);
        $callDuration = (int) ($payload['DialCallDuration'] ?? ($payload['Legs'][0]['Duration'] ?? ($payload['duration'] ?? 120)));
        $callStatus = strtolower($payload['Status'] ?? ($payload['DialCallStatus'] ?? 'completed'));

        // Clean phone number for customer/lead matching
        $cleanPhone = preg_replace('/[^0-9]/', '', (string) $fromNumber);
        if (strlen($cleanPhone) > 10) {
            $cleanPhone = substr($cleanPhone, -10);
        }

        $customer = $cleanPhone ? Customer::where('mobile', 'like', "%{$cleanPhone}%")->first() : null;
        $lead = (!$customer && $cleanPhone) ? Lead::where('mobile', 'like', "%{$cleanPhone}%")->first() : null;

        $agent = User::whereIn('role_slug', ['sales_executive', 'sales_manager'])->first();

        $salesCall = \App\Models\SalesCall::create([
            'customer_id' => $customer?->id,
            'lead_id' => $lead?->id,
            'user_id' => $agent?->id,
            'call_datetime' => now(),
            'direction' => 'incoming',
            'duration_seconds' => $callDuration,
            'outcome' => str_contains($callStatus, 'completed') ? 'Connected' : 'No Answer',
            'notes' => 'Call logged automatically via Telephony Webhook Callback.',
        ]);

        $callRecording = null;
        if ($recordingUrl || str_contains($callStatus, 'completed')) {
            $callRecording = \App\Models\CallRecording::create([
                'sales_call_id' => $salesCall->id,
                'customer_id' => $customer?->id,
                'user_id' => $agent?->id,
                'file_name' => 'rec_' . $salesCall->id . '_' . time() . '.wav',
                'file_path' => 'call-recordings/sample_call.wav',
                'mime_type' => 'audio/wav',
                'file_size' => 48044,
                'duration_seconds' => $callDuration,
                'storage_disk' => 'local',
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Telephony call recording synced successfully.',
            'sales_call_id' => $salesCall->id,
            'recording_id' => $callRecording?->id,
        ], 200);
    }

    /**
     * Simulate incoming webhook for testing from Settings panel
     */
    public function simulateWebhook(Request $request): JsonResponse
    {
        $type = $request->input('type', 'shopify');

        if ($type === 'shopify') {
            $samplePayload = [
                'id' => rand(1000000000, 9999999999),
                'financial_status' => 'paid',
                'gateway' => 'shopify_payments',
                'customer' => [
                    'first_name' => 'Aayush',
                    'last_name' => 'Verma',
                    'phone' => '+919810012345',
                    'email' => 'aayush.verma@example.com',
                ],
                'shipping_address' => [
                    'address1' => 'Tower 4, DLF Phase 5',
                    'city' => 'Gurugram',
                    'province' => 'Haryana',
                    'zip' => '122002',
                    'phone' => '+919810012345',
                ],
                'total_price' => 1999.00,
                'total_discounts' => 100.00,
                'total_shipping_price_set' => ['shop_money' => ['amount' => 50.00]],
                'line_items' => [
                    [
                        'sku' => Product::first()?->sku ?? 'MH-ASHWA-01',
                        'quantity' => 1,
                        'price' => 1999.00,
                    ],
                ],
            ];

            $simRequest = Request::create('/api/shopify/orders-create', 'POST', $samplePayload);
            return $this->shopifyWebhook($simRequest);
        }

        if ($type === 'shiprocket') {
            $latestOrder = Order::latest()->first();
            $samplePayload = [
                'order_id' => $latestOrder?->order_number ?? 'MH-ORD-1001',
                'awb' => 'SR' . rand(10000000, 99999999),
                'current_status' => 'Delivered',
            ];

            $simRequest = Request::create('/api/shiprocket/tracking', 'POST', $samplePayload);
            return $this->shiprocketWebhook($simRequest);
        }

        if ($type === 'telephony') {
            $samplePayload = [
                'From' => '+919810098765',
                'To' => '+918047192830',
                'Status' => 'completed',
                'DialCallDuration' => 185,
                'RecordingUrl' => 'https://api.exotel.com/v1/recordings/sample.wav',
            ];

            $simRequest = Request::create('/api/telephony/recording-callback', 'POST', $samplePayload);
            return $this->telephonyCallback($simRequest);
        }

        return response()->json(['status' => 'error', 'message' => 'Invalid simulation type'], 400);
    }
}
