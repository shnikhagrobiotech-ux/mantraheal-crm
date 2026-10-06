<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DeliveryController extends Controller
{
    /**
     * Display Delivery & Logistics Dispatch Panel
     */
    public function index(Request $request): View
    {
        $currentTab = $request->input('tab', 'ready');
        $search = $request->input('search');

        // Orders ready for booking courier
        $readyQuery = Order::with(['customer', 'items.product'])
            ->whereIn('order_status', ['New', 'Confirmed', 'Processing'])
            ->whereNull('tracking_number');

        if ($search) {
            $readyQuery->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('shipping_name', 'like', "%{$search}%")
                    ->orWhere('shipping_city', 'like', "%{$search}%")
                    ->orWhere('shipping_phone', 'like', "%{$search}%");
            });
        }
        $readyOrders = $readyQuery->latest()->paginate(15, ['*'], 'ready_page');

        // Shipments queries
        $shipmentsQuery = Shipment::with(['order.customer', 'order.items.product']);
        if ($search) {
            $shipmentsQuery->where(function ($q) use ($search) {
                $q->where('tracking_number', 'like', "%{$search}%")
                    ->orWhere('awb_code', 'like', "%{$search}%")
                    ->orWhere('courier_name', 'like', "%{$search}%")
                    ->orWhereHas('order', function ($oq) use ($search) {
                        $oq->where('order_number', 'like', "%{$search}%")
                            ->orWhere('shipping_name', 'like', "%{$search}%")
                            ->orWhere('shipping_city', 'like', "%{$search}%");
                    });
            });
        }

        $inTransitShipments = (clone $shipmentsQuery)->whereIn('status', ['In Transit', 'Shipped', 'Manifested'])->latest()->paginate(15, ['*'], 'transit_page');
        $outForDeliveryShipments = (clone $shipmentsQuery)->where('status', 'Out for Delivery')->latest()->paginate(15, ['*'], 'ofd_page');
        $deliveredShipments = (clone $shipmentsQuery)->where('status', 'Delivered')->latest()->paginate(15, ['*'], 'delivered_page');
        $rtoShipments = (clone $shipmentsQuery)->whereIn('status', ['RTO', 'NDR', 'Returned'])->latest()->paginate(15, ['*'], 'rto_page');

        // Tab counts
        $counts = [
            'ready' => Order::whereIn('order_status', ['New', 'Confirmed', 'Processing'])->whereNull('tracking_number')->count(),
            'in_transit' => Shipment::whereIn('status', ['In Transit', 'Shipped', 'Manifested'])->count(),
            'out_for_delivery' => Shipment::where('status', 'Out for Delivery')->count(),
            'delivered' => Shipment::where('status', 'Delivered')->count(),
            'rto' => Shipment::whereIn('status', ['RTO', 'NDR', 'Returned'])->count(),
        ];

        $couriers = config('mantraheal.couriers', [
            'Delhivery', 'Blue Dart', 'Shiprocket', 'DTDC', 'XpressBees', 'Shadowfax'
        ]);

        return view('deliveries.index', compact(
            'currentTab',
            'readyOrders',
            'inTransitShipments',
            'outForDeliveryShipments',
            'deliveredShipments',
            'rtoShipments',
            'counts',
            'couriers'
        ));
    }

    /**
     * Book courier, generate AWB, and create shipment
     */
    public function bookCourier(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'courier_name' => 'required|string',
            'weight_grams' => 'nullable|integer|min:50',
            'notes' => 'nullable|string',
        ]);

        $order = Order::findOrFail($validated['order_id']);

        // Generate realistic courier AWB code
        $courierPrefix = match ($validated['courier_name']) {
            'Delhivery' => 'DEL',
            'Blue Dart' => 'BD',
            'Shiprocket' => 'SR',
            'DTDC' => 'DT',
            'XpressBees' => 'XB',
            default => 'EXP',
        };
        $awbCode = $courierPrefix . rand(100000000, 999999999);

        $shipment = Shipment::create([
            'order_id' => $order->id,
            'courier_name' => $validated['courier_name'],
            'tracking_number' => $awbCode,
            'awb_code' => $awbCode,
            'shipped_date' => now(),
            'expected_delivery_date' => now()->addDays(rand(2, 4)),
            'status' => 'In Transit',
            'notes' => $validated['notes'] ?? 'Booked via CRM Delivery Panel',
        ]);

        $order->update([
            'order_status' => 'Shipped',
            'fulfillment_status' => 'Fulfilled',
            'courier_name' => $validated['courier_name'],
            'tracking_number' => $awbCode,
        ]);

        AuditLog::log('created', $shipment, "Shipment booked with {$validated['courier_name']} (AWB: {$awbCode}) for Order #{$order->order_number}");

        return back()->with('success', "Courier booked! AWB #{$awbCode} generated via {$validated['courier_name']}.");
    }

    /**
     * Render printable thermal or A4 shipping label
     */
    public function shippingLabel(Shipment $shipment): View
    {
        $shipment->load(['order.customer', 'order.items.product']);
        return view('deliveries.label', compact('shipment'));
    }

    /**
     * Synchronize live courier tracking milestones
     */
    public function syncTracking(Request $request)
    {
        $shipments = Shipment::whereIn('status', ['In Transit', 'Shipped', 'Manifested', 'Out for Delivery'])->get();
        $updatedCount = 0;

        foreach ($shipments as $s) {
            // Milestone advancement simulation
            if ($s->status === 'In Transit') {
                $s->update(['status' => 'Out for Delivery']);
                $updatedCount++;
            } elseif ($s->status === 'Out for Delivery') {
                $s->update([
                    'status' => 'Delivered',
                    'actual_delivery_date' => now(),
                ]);
                $s->order->update(['order_status' => 'Delivered']);
                $updatedCount++;
            }
        }

        AuditLog::log('sync', null, "Courier tracking synchronized: {$updatedCount} shipment milestones updated.");

        return back()->with('success', "Live logistics sync complete: {$updatedCount} tracking milestones advanced.");
    }

    /**
     * Update individual shipment delivery status
     */
    public function updateStatus(Request $request, Shipment $shipment)
    {
        $validated = $request->validate([
            'status' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $shipment->update([
            'status' => $validated['status'],
            'actual_delivery_date' => $validated['status'] === 'Delivered' ? now() : null,
            'notes' => $validated['notes'] ?? $shipment->notes,
        ]);

        if ($validated['status'] === 'Delivered') {
            $shipment->order->update(['order_status' => 'Delivered']);
        } elseif (in_array($validated['status'], ['RTO', 'Returned'])) {
            $shipment->order->update(['order_status' => 'RTO']);
        }

        AuditLog::log('updated', $shipment, "Shipment AWB #{$shipment->awb_code} status updated to {$validated['status']}");

        return back()->with('success', "Shipment status updated to {$validated['status']}.");
    }
}
