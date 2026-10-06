<?php

namespace Tests\Feature;

use App\Helpers\NumberToWords;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Tests\TestCase;

class OrderInvoiceTest extends TestCase
{
    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::where('email', 'admin@mantraheal.com')->first();
    }

    public function test_order_creation_page_loads_successfully(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('orders.create'));
        $response->assertStatus(200);
        $response->assertSee('Create New Sales Order');
        $response->assertSee('orderForm()');
    }

    public function test_can_create_order_with_items_and_gst(): void
    {
        $customer = Customer::first();
        $product = Product::first();

        $payload = [
            'customer_id' => $customer->id,
            'order_date' => now()->format('Y-m-d'),
            'channel' => 'direct',
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'order_status' => 'confirmed',
            'discount_amount' => 50,
            'shipping_charge' => 70,
            'shipping_address_line1' => 'Plot 10, Sector 29',
            'shipping_city' => 'Gurugram',
            'shipping_state' => 'Haryana',
            'shipping_pincode' => '122001',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'unit_price' => $product->base_price ?: 999,
                ]
            ]
        ];

        $response = $this->actingAs($this->adminUser)->post(route('orders.store'), $payload);
        $response->assertRedirect();

        $latestOrder = Order::latest()->first();
        $this->assertNotNull($latestOrder);
        $this->assertEquals($customer->id, $latestOrder->customer_id);
        $this->assertGreaterThan(0, $latestOrder->grand_total);
    }

    public function test_gst_tax_invoice_renders_with_breakdown_and_words(): void
    {
        $order = Order::first();
        $this->assertNotNull($order);

        $response = $this->actingAs($this->adminUser)->get(route('orders.invoice', $order->id));
        $response->assertStatus(200);
        $response->assertSee('TAX INVOICE');
        $response->assertSee('INV-' . $order->order_number);
        $response->assertSee('Invoice Amount in Words:');
        $response->assertSee('Bank Transfer');
        $response->assertSee('Place of Supply');
    }

    public function test_number_to_words_converts_properly(): void
    {
        $words = NumberToWords::convert(1250.50);
        $this->assertEquals('Rupees One Thousand Two Hundred Fifty and Fifty Paise Only', $words);

        $wordsZero = NumberToWords::convert(0);
        $this->assertEquals('Zero Rupees Only', $wordsZero);
    }
}
