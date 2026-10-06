<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Shipment;
use App\Models\User;
use Tests\TestCase;

class DeliveryTest extends TestCase
{
    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::where('email', 'admin@mantraheal.com')->first();
    }

    public function test_delivery_panel_loads(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('deliveries.index'));
        $response->assertStatus(200);
        $response->assertSee('Delivery & Dispatch Logistics Panel', false);
        $response->assertSee('Ready to Ship');
        $response->assertSee('In Transit');
    }

    public function test_can_book_courier_and_generate_awb(): void
    {
        $order = Order::whereNull('tracking_number')->first();
        if (!$order) {
            $order = Order::create([
                'order_number' => 'MH-ORD-TESTAWB',
                'customer_id' => 1,
                'order_date' => now(),
                'channel' => 'Website',
                'order_status' => 'Confirmed',
                'payment_status' => 'Paid',
                'payment_method' => 'Prepaid UPI',
                'shipping_name' => 'Dr. Sharma',
                'shipping_phone' => '+919876543210',
                'shipping_address_line1' => 'Plot 4, Sector 15',
                'shipping_city' => 'Gurugram',
                'shipping_state' => 'Haryana',
                'shipping_pincode' => '122001',
                'grand_total' => 1899.00,
            ]);
        }

        $payload = [
            'order_id' => $order->id,
            'courier_name' => 'Delhivery',
            'weight_grams' => 600,
            'notes' => 'Urgent Ayurvedic medicines shipment',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('deliveries.book-courier'), $payload);
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertNotNull($order->tracking_number);
        $this->assertStringStartsWith('DEL', $order->tracking_number);
        $this->assertEquals('Shipped', $order->order_status);

        $shipment = Shipment::where('order_id', $order->id)->first();
        $this->assertNotNull($shipment);
        $this->assertEquals('Delhivery', $shipment->courier_name);
        $this->assertEquals('In Transit', $shipment->status);
    }

    public function test_shipping_label_renders_printable_format(): void
    {
        $shipment = Shipment::first();
        $this->assertNotNull($shipment);

        $response = $this->actingAs($this->adminUser)->get(route('deliveries.shipping-label', $shipment->id));
        $response->assertStatus(200);
        $response->assertSee('SHIPPING LABEL');
        $response->assertSee($shipment->tracking_number);
        $response->assertSee($shipment->courier_name);
        $response->assertSee('SHIP TO:');
    }

    public function test_sync_courier_tracking_advances_milestones(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('deliveries.sync-tracking'));
        $response->assertRedirect();
        $response->assertSessionHas('success');
    }
}
