<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\MarketingCampaign;
use App\Models\MarketingSource;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MarketingSourceController extends Controller
{
    public function index()
    {
        $sources = MarketingSource::with('campaigns')->get();

        // Calculate attribution metrics
        $attributionData = [];
        foreach ($sources as $source) {
            $leadsCount = Lead::where('source', $source->name)->count();
            $wonLeads = Lead::where('source', $source->name)->where('stage', 'Won')->count();
            $orders = Order::where('channel', $source->name)->get();
            $orderCount = $orders->count();
            $revenue = $orders->sum('grand_total');
            $conversionRate = $leadsCount > 0 ? round(($wonLeads / $leadsCount) * 100, 1) : ($orderCount > 0 ? 100 : 0);

            $attributionData[] = [
                'source' => $source,
                'leads' => $leadsCount,
                'orders' => $orderCount,
                'revenue' => $revenue,
                'conversion_rate' => $conversionRate,
            ];
        }

        $campaigns = MarketingCampaign::with('source')->latest()->get();

        return view('marketing.sources', compact('attributionData', 'campaigns', 'sources'));
    }

    public function storeSource(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:marketing_sources,name',
            'channel_type' => 'required|string',
        ]);

        $validated['code'] = Str::slug($validated['name']);
        $validated['is_active'] = true;

        $source = MarketingSource::create($validated);
        AuditLog::log('created', $source, "Marketing Source {$source->name} created");

        return back()->with('success', 'Marketing source added.');
    }

    public function storeCampaign(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'marketing_source_id' => 'required|exists:marketing_sources,id',
            'budget' => 'required|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        $validated['code'] = 'CAMP-' . strtoupper(Str::random(6));
        $validated['is_active'] = true;

        $campaign = MarketingCampaign::create($validated);
        AuditLog::log('created', $campaign, "Marketing Campaign {$campaign->name} created");

        return back()->with('success', 'Campaign created successfully.');
    }
}
