<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Services\ShopifySyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopifySyncController extends Controller
{
    public function __construct(
        protected ShopifySyncService $syncService
    ) {}

    /**
     * Display Shopify Synchronization Hub
     */
    public function index(): View
    {
        $stats = $this->syncService->getSyncStats();
        $products = Product::where('is_active', true)->take(12)->get();
        $unfulfilledShopifyOrders = Order::where(function ($q) {
            $q->whereNotNull('shopify_order_id')->orWhere('channel', 'Shopify');
        })->whereIn('order_status', ['Confirmed', 'Processing', 'Packed'])->latest()->get();

        return view('shopify.sync', compact('stats', 'products', 'unfulfilledShopifyOrders'));
    }

    /**
     * Trigger manual or automated order pull from Shopify
     */
    public function pullOrders(Request $request)
    {
        $limit = (int) $request->input('limit', 5);
        $result = $this->syncService->pullOrders($limit);

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return back()->with('success', "Shopify order sync complete: {$result['imported_count']} new orders imported, {$result['updated_count']} updated.");
    }

    /**
     * Push shipment tracking details to Shopify for an order
     */
    public function pushFulfillment(Request $request, Order $order)
    {
        $result = $this->syncService->pushFulfillment($order);

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return back()->with('success', $result['message']);
    }

    /**
     * Push inventory stock adjustment to Shopify
     */
    public function pushInventory(Request $request, Product $product)
    {
        $quantity = (int) $request->input('stock', $product->total_stock);
        $result = $this->syncService->pushInventory($product, $quantity);

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return back()->with('success', $result['message']);
    }
}
