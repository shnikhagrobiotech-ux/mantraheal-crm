<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\SalesCall;
use App\Models\User;
use Tests\TestCase;

class DialerTest extends TestCase
{
    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::where('email', 'admin@mantraheal.com')->first();
    }

    public function test_dialer_contact_lookup_matches_customer(): void
    {
        $customer = Customer::first();
        $this->assertNotNull($customer);

        $phone = preg_replace('/[^0-9]/', '', $customer->mobile);
        $response = $this->actingAs($this->adminUser)->getJson('/dialer/lookup?phone=' . $phone);

        $response->assertStatus(200);
        $this->assertNotNull($response->json('match'));
        $this->assertEquals('customer', $response->json('match.type'));
        $this->assertEquals($customer->name, $response->json('match.name'));
    }

    public function test_dialer_start_call_initiates_session(): void
    {
        $response = $this->actingAs($this->adminUser)->postJson('/dialer/start-call', [
            'phone' => '+91 98100 12345',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'initiated']);
        $this->assertNotNull($response->json('session_id'));
    }

    public function test_dialer_end_call_creates_sales_call_and_recording(): void
    {
        $customer = Customer::first();

        $response = $this->actingAs($this->adminUser)->postJson('/dialer/end-call', [
            'phone' => $customer->mobile,
            'duration_seconds' => 145,
            'outcome' => 'Order Taken',
            'notes' => 'Customer requested Ashwagandha bundle COD',
            'customer_id' => $customer->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $callId = $response->json('sales_call_id');
        $this->assertNotNull($callId);

        $call = SalesCall::find($callId);
        $this->assertNotNull($call);
        $this->assertEquals('Order Taken', $call->outcome);
        $this->assertEquals(145, $call->duration_seconds);
        $this->assertEquals($customer->id, $call->customer_id);

        // Recording sample should be created
        $this->assertNotNull($call->recording);
        $this->assertEquals(145, $call->recording->duration_seconds);
    }
}
