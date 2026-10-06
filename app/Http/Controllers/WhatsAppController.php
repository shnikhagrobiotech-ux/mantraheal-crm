<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\CommunicationLog;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\WhatsAppTemplate;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class WhatsAppController extends Controller
{
    public function __construct(
        protected WhatsAppService $whatsAppService
    ) {}

    public function index(Request $request)
    {
        $templates = WhatsAppTemplate::all();
        $logs = CommunicationLog::with(['customer', 'lead', 'template', 'user'])
            ->where('channel', 'WhatsApp')
            ->latest()
            ->paginate(15);

        $customers = Customer::orderBy('name')->get();
        $leads = Lead::orderBy('name')->get();

        return view('communication.whatsapp', compact('templates', 'logs', 'customers', 'leads'));
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'recipient_type' => 'required|in:customer,lead',
            'recipient_id' => 'required|integer',
            'template_slug' => 'nullable|string',
            'custom_message' => 'nullable|string',
        ]);

        $recipient = $validated['recipient_type'] === 'customer'
            ? Customer::findOrFail($validated['recipient_id'])
            : Lead::findOrFail($validated['recipient_id']);

        if (!empty($validated['template_slug'])) {
            $variables = [
                'name' => $recipient->name,
                'company_name' => config('mantraheal.company_name', 'MantraHeal'),
                'order_number' => $request->input('order_number', 'MH-ORD-Sample'),
                'amount' => $request->input('amount', '₹1,499'),
                'tracking_url' => 'https://shiprocket.co/tracking/' . ($request->input('tracking_number', 'MH123456')),
            ];

            $log = $this->whatsAppService->sendTemplateMessage($recipient, $validated['template_slug'], $variables);
        } else {
            $message = $validated['custom_message'] ?? 'Hello from ' . config('mantraheal.company_name', 'MantraHeal');
            $log = $this->whatsAppService->sendCustomMessage($recipient, $message);
        }

        AuditLog::log('whatsapp_sent', $recipient, "WhatsApp message sent to {$recipient->name} ({$log->status})");

        return back()->with('success', 'WhatsApp message dispatched successfully.');
    }
}
