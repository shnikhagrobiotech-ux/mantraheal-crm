<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Tests\TestCase;

class SettingTest extends TestCase
{
    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::where('email', 'admin@mantraheal.com')->first() ?? User::factory()->create();
    }

    public function test_unauthenticated_user_cannot_access_settings(): void
    {
        $response = $this->get('/settings');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_settings(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/settings');
        $response->assertStatus(200);
        $response->assertSee('System & Integration Settings', false);
        $response->assertSee('Company Branding');
        $response->assertSee('Meta WhatsApp Business Cloud API');
        $response->assertSee('Shopify E-Commerce Store Integration');
        $response->assertSee('Razorpay Payment Gateway');
    }

    public function test_user_can_update_settings(): void
    {
        $response = $this->actingAs($this->adminUser)->post('/settings', [
            'company_name' => 'MantraHeal Ayurveda Pvt Ltd',
            'support_email' => 'care@mantraheal.com',
            'razorpay_mode' => 'test',
            'default_gst' => '12',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('MantraHeal Ayurveda Pvt Ltd', Setting::get('company_name'));
        $this->assertEquals('care@mantraheal.com', Setting::get('support_email'));
    }

    public function test_test_integration_endpoint_validates_services(): void
    {
        // WhatsApp ping simulation
        $waResponse = $this->actingAs($this->adminUser)->postJson('/settings/test-integration', [
            'service' => 'whatsapp',
            'whatsapp_phone_number_id' => '109283746501928',
            'whatsapp_token' => 'EAABwzLixnjYBA...',
        ]);
        $waResponse->assertStatus(200)->assertJson(['success' => true]);

        // Razorpay ping simulation
        $rzpResponse = $this->actingAs($this->adminUser)->postJson('/settings/test-integration', [
            'service' => 'razorpay',
            'razorpay_key_id' => 'rzp_test_1234567890',
            'razorpay_mode' => 'test',
        ]);
        $rzpResponse->assertStatus(200)->assertJson(['success' => true]);

        // Shopify ping simulation
        $shopifyResponse = $this->actingAs($this->adminUser)->postJson('/settings/test-integration', [
            'service' => 'shopify',
            'shopify_shop_url' => 'mantraheal.myshopify.com',
        ]);
        $shopifyResponse->assertStatus(200)->assertJson(['success' => true]);
    }
}
