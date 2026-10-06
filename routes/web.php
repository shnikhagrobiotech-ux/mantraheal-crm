<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\CallRecordingController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\DialerController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\FollowUpController;
use App\Http\Controllers\GoodsReceivedNoteController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LeadImportController;
use App\Http\Controllers\MarketingSourceController;
use App\Http\Controllers\MetaLeadSyncController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\RefundController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\RtoController;
use App\Http\Controllers\SalesCallController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ShopifySyncController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\WhatsAppController;
use Illuminate\Support\Facades\Route;

// Public Authentication Routes
Route::get('login', [AuthController::class, 'showLogin'])->name('login');
Route::post('login', [AuthController::class, 'login'])->name('login.post');
Route::post('logout', [AuthController::class, 'logout'])->name('logout');

// Protected CRM Application Routes
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('profile', [AuthController::class, 'profile'])->name('profile');
    Route::post('profile', [AuthController::class, 'updateProfile'])->name('profile.update');

    // Customers & Customer 360
    Route::get('customers/export', [CustomerController::class, 'exportCsv'])->name('customers.export');
    Route::resource('customers', CustomerController::class);

    // Leads & Pipeline
    Route::get('leads/import', [LeadImportController::class, 'index'])->name('leads.import');
    Route::get('leads/import/template', [LeadImportController::class, 'downloadTemplate'])->name('leads.import.template');
    Route::post('leads/import', [LeadImportController::class, 'import'])->name('leads.import.process');
    Route::get('leads/export', [LeadController::class, 'exportCsv'])->name('leads.export');
    Route::post('leads/{lead}/convert', [LeadController::class, 'convert'])->name('leads.convert');
    Route::post('leads/{lead}/note', [LeadController::class, 'addNote'])->name('leads.note');
    Route::resource('leads', LeadController::class);

    // Sales Calls & Audio Recordings
    Route::resource('calls', SalesCallController::class)->only(['index', 'create', 'store']);
    Route::get('call-recordings', [CallRecordingController::class, 'index'])->name('call-recordings.index');
    Route::get('call-recordings/{recording}/play', [CallRecordingController::class, 'play'])->name('call-recordings.play');

    // Follow-ups
    Route::get('followups', [FollowUpController::class, 'index'])->name('followups.index');
    Route::post('followups', [FollowUpController::class, 'store'])->name('followups.store');
    Route::post('followups/{followup}/complete', [FollowUpController::class, 'complete'])->name('followups.complete');
    Route::post('followups/{followup}/reschedule', [FollowUpController::class, 'reschedule'])->name('followups.reschedule');

    // Orders, Invoicing & Payments
    Route::get('orders/export', [OrderController::class, 'exportCsv'])->name('orders.export');
    Route::get('orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');
    Route::post('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::post('orders/{order}/payment', [OrderController::class, 'recordPayment'])->name('orders.record-payment');
    Route::resource('orders', OrderController::class);

    // Products & Categories
    Route::resource('products', ProductController::class);
    Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);

    // Inventory, Multi-Warehouse, Stock Ledger & Batches
    Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('inventory/ledger', [InventoryController::class, 'ledger'])->name('inventory.ledger');
    Route::post('inventory/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');

    Route::resource('warehouses', WarehouseController::class)->only(['index', 'store', 'show']);
    Route::get('batches', [BatchController::class, 'index'])->name('batches.index');
    Route::get('batches/expiry-alerts', [BatchController::class, 'expiryAlerts'])->name('batches.expiry-alerts');
    Route::post('batches', [BatchController::class, 'store'])->name('batches.store');

    // Purchase & GRN
    Route::resource('suppliers', SupplierController::class)->only(['index', 'store', 'show']);
    Route::resource('purchase-orders', PurchaseOrderController::class)->only(['index', 'create', 'store', 'show']);
    Route::get('grn', [GoodsReceivedNoteController::class, 'index'])->name('grn.index');
    Route::get('grn/create', [GoodsReceivedNoteController::class, 'create'])->name('grn.create');
    Route::post('grn', [GoodsReceivedNoteController::class, 'store'])->name('grn.store');

    // Returns, Refunds & RTO
    Route::resource('returns', ReturnController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('returns/{return}/qc', [ReturnController::class, 'updateQc'])->name('returns.update-qc');
    Route::get('refunds', [RefundController::class, 'index'])->name('refunds.index');
    Route::post('refunds', [RefundController::class, 'store'])->name('refunds.store');
    Route::resource('rto', RtoController::class)->only(['index', 'store', 'update']);

    // Support Tickets & Complaints
    Route::resource('support', SupportTicketController::class);
    Route::post('support/{support}/message', [SupportTicketController::class, 'addMessage'])->name('support.message');
    Route::post('support/{support}/status', [SupportTicketController::class, 'updateStatus'])->name('support.status');

    // Communication: WhatsApp
    Route::get('communication/whatsapp', [WhatsAppController::class, 'index'])->name('communication.whatsapp');
    Route::post('communication/whatsapp/send', [WhatsAppController::class, 'send'])->name('communication.whatsapp.send');

    // Marketing Attribution
    Route::get('marketing', [MarketingSourceController::class, 'index'])->name('marketing.index');
    Route::post('marketing/sources', [MarketingSourceController::class, 'storeSource'])->name('marketing.sources.store');
    Route::post('marketing/campaigns', [MarketingSourceController::class, 'storeCampaign'])->name('marketing.campaigns.store');

    // Reports & Exports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('sales', [ReportController::class, 'sales'])->name('sales');
        Route::get('customers', [ReportController::class, 'customers'])->name('customers');
        Route::get('calls', [ReportController::class, 'calls'])->name('calls');
        Route::get('inventory', [ReportController::class, 'inventory'])->name('inventory');
        Route::get('rto', [ReportController::class, 'rto'])->name('rto');
    });

    // Employee & User Management
    Route::resource('employees', EmployeeController::class)->only(['index', 'create', 'store', 'show', 'update']);

    // Audit Logs
    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit.index');

    // Settings
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
    Route::post('settings/test-integration', [SettingController::class, 'testIntegration'])->name('settings.test-integration');

    // In-App Softphone Dialer
    Route::get('dialer/lookup', [DialerController::class, 'lookup'])->name('dialer.lookup');
    Route::post('dialer/start-call', [DialerController::class, 'startCall'])->name('dialer.start-call');
    Route::post('dialer/end-call', [DialerController::class, 'endCall'])->name('dialer.end-call');

    // Shopify Synchronization Hub
    Route::get('shopify-sync', [ShopifySyncController::class, 'index'])->name('shopify.sync.index');
    Route::post('shopify-sync/pull-orders', [ShopifySyncController::class, 'pullOrders'])->name('shopify.sync.pull-orders');
    Route::post('shopify-sync/{order}/push-fulfillment', [ShopifySyncController::class, 'pushFulfillment'])->name('shopify.sync.push-fulfillment');
    Route::post('shopify-sync/{product}/push-inventory', [ShopifySyncController::class, 'pushInventory'])->name('shopify.sync.push-inventory');

    // Delivery & Logistics Dispatch Panel
    Route::get('deliveries', [DeliveryController::class, 'index'])->name('deliveries.index');
    Route::post('deliveries/book-courier', [DeliveryController::class, 'bookCourier'])->name('deliveries.book-courier');
    Route::get('deliveries/{shipment}/label', [DeliveryController::class, 'shippingLabel'])->name('deliveries.shipping-label');
    Route::post('deliveries/sync-tracking', [DeliveryController::class, 'syncTracking'])->name('deliveries.sync-tracking');
    Route::post('deliveries/{shipment}/update-status', [DeliveryController::class, 'updateStatus'])->name('deliveries.update-status');

    // Meta Ads Sync Hub
    Route::get('meta-sync', [MetaLeadSyncController::class, 'index'])->name('meta.sync.index');
    Route::post('meta-sync/settings', [MetaLeadSyncController::class, 'updateSettings'])->name('meta.sync.settings');
    Route::post('meta-sync/simulate', [MetaLeadSyncController::class, 'simulateLead'])->name('meta.sync.simulate');
});
