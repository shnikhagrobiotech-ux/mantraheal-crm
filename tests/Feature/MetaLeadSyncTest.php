<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Setting;
use App\Models\User;
use Tests\TestCase;

class MetaLeadSyncTest extends TestCase
{
    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::where('email', 'admin@mantraheal.com')->first();
    }

    public function test_meta_sync_dashboard_loads(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('meta.sync.index'));
        $response->assertStatus(200);
        $response->assertSee('Meta Ads', false);
        $response->assertSee('Total Meta Leads');
        $response->assertSee('Simulate Incoming Meta Lead');
    }

    public function test_meta_webhook_verification_handshake(): void
    {
        $token = Setting::get('meta_verify_token', 'mantraheal_meta_token_2026');
        $challenge = 'CHALLENGE_' . rand(100000, 999999);

        $response = $this->get("/api/meta/leadgen-webhook?hub.mode=subscribe&hub.verify_token={$token}&hub.challenge={$challenge}");

        $response->assertStatus(200);
        $this->assertEquals($challenge, $response->getContent());
    }

    public function test_meta_webhook_verification_rejects_invalid_token(): void
    {
        $response = $this->get('/api/meta/leadgen-webhook?hub.mode=subscribe&hub.verify_token=wrong_token&hub.challenge=12345');
        $response->assertStatus(403);
    }

    public function test_meta_webhook_post_ingests_lead(): void
    {
        $uniqueLeadgenId = 'META-' . rand(10000000, 99999999);

        $payload = [
            'object' => 'page',
            'entry' => [
                [
                    'id' => '10492837482',
                    'time' => time(),
                    'changes' => [
                        [
                            'field' => 'leadgen',
                            'value' => [
                                'leadgen_id' => $uniqueLeadgenId,
                                'page_id' => '10492837482',
                                'form_id' => '984729184729',
                                'ad_id' => '83719482910',
                                'created_time' => time(),
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->postJson('/api/meta/leadgen-webhook', $payload);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success', 'processed' => 1]);

        $lead = Lead::where('meta_lead_id', $uniqueLeadgenId)->first();
        $this->assertNotNull($lead);
        $this->assertEquals('Meta Ads', $lead->source);
        $this->assertNotNull($lead->name);
        $this->assertNotNull($lead->mobile);
    }

    public function test_meta_simulation_creates_lead(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('meta.sync.simulate'));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $latestMetaLead = Lead::where('source', 'Meta Ads')->latest()->first();
        $this->assertNotNull($latestMetaLead);
        $this->assertNotNull($latestMetaLead->meta_lead_id);
    }
}
