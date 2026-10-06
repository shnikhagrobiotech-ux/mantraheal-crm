<?php

namespace Tests\Feature;

use App\Models\CallRecording;
use App\Models\SalesCall;
use App\Models\User;
use Tests\TestCase;

class WebhookTest extends TestCase
{
    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::where('email', 'admin@mantraheal.com')->first();
    }

    public function test_shopify_simulation_webhook_creates_order(): void
    {
        $response = $this->actingAs($this->adminUser)->postJson('/api/webhooks/simulate', [
            'type' => 'shopify',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'status',
            'order_id',
        ]);
        $this->assertEquals('success', $response->json('status'));
    }

    public function test_shiprocket_simulation_webhook_updates_status(): void
    {
        $response = $this->actingAs($this->adminUser)->postJson('/api/webhooks/simulate', [
            'type' => 'shiprocket',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('success', $response->json('status'));
    }

    public function test_telephony_simulation_logs_call_and_recording(): void
    {
        $response = $this->actingAs($this->adminUser)->postJson('/api/webhooks/simulate', [
            'type' => 'telephony',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('success', $response->json('status'));
        $this->assertNotNull($response->json('sales_call_id'));
        $this->assertNotNull($response->json('recording_id'));

        $salesCall = SalesCall::find($response->json('sales_call_id'));
        $this->assertNotNull($salesCall);
        $this->assertEquals('Connected', $salesCall->outcome);
    }

    public function test_telephony_recording_callback_endpoint(): void
    {
        $payload = [
            'From' => '+919876543210',
            'To' => '+918047192830',
            'DialCallStatus' => 'completed',
            'DialCallDuration' => 95,
            'RecordingUrl' => 'https://api.telephony.test/sample.wav',
        ];

        $response = $this->postJson('/api/telephony/recording-callback', $payload);
        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);
    }

    public function test_call_recording_playback_serves_audio(): void
    {
        $recording = CallRecording::first();
        if (!$recording) {
            $recording = CallRecording::create([
                'sales_call_id' => SalesCall::first()?->id ?? 1,
                'file_name' => 'sample_call.wav',
                'file_path' => 'call-recordings/sample_call.wav',
                'mime_type' => 'audio/wav',
                'file_size' => 48044,
                'duration_seconds' => 120,
            ]);
        }

        $response = $this->actingAs($this->adminUser)->get(route('call-recordings.play', $recording->id));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'audio/wav');
    }
}
