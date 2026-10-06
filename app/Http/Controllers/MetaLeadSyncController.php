<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Lead;
use App\Models\MarketingCampaign;
use App\Models\MarketingSource;
use App\Models\Setting;
use App\Models\User;
use App\Services\MetaLeadSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MetaLeadSyncController extends Controller
{
    public function __construct(
        protected MetaLeadSyncService $metaService
    ) {}

    /**
     * Webhook verification endpoint for Meta Developer Portal (GET)
     */
    public function verifyWebhook(Request $request): Response
    {
        $challenge = $this->metaService->verifyWebhook($request);

        if ($challenge !== null) {
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        return response('Forbidden', 403);
    }

    /**
     * Webhook receiver endpoint for Meta LeadGen notifications (POST)
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        $payload = $request->all();
        $leads = $this->metaService->handleWebhookPayload($payload);

        return response()->json([
            'status' => 'success',
            'processed' => count($leads),
        ]);
    }

    /**
     * Meta Ads Sync Hub Dashboard view
     */
    public function index()
    {
        $totalMetaLeads = Lead::where('source', 'Meta Ads')->count();
        $syncedToday = Lead::where('source', 'Meta Ads')->whereDate('created_at', today())->count();
        $convertedMetaLeads = Lead::where('source', 'Meta Ads')->where('stage', 'Won')->count();
        $conversionRate = $totalMetaLeads > 0 ? round(($convertedMetaLeads / $totalMetaLeads) * 100, 1) : 0;

        $campaignsCount = MarketingCampaign::whereHas('source', function ($q) {
            $q->where('name', 'Meta Ads');
        })->count();

        $metaLeads = Lead::where('source', 'Meta Ads')
            ->with('assignedUser')
            ->latest()
            ->paginate(15);

        $stages = config('mantraheal.lead_stages', ['New', 'Contacted', 'Interested', 'Follow-up', 'Quotation Sent', 'Won', 'Lost']);
        $salesExecutives = User::whereIn('role_slug', ['sales_executive', 'sales_manager'])->get();

        $settings = [
            'meta_page_id' => Setting::get('meta_page_id', '10492837482'),
            'meta_access_token' => Setting::get('meta_access_token', 'EAAXx...'),
            'meta_app_secret' => Setting::get('meta_app_secret', '••••••••••••••••'),
            'meta_verify_token' => Setting::get('meta_verify_token', 'mantraheal_meta_token_2026'),
            'meta_default_stage' => Setting::get('meta_default_stage', 'New'),
        ];

        $webhookUrl = url('api/meta/leadgen-webhook');

        return view('meta.sync', compact(
            'totalMetaLeads',
            'syncedToday',
            'conversionRate',
            'campaignsCount',
            'metaLeads',
            'stages',
            'salesExecutives',
            'settings',
            'webhookUrl'
        ));
    }

    /**
     * Save Meta Ads integration settings
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'meta_page_id' => 'nullable|string|max:100',
            'meta_access_token' => 'nullable|string',
            'meta_app_secret' => 'nullable|string|max:100',
            'meta_verify_token' => 'required|string|max:100',
            'meta_default_stage' => 'required|string|max:50',
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value);
        }

        AuditLog::log('updated', null, 'Meta Ads integration configuration updated');

        return back()->with('success', 'Meta Ads integration settings saved successfully.');
    }

    /**
     * Trigger 1-click test simulation of an incoming Meta Lead
     */
    public function simulateLead()
    {
        $lead = $this->metaService->generateSimulatedLead();

        return back()->with('success', "Simulated Meta Lead captured: {$lead->name} ({$lead->mobile}) from campaign '{$lead->meta_campaign_name}'!");
    }
}
