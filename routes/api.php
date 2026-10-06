<?php

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\MetaLeadSyncController;
use Illuminate\Support\Facades\Route;

// External webhook endpoints (Shopify, Shiprocket, Telephony, Meta Ads)
Route::post('shopify/orders', [ApiController::class, 'shopifyWebhook'])->name('api.shopify.webhook');
Route::post('shopify/orders-create', [ApiController::class, 'shopifyWebhook'])->name('api.shopify.orders-create');
Route::post('shiprocket/tracking', [ApiController::class, 'shiprocketWebhook'])->name('api.shiprocket.webhook');
Route::post('telephony/recording-callback', [ApiController::class, 'telephonyCallback'])->name('api.telephony.recording-callback');

// Meta Ads (Facebook & Instagram) LeadGen Webhook
Route::get('meta/leadgen-webhook', [MetaLeadSyncController::class, 'verifyWebhook'])->name('api.meta.leadgen-webhook.verify');
Route::post('meta/leadgen-webhook', [MetaLeadSyncController::class, 'handleWebhook'])->name('api.meta.leadgen-webhook');

// Marketing lead capture API
Route::post('leads/capture', [ApiController::class, 'captureLead'])->name('api.leads.capture');

// Webhook simulation & test endpoint for Settings panel
Route::post('webhooks/simulate', [ApiController::class, 'simulateWebhook'])->name('api.webhooks.simulate');
