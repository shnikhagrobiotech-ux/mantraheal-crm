<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\ShopifySyncService;
use Tests\TestCase;

class ShopifySyncTest extends TestCase
{
    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::where('email', 'admin@mantraheal.com')->first();
    }

    public function test_shopify_sync_dashboard_loads(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('shopify.sync.index'));
        $response->assertStatus(200);
        $response->assertSee('Shopify Active Synchronization Hub');
        $response->assertSee('Synced Shopify Orders');
        $response->assertSee('Pull Shopify Orders Now');
    }

    public function test_pull_orders_imports_orders(): void
    {
        $response = $this->actingAs($this->adminUser)->postJson(route('shopify.sync.pull-orders'), [
            'limit' => 3,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);
        $this->assertGreaterThan(0, $response->json('imported_count'));

        $shopifyOrder = Order::where('channel', 'Shopify')->latest()->first();
        $this->assertNotNull($shopifyOrder);
        $this->assertNotNull($shopifyOrder->shopify_order_id);
    }

    public function test_push_fulfillment_to_shopify(): void
    {
        $order = Order::where('channel', 'Shopify')->first();
        if (!$order) {
            app(ShopifySyncService::class)->pullOrders(1);
            $order = Order::where('channel', 'Shopify')->first();
        }

        $order->update(['tracking_number' => 'DEL987654321', 'courier_name' => 'Delhivery']);

        $response = $this->actingAs($this->adminUser)->postJson(route('shopify.sync.push-fulfillment', $order->id));
        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);
    }

    public function test_push_inventory_to_shopify(): void
    {
        $product = Product::first();
        $this->assertNotNull($product);

        $response = $this->actingAs($this->adminUser)->postJson(route('shopify.sync.push-inventory', $product->id), [
            'stock' => 150,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);
    }
}
