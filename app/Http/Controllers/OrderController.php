<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected ReportService $reportService
    ) {}

    public function index(Request $request)
    {
        $query = Order::with(['customer', 'assignedUser', 'items']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('tracking_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%")
                            ->orWhere('mobile', 'like', "%{$search}%");
                    });
            });
        }

        if ($status = $request->input('status')) {
            $query->where('order_status', $status);
        }

        if ($paymentStatus = $request->input('payment_status')) {
            $query->where('payment_status', $paymentStatus);
        }

        if ($channel = $request->input('channel')) {
            $query->where('channel', $channel);
        }

        if ($assignedTo = $request->input('assigned_user_id')) {
            $query->where('assigned_user_id', $assignedTo);
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('order_date', '>=', $dateFrom);
        }
        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('order_date', '<=', $dateTo);
        }

        $orders = $query->latest('order_date')->paginate(15)->withQueryString();
        $statuses = config('mantraheal.order_statuses');
        $paymentStatuses = config('mantraheal.payment_statuses');
        $channels = ['Website', 'Shopify', 'Meta Ads', 'Phone/Call', 'WhatsApp', 'Offline'];
        $salesExecutives = User::whereIn('role_slug', ['sales_executive', 'sales_manager'])->get();

        return view('orders.index', compact('orders', 'statuses', 'paymentStatuses', 'channels', 'salesExecutives'));
    }

    public function create(Request $request)
    {
        $customers = Customer::orderBy('name')->get();
        $products = Product::where('is_active', true)->with('variants')->get();
        $selectedCustomer = $request->input('customer_id') ? Customer::find($request->input('customer_id')) : null;
        $couriers = config('mantraheal.couriers');
        $paymentMethods = config('mantraheal.payment_methods');

        return view('orders.create', compact('customers', 'products', 'selectedCustomer', 'couriers', 'paymentMethods'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'order_date' => 'required|date',
            'channel' => 'required|string',
            'payment_method' => 'required|string',
            'payment_status' => 'required|string',
            'order_status' => 'required|string',
            'discount_amount' => 'nullable|numeric|min:0',
            'coupon_code' => 'nullable|string|max:50',
            'shipping_charge' => 'nullable|numeric|min:0',
            'shipping_name' => 'nullable|string|max:255',
            'shipping_phone' => 'nullable|string|max:20',
            'shipping_address_line1' => 'required|string|max:255',
            'shipping_address_line2' => 'nullable|string|max:255',
            'shipping_city' => 'required|string|max:100',
            'shipping_state' => 'required|string|max:100',
            'shipping_pincode' => 'required|string|max:10',
            'courier_name' => 'nullable|string',
            'tracking_number' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'nullable|numeric|min:0',
        ]);

        $order = $this->orderService->createOrder($validated, $validated['items']);

        return redirect()->route('orders.show', $order->id)
            ->with('success', "Order #{$order->order_number} created successfully.");
    }

    public function show(Order $order)
    {
        $order->load([
            'customer.addresses',
            'items.product',
            'payments.recorder',
            'shipments',
            'returns.items',
            'rtoRecord',
            'assignedUser',
        ]);

        $statuses = config('mantraheal.order_statuses');
        $paymentMethods = config('mantraheal.payment_methods');
        $couriers = config('mantraheal.couriers');

        return view('orders.show', compact('order', 'statuses', 'paymentMethods', 'couriers'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'order_status' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $this->orderService->updateStatus($order, $validated['order_status'], $validated['notes'] ?? null);

        return back()->with('success', "Order status updated to {$validated['order_status']}.");
    }

    public function recordPayment(Request $request, Order $order)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'transaction_reference' => 'nullable|string|max:100',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $this->orderService->recordPayment($order, $validated);

        return back()->with('success', 'Payment recorded successfully.');
    }

    public function invoice(Order $order)
    {
        $order->load(['customer.addresses', 'items.product', 'payments']);
        return view('orders.invoice', compact('order'));
    }

    public function destroy(Order $order)
    {
        AuditLog::log('deleted', $order, "Order #{$order->order_number} deleted");
        $order->delete();
        return redirect()->route('orders.index')->with('success', 'Order deleted.');
    }

    public function exportCsv()
    {
        $orders = Order::with('customer')->get();
        $headers = ['Order Number', 'Date', 'Customer', 'Phone', 'Channel', 'Subtotal', 'Tax', 'Shipping', 'Grand Total', 'Status', 'Payment Status', 'Courier', 'AWB'];
        $rows = [];

        foreach ($orders as $o) {
            $rows[] = [
                $o->order_number,
                $o->order_date->format('Y-m-d H:i'),
                $o->customer?->name ?? 'Guest',
                $o->customer?->mobile ?? '',
                $o->channel,
                $o->subtotal,
                $o->total_tax,
                $o->shipping_charge,
                $o->grand_total,
                $o->order_status,
                $o->payment_status,
                $o->courier_name ?? '',
                $o->tracking_number ?? '',
            ];
        }

        return $this->reportService->exportCsv($headers, $rows, 'mantraheal_orders_' . date('Y-m-d') . '.csv');
    }
}
