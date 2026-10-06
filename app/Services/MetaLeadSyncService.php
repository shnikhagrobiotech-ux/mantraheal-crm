<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\MarketingCampaign;
use App\Models\MarketingSource;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MetaLeadSyncService
{
    /**
     * Handle Meta Webhook Verification handshake (GET request)
     */
    public function verifyWebhook(Request $request): ?string
    {
        $mode = $request->query('hub_mode', $request->query('hub.mode'));
        $token = $request->query('hub_verify_token', $request->query('hub.verify_token'));
        $challenge = $request->query('hub_challenge', $request->query('hub.challenge'));

        $configuredToken = Setting::get('meta_verify_token', 'mantraheal_meta_token_2026');

        if ($mode === 'subscribe' && $token === $configuredToken) {
            return (string) $challenge;
        }

        return null;
    }

    /**
     * Process incoming Meta LeadGen webhook payload
     */
    public function handleWebhookPayload(array $payload): array
    {
        $results = [];
        $entries = $payload['entry'] ?? [];

        foreach ($entries as $entry) {
            $changes = $entry['changes'] ?? [];
            foreach ($changes as $change) {
                if (($change['field'] ?? '') === 'leadgen') {
                    $val = $change['value'] ?? [];
                    $leadgenId = (string) ($val['leadgen_id'] ?? '');

                    if ($leadgenId) {
                        $metaData = $this->fetchLeadFromMetaGraph($leadgenId, $val);
                        $lead = $this->ingestMetaLead($metaData);
                        if ($lead) {
                            $results[] = $lead;
                        }
                    }
                }
            }
        }

        return $results;
    }

    /**
     * Fetch lead details from Meta Graph API or fallback to simulated payload
     */
    public function fetchLeadFromMetaGraph(string $leadgenId, array $fallbackContext = []): array
    {
        $accessToken = Setting::get('meta_access_token');

        if ($accessToken && !str_starts_with($accessToken, 'mock_')) {
            try {
                $response = Http::timeout(5)->get("https://graph.facebook.com/v19.0/{$leadgenId}", [
                    'access_token' => $accessToken,
                    'fields' => 'id,created_time,ad_id,ad_name,campaign_id,campaign_name,form_id,field_data',
                ]);

                if ($response->successful()) {
                    return $response->json();
                }
            } catch (\Exception $e) {
                Log::warning("Meta Graph API fetch failed for leadgen_id {$leadgenId}: " . $e->getMessage());
            }
        }

        // Offline / Simulation fallback data generator
        return [
            'id' => $leadgenId,
            'created_time' => now()->toISOString(),
            'ad_id' => $fallbackContext['ad_id'] ?? ('AD-' . rand(100000, 999999)),
            'ad_name' => $fallbackContext['ad_name'] ?? 'Ayurvedic Wellness Video Reel #4',
            'campaign_id' => $fallbackContext['campaign_id'] ?? 'CAMP-META-2026',
            'campaign_name' => $fallbackContext['campaign_name'] ?? 'Summer Vitality & Stamina Drive',
            'form_id' => $fallbackContext['form_id'] ?? ('FORM-' . rand(10000, 99999)),
            'field_data' => [
                ['name' => 'full_name', 'values' => [$fallbackContext['name'] ?? 'Deepak Malhotra']],
                ['name' => 'phone_number', 'values' => [$fallbackContext['phone'] ?? '+91 98711 23456']],
                ['name' => 'email', 'values' => [$fallbackContext['email'] ?? 'deepak.malhotra@example.com']],
                ['name' => 'city', 'values' => [$fallbackContext['city'] ?? 'New Delhi']],
                ['name' => 'state', 'values' => [$fallbackContext['state'] ?? 'Delhi']],
                ['name' => 'health_concern', 'values' => ['Chronic Stress & Low Stamina']],
            ],
        ];
    }

    /**
     * Ingest parsed Meta Lead into Leads table
     */
    public function ingestMetaLead(array $data): ?Lead
    {
        $metaLeadId = (string) ($data['id'] ?? '');

        // Prevent duplicate ingestion of same Meta Lead ID
        if ($metaLeadId) {
            $existing = Lead::where('meta_lead_id', $metaLeadId)->first();
            if ($existing) {
                return $existing;
            }
        }

        // Parse fields from field_data
        $fields = [];
        foreach ($data['field_data'] ?? [] as $fd) {
            $fieldName = strtolower(trim($fd['name'] ?? ''));
            $fieldVal = trim($fd['values'][0] ?? '');

            if (in_array($fieldName, ['full_name', 'name', 'first_name'])) {
                $fields['name'] = $fieldVal;
            } elseif (in_array($fieldName, ['phone_number', 'phone', 'mobile'])) {
                $fields['mobile'] = $fieldVal;
            } elseif (in_array($fieldName, ['email', 'email_address'])) {
                $fields['email'] = $fieldVal;
            } elseif ($fieldName === 'city') {
                $fields['city'] = $fieldVal;
            } elseif ($fieldName === 'state') {
                $fields['state'] = $fieldVal;
            } elseif (in_array($fieldName, ['pincode', 'zip_code', 'postal_code'])) {
                $fields['pincode'] = substr($fieldVal, 0, 10);
            } else {
                // Custom form questions
                $fields['custom'][$fd['name']] = $fieldVal;
            }
        }

        $name = $fields['name'] ?? 'Meta Prospect';
        $mobile = $fields['mobile'] ?? '+91 98100 00000';
        $email = $fields['email'] ?? null;
        $city = $fields['city'] ?? null;
        $state = $fields['state'] ?? null;
        $pincode = $fields['pincode'] ?? null;

        // Clean phone digits
        $cleanDigits = preg_replace('/[^0-9]/', '', $mobile);
        $searchDigits = strlen($cleanDigits) > 10 ? substr($cleanDigits, -10) : $cleanDigits;

        // Check for existing lead by phone
        $existingLead = Lead::whereRaw("REPLACE(REPLACE(REPLACE(mobile, ' ', ''), '-', ''), '+', '') LIKE ?", ["%{$searchDigits}%"])
            ->orWhere('mobile', 'like', "%{$searchDigits}%")
            ->first();

        $campaignName = $data['campaign_name'] ?? 'Meta Lead Ads';
        $adName = $data['ad_name'] ?? null;
        $formId = $data['form_id'] ?? null;

        $notes = "Captured from Meta Ads (Facebook/Instagram).\nCampaign: {$campaignName}";
        if ($adName) $notes .= "\nAd: {$adName}";
        if (!empty($fields['custom'])) {
            $notes .= "\nForm Inquiries: " . json_encode($fields['custom'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        // Link with MarketingCampaign if available
        $source = MarketingSource::where('name', 'Meta Ads')->first();
        if ($source && $campaignName) {
            MarketingCampaign::firstOrCreate(
                ['code' => Str::slug($campaignName)],
                [
                    'name' => $campaignName,
                    'marketing_source_id' => $source->id,
                    'budget' => 25000.00,
                    'is_active' => true,
                ]
            );
        }

        // Round-robin sales rep assignment
        $salesReps = User::whereIn('role_slug', ['sales_executive', 'sales_manager'])->pluck('id')->toArray();
        $assignedId = null;
        if (!empty($salesReps)) {
            $assignedId = $salesReps[array_rand($salesReps)];
        }

        if ($existingLead) {
            // Update existing lead with Meta context
            $existingLead->update([
                'meta_lead_id' => $metaLeadId ?: $existingLead->meta_lead_id,
                'meta_form_id' => $formId ?: $existingLead->meta_form_id,
                'meta_campaign_name' => $campaignName ?: $existingLead->meta_campaign_name,
                'meta_ad_name' => $adName ?: $existingLead->meta_ad_name,
                'notes' => $existingLead->notes . "\n\n[Meta Lead Ads Re-inquiry]\n" . $notes,
            ]);

            LeadActivity::create([
                'lead_id' => $existingLead->id,
                'type' => 'ad_lead',
                'description' => "Re-inquired via Meta Ads campaign '{$campaignName}' (Form: {$formId}).",
            ]);

            return $existingLead;
        }

        // Create new lead
        $lead = Lead::create([
            'name' => $name,
            'mobile' => $mobile,
            'email' => $email,
            'city' => $city,
            'state' => $state,
            'pincode' => $pincode,
            'source' => 'Meta Ads',
            'stage' => Setting::get('meta_default_stage', 'New'),
            'estimated_value' => 1999.00,
            'assigned_user_id' => $assignedId,
            'notes' => $notes,
            'meta_lead_id' => $metaLeadId,
            'meta_form_id' => $formId,
            'meta_campaign_name' => $campaignName,
            'meta_ad_name' => $adName,
        ]);

        LeadActivity::create([
            'lead_id' => $lead->id,
            'type' => 'ad_lead',
            'description' => "Instant Lead captured via Meta Ads campaign '{$campaignName}' (Ad: {$adName}).",
        ]);

        AuditLog::log('created', $lead, "Meta Lead {$lead->name} imported from ad '{$adName}'");

        return $lead;
    }

    /**
     * Generate and ingest a live simulated Meta Lead for demo/testing
     */
    public function generateSimulatedLead(): Lead
    {
        $leadgenId = 'META-LEAD-' . strtoupper(Str::random(8));

        $samples = [
            [
                'name' => 'Meera Krishnamurthy',
                'phone' => '+91 98450 ' . rand(10000, 99999),
                'email' => 'meera.k' . rand(10, 99) . '@gmail.com',
                'city' => 'Bengaluru',
                'state' => 'Karnataka',
                'campaign' => 'Summer Vitality & Stamina Drive',
                'ad' => 'KSM-66 Stress Relief Story Ad #2',
                'concern' => 'Sleep Quality & Mental Fatigue',
            ],
            [
                'name' => 'Rohan Vardhan',
                'phone' => '+91 97110 ' . rand(10000, 99999),
                'email' => 'rohan.v' . rand(10, 99) . '@gmail.com',
                'city' => 'Pune',
                'state' => 'Maharashtra',
                'campaign' => 'Pure Himalayan Shilajit Launch',
                'ad' => 'Doctor Recommended Shilajit Resin Carousel',
                'concern' => 'Energy Levels & Athletic Stamina',
            ],
            [
                'name' => 'Ananya Sen',
                'phone' => '+91 98300 ' . rand(10000, 99999),
                'email' => 'ananya.sen' . rand(10, 99) . '@gmail.com',
                'city' => 'Kolkata',
                'state' => 'West Bengal',
                'campaign' => 'Ayurvedic Detox & Immunity Drive',
                'ad' => 'Triphala & Giloy Natural Cleanse Video',
                'concern' => 'Digestive Health & Metabolism',
            ],
        ];

        $sample = $samples[array_rand($samples)];

        $payload = [
            'id' => $leadgenId,
            'created_time' => now()->toISOString(),
            'ad_id' => 'AD-' . rand(100000, 999999),
            'ad_name' => $sample['ad'],
            'campaign_id' => 'CAMP-' . rand(1000, 9999),
            'campaign_name' => $sample['campaign'],
            'form_id' => 'FORM-' . rand(10000, 99999),
            'field_data' => [
                ['name' => 'full_name', 'values' => [$sample['name']]],
                ['name' => 'phone_number', 'values' => [$sample['phone']]],
                ['name' => 'email', 'values' => [$sample['email']]],
                ['name' => 'city', 'values' => [$sample['city']]],
                ['name' => 'state', 'values' => [$sample['state']]],
                ['name' => 'health_concern', 'values' => [$sample['concern']]],
            ],
        ];

        return $this->ingestMetaLead($payload);
    }
}
