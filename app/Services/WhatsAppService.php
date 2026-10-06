<?php

namespace App\Services;

use App\Models\CommunicationLog;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Setting;
use App\Models\WhatsAppTemplate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WhatsAppService
{
    protected ?string $apiUrl;
    protected ?string $phoneNumberId;
    protected ?string $accessToken;

    public function __construct()
    {
        $this->apiUrl = Setting::get('whatsapp_api_url', config('services.whatsapp.url', env('WHATSAPP_API_URL', 'https://graph.facebook.com/v19.0')));
        $this->phoneNumberId = Setting::get('whatsapp_phone_number_id', config('services.whatsapp.phone_number_id', env('WHATSAPP_PHONE_NUMBER_ID')));
        $this->accessToken = Setting::get('whatsapp_token', config('services.whatsapp.access_token', env('WHATSAPP_ACCESS_TOKEN')));
    }

    public function sendTemplateMessage(Customer|Lead $recipient, string $templateSlug, array $variables = []): CommunicationLog
    {
        $template = WhatsAppTemplate::where('slug', $templateSlug)->where('is_active', true)->first();
        $messageBody = $template ? $template->render($variables) : 'Hello ' . $recipient->name;

        $mobile = $recipient->whatsapp ?? $recipient->mobile;
        // Clean mobile number
        $cleanPhone = preg_replace('/[^0-9]/', '', $mobile);
        if (strlen($cleanPhone) === 10) {
            $cleanPhone = '91' . $cleanPhone;
        }

        $externalId = 'WA-' . strtoupper(Str::random(10));
        $status = 'Sent';

        // If WhatsApp credentials configured, make API call
        if ($this->phoneNumberId && $this->accessToken) {
            try {
                $response = Http::withToken($this->accessToken)
                    ->post("{$this->apiUrl}/{$this->phoneNumberId}/messages", [
                        'messaging_product' => 'whatsapp',
                        'to' => $cleanPhone,
                        'type' => 'text',
                        'text' => ['body' => $messageBody],
                    ]);

                if ($response->successful()) {
                    $externalId = $response->json('messages.0.id', $externalId);
                    $status = 'Delivered';
                } else {
                    Log::warning('WhatsApp API error', ['response' => $response->json()]);
                    $status = 'Failed';
                }
            } catch (\Exception $e) {
                Log::error('WhatsApp send exception: ' . $e->getMessage());
                $status = 'Failed';
            }
        }

        return CommunicationLog::create([
            'customer_id' => $recipient instanceof Customer ? $recipient->id : null,
            'lead_id' => $recipient instanceof Lead ? $recipient->id : null,
            'user_id' => Auth::id(),
            'channel' => 'WhatsApp',
            'template_id' => $template?->id,
            'recipient' => $mobile,
            'subject' => $template?->name ?? 'WhatsApp Message',
            'message_body' => $messageBody,
            'status' => $status,
            'external_id' => $externalId,
        ]);
    }

    public function sendCustomMessage(Customer|Lead $recipient, string $message): CommunicationLog
    {
        $mobile = $recipient->whatsapp ?? $recipient->mobile;
        return CommunicationLog::create([
            'customer_id' => $recipient instanceof Customer ? $recipient->id : null,
            'lead_id' => $recipient instanceof Lead ? $recipient->id : null,
            'user_id' => Auth::id(),
            'channel' => 'WhatsApp',
            'recipient' => $mobile,
            'subject' => 'Direct WhatsApp Message',
            'message_body' => $message,
            'status' => 'Sent',
            'external_id' => 'WA-' . strtoupper(Str::random(10)),
        ]);
    }
}
