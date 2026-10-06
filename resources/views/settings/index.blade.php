@extends('layouts.app')

@section('title', 'System Settings & Integrations — MantraHeal CRM')

@section('content')
<div class="max-w-6xl mx-auto space-y-6" x-data="{
    activeTab: 'company',
    testStatus: {},
    testLoading: {},
    async runTest(service, params = {}) {
        this.testLoading[service] = true;
        this.testStatus[service] = null;
        try {
            const formData = new FormData();
            formData.append('service', service);
            formData.append('_token', '{{ csrf_token() }}');
            for (const [key, val] of Object.entries(params)) {
                formData.append(key, val);
            }
            const res = await fetch('{{ route('settings.test-integration') }}', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });
            const data = await res.json();
            this.testStatus[service] = data;
        } catch (err) {
            this.testStatus[service] = { success: false, message: 'Connection test failed or timed out.' };
        } finally {
            this.testLoading[service] = false;
        }
    },
    simStatus: {},
    simLoading: {},
    async runWebhookSimulation(type) {
        this.simLoading[type] = true;
        this.simStatus[type] = null;
        try {
            const formData = new FormData();
            formData.append('type', type);
            formData.append('_token', '{{ csrf_token() }}');
            const res = await fetch('{{ route('api.webhooks.simulate') }}', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });
            const data = await res.json();
            this.simStatus[type] = data;
        } catch (e) {
            this.simStatus[type] = { status: 'error', message: 'Webhook simulation failed.' };
        } finally {
            this.simLoading[type] = false;
        }
    },
    copied: false,
    copyText(text) {
        navigator.clipboard.writeText(text);
        this.copied = true;
        setTimeout(() => this.copied = false, 2000);
    }
}">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xs text-slate-500 font-medium">Administration</span>
                <span class="text-slate-300">/</span>
                <span class="text-xs text-teal-700 font-semibold">Settings & Omnichannel APIs</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2.5">
                <svg class="w-7 h-7 text-teal-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                System & Integration Settings
            </h1>
            <p class="text-sm text-slate-500">Configure corporate branding, taxation, logistics, WhatsApp Cloud API & payment gateways</p>
        </div>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                CRM Engine Active
            </span>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success') }}</span>
            </div>
            <button @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
        </div>
    @endif

    <!-- Navigation Tabs -->
    <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-2">
        <button type="button" @click="activeTab = 'company'" :class="activeTab === 'company' ? 'bg-teal-700 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'" class="px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            Company Profile
        </button>

        <button type="button" @click="activeTab = 'financial'" :class="activeTab === 'financial' ? 'bg-teal-700 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'" class="px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/></svg>
            GST & Finance
        </button>

        <button type="button" @click="activeTab = 'whatsapp'" :class="activeTab === 'whatsapp' ? 'bg-teal-700 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'" class="px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.694.062-2.12-.529-1.503-.621-2.483-2.158-2.558-2.258-.075-.102-.612-.813-.612-1.554 0-.742.389-1.107.527-1.258.138-.15.301-.188.402-.188.102 0 .204.001.293.006.094.005.219-.036.344.262.13.312.446 1.087.485 1.167.039.08.065.174.013.277-.052.102-.078.166-.156.257-.078.092-.164.205-.234.276-.078.077-.16.161-.069.317.091.156.403.666.865 1.077.595.53 1.097.694 1.253.771.156.078.247.065.338-.039.091-.104.389-.453.493-.609.104-.156.208-.13.351-.078.143.052.911.43 1.067.508.156.078.26.117.299.182.039.065.039.378-.105.783z"/></svg>
            WhatsApp Cloud API
        </button>

        <button type="button" @click="activeTab = 'shopify'" :class="activeTab === 'shopify' ? 'bg-teal-700 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'" class="px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            Shopify Store
        </button>

        <button type="button" @click="activeTab = 'meta'" :class="activeTab === 'meta' ? 'bg-teal-700 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'" class="px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-2">
            <svg class="w-4 h-4 text-blue-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.04c-5.5 0-10 4.49-10 10.02 0 5 3.66 9.15 8.44 9.9v-7H7.9v-2.9h2.54V9.85c0-2.51 1.49-3.89 3.78-3.89 1.09 0 2.23.19 2.23.19v2.47h-1.26c-1.24 0-1.63.77-1.63 1.56v1.88h2.78l-.45 2.9h-2.33v7a10 10 0 008.44-9.9c0-5.53-4.5-10.02-10-10.02z"/></svg>
            Meta Ads
        </button>

        <button type="button" @click="activeTab = 'shiprocket'" :class="activeTab === 'shiprocket' ? 'bg-teal-700 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'" class="px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-2">
            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
            Shiprocket Logistics
        </button>

        <button type="button" @click="activeTab = 'razorpay'" :class="activeTab === 'razorpay' ? 'bg-teal-700 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'" class="px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-2">
            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            Razorpay Gateway
        </button>

        <button type="button" @click="activeTab = 'mail'" :class="activeTab === 'mail' ? 'bg-teal-700 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'" class="px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-2">
            <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            Email & SMTP
        </button>

        <button type="button" @click="activeTab = 'telephony'" :class="activeTab === 'telephony' ? 'bg-teal-700 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'" class="px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-2">
            <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
            Telephony
        </button>
    </div>

    <!-- Main Settings Form -->
    <form action="{{ route('settings.update') }}" method="POST" class="space-y-6">
        @csrf

        <!-- TAB 1: Company & Brand Identity -->
        <div x-show="activeTab === 'company'" class="space-y-6">
            <div class="card p-6 space-y-5">
                <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                    <div>
                        <h2 class="font-bold text-slate-800 text-base flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center font-bold text-sm">MH</span>
                            Company Branding & Business Profile
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">Define your brand identity, corporate contact, and invoice letterhead data</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Company Legal / Trade Name</label>
                        <input type="text" name="company_name" value="{{ $settings['company_name'] ?? 'MantraHeal' }}" required class="form-control w-full text-sm font-bold text-slate-800">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Application Title / CRM Brand</label>
                        <input type="text" name="app_name" value="{{ $settings['app_name'] ?? 'MantraHeal CRM' }}" required class="form-control w-full text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Brand Tagline / Slogan</label>
                        <input type="text" name="tagline" value="{{ $settings['tagline'] ?? 'Ayurvedic & Healthcare Direct-to-Consumer CRM' }}" class="form-control w-full text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Official Support Email</label>
                        <input type="email" name="support_email" value="{{ $settings['support_email'] ?? 'support@mantraheal.com' }}" required class="form-control w-full text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Toll-Free / Helpline Phone</label>
                        <input type="text" name="support_phone" value="{{ $settings['support_phone'] ?? '+91 98765 43210' }}" required class="form-control w-full text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Primary Brand Accent Color</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="brand_color" value="{{ $settings['brand_color'] ?? '#0f766e' }}" class="h-9 w-12 rounded cursor-pointer border border-slate-200">
                            <input type="text" value="{{ $settings['brand_color'] ?? '#0f766e' }}" class="form-control flex-1 text-xs font-mono uppercase" readonly>
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Registered Corporate Headquarters Address</label>
                        <textarea name="company_address" rows="2" class="form-control w-full text-sm leading-relaxed">{{ $settings['company_address'] ?? 'Plot No. 42, Sector 18, Udyog Vihar, Gurugram, Haryana, 122015, India' }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 2: GST & Financial Parameters -->
        <div x-show="activeTab === 'financial'" class="space-y-6">
            <div class="card p-6 space-y-5">
                <div class="border-b border-slate-100 pb-3">
                    <h2 class="font-bold text-slate-800 text-base flex items-center gap-2">
                        <svg class="w-5 h-5 text-teal-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/></svg>
                        Taxation, Currency & Bank Billing Credentials
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Parameters applied to customer GST invoices, HSN formulation tax, and payment collection</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Corporate GSTIN</label>
                        <input type="text" name="gstin" value="{{ $settings['gstin'] ?? '06AABCM1234F1Z8' }}" required class="form-control w-full text-sm font-mono uppercase font-bold text-teal-800">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Company PAN</label>
                        <input type="text" name="pan_number" value="{{ $settings['pan_number'] ?? 'AABCM1234F' }}" required class="form-control w-full text-sm font-mono uppercase font-bold">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Default Currency Symbol</label>
                        <input type="text" name="currency" value="{{ $settings['currency'] ?? 'INR (₹)' }}" required class="form-control w-full text-sm font-bold">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Ayurvedic Formulation GST %</label>
                        <div class="relative">
                            <input type="number" step="0.5" name="default_gst" value="{{ $settings['default_gst'] ?? 12 }}" class="form-control w-full text-sm pr-8 font-bold">
                            <span class="absolute right-3 top-2 text-slate-400 font-bold text-sm">%</span>
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-100 pt-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-3">Beneficiary Corporate Bank Account (For Invoices & Bank Transfers)</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-500 mb-1">Bank Name</label>
                            <input type="text" name="bank_name" value="{{ $settings['bank_name'] ?? 'HDFC Bank Ltd.' }}" class="form-control w-full text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-500 mb-1">Account Number</label>
                            <input type="text" name="bank_account_no" value="{{ $settings['bank_account_no'] ?? '50200088192837' }}" class="form-control w-full text-xs font-mono">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-500 mb-1">IFSC Code</label>
                            <input type="text" name="bank_ifsc" value="{{ $settings['bank_ifsc'] ?? 'HDFC0001234' }}" class="form-control w-full text-xs font-mono uppercase">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-500 mb-1">Branch Location</label>
                            <input type="text" name="bank_branch" value="{{ $settings['bank_branch'] ?? 'Cyber City, Gurugram' }}" class="form-control w-full text-xs">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 3: WhatsApp Cloud API -->
        <div x-show="activeTab === 'whatsapp'" class="space-y-6">
            <div class="card p-6 space-y-5">
                <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                    <div>
                        <h2 class="font-bold text-slate-800 text-base flex items-center gap-2">
                            <span class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-xs">WA</span>
                            Meta WhatsApp Business Cloud API
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">Send order updates, tracking alerts, and tele-sales follow-ups via official Meta WhatsApp API</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" 
                                @click="runTest('whatsapp', { 
                                    whatsapp_phone_number_id: $el.closest('form').querySelector('[name=whatsapp_phone_number_id]').value,
                                    whatsapp_token: $el.closest('form').querySelector('[name=whatsapp_token]').value 
                                })"
                                :disabled="testLoading['whatsapp']"
                                class="btn btn-secondary text-xs px-3 py-1.5 flex items-center gap-1.5 border border-slate-300 shadow-sm">
                            <svg x-show="testLoading['whatsapp']" class="w-3.5 h-3.5 animate-spin text-teal-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span x-text="testLoading['whatsapp'] ? 'Testing...' : 'Test WhatsApp Ping'"></span>
                        </button>
                    </div>
                </div>

                <!-- Test Feedback Notification -->
                <div x-show="testStatus['whatsapp']" x-transition class="p-3 rounded-lg text-xs" :class="testStatus['whatsapp']?.success ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200'">
                    <div class="font-bold" x-text="testStatus['whatsapp']?.success ? '✓ WhatsApp Connection Verified' : '✗ WhatsApp Connection Error'"></div>
                    <div x-text="testStatus['whatsapp']?.message"></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">WhatsApp Phone Number ID</label>
                        <input type="text" name="whatsapp_phone_number_id" value="{{ $settings['whatsapp_phone_number_id'] ?? env('WHATSAPP_PHONE_NUMBER_ID', '109283746501928') }}" class="form-control w-full text-xs font-mono">
                        <span class="text-[10px] text-slate-400 mt-1 block">Find in Meta for Developers &gt; WhatsApp &gt; API Setup</span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">WhatsApp Business Account ID (WABA)</label>
                        <input type="text" name="whatsapp_waba_id" value="{{ $settings['whatsapp_waba_id'] ?? env('WHATSAPP_BUSINESS_ACCOUNT_ID', '981273645019283') }}" class="form-control w-full text-xs font-mono">
                        <span class="text-[10px] text-slate-400 mt-1 block">Your Meta Business Portfolio WhatsApp ID</span>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Permanent System User Access Token</label>
                        <input type="password" name="whatsapp_token" value="{{ $settings['whatsapp_token'] ?? 'EAABwzLixnjYBA...' }}" class="form-control w-full text-xs font-mono">
                        <span class="text-[10px] text-slate-400 mt-1 block">Meta System User token with <code>whatsapp_business_messaging</code> permission</span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">API Base URL</label>
                        <input type="text" name="whatsapp_api_url" value="{{ $settings['whatsapp_api_url'] ?? env('WHATSAPP_API_URL', 'https://graph.facebook.com/v19.0') }}" class="form-control w-full text-xs font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Webhook Verify Token</label>
                        <input type="text" name="whatsapp_verify_token" value="{{ $settings['whatsapp_verify_token'] ?? 'mantraheal_wa_secret_token_2026' }}" class="form-control w-full text-xs font-mono">
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 4: Shopify E-Commerce Store -->
        <div x-show="activeTab === 'shopify'" class="space-y-6">
            <div class="card p-6 space-y-5">
                <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                    <div>
                        <h2 class="font-bold text-slate-800 text-base flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold text-xs">SH</span>
                            Shopify E-Commerce Store Integration
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">Automated two-way synchronization of online customer orders, payment status, and stock levels</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('shopify.sync.index') }}" class="btn bg-emerald-700 hover:bg-emerald-600 text-white font-bold text-xs px-3 py-1.5 rounded-lg flex items-center gap-1.5 shadow-sm">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Open Shopify Sync Hub
                        </a>
                        <button type="button" 
                                @click="runTest('shopify', { 
                                    shopify_shop_url: $el.closest('form').querySelector('[name=shopify_shop_url]').value,
                                    shopify_access_token: $el.closest('form').querySelector('[name=shopify_access_token]').value 
                                })"
                                :disabled="testLoading['shopify']"
                                class="btn btn-secondary text-xs px-3 py-1.5 flex items-center gap-1.5 border border-slate-300 shadow-sm">
                            <svg x-show="testLoading['shopify']" class="w-3.5 h-3.5 animate-spin text-teal-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span x-text="testLoading['shopify'] ? 'Testing...' : 'Test Shopify Connection'"></span>
                        </button>
                    </div>
                </div>

                <!-- Test Feedback -->
                <div x-show="testStatus['shopify']" x-transition class="p-3 rounded-lg text-xs" :class="testStatus['shopify']?.success ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200'">
                    <div class="font-bold" x-text="testStatus['shopify']?.success ? '✓ Shopify Connection Verified' : '✗ Shopify Error'"></div>
                    <div x-text="testStatus['shopify']?.message"></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Shopify Store Domain</label>
                        <input type="text" name="shopify_shop_url" value="{{ $settings['shopify_shop_url'] ?? env('SHOPIFY_SHOP_URL', 'mantraheal.myshopify.com') }}" class="form-control w-full text-xs font-mono">
                        <span class="text-[10px] text-slate-400 mt-1 block">e.g. yourstore.myshopify.com</span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Admin API Access Token</label>
                        <input type="password" name="shopify_access_token" value="{{ $settings['shopify_access_token'] ?? 'shpat_98a72b8109283746c1092' }}" class="form-control w-full text-xs font-mono">
                        <span class="text-[10px] text-slate-400 mt-1 block">Generated in Shopify Custom Apps with <code>read_orders, write_orders</code></span>
                    </div>

                    <div class="md:col-span-2 p-4 bg-slate-50 rounded-xl border border-slate-200/80">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">MantraHeal Shopify Inbound Webhook URL</span>
                            <button type="button" @click="copyText('{{ url('api/shopify/orders-create') }}')" class="text-xs text-teal-700 hover:text-teal-900 font-semibold flex items-center gap-1">
                                <span x-text="copied ? 'Copied!' : 'Copy URL'"></span>
                            </button>
                        </div>
                        <code class="text-xs text-teal-800 font-mono select-all bg-white px-3 py-1.5 rounded border border-slate-200 block">{{ url('api/shopify/orders-create') }}</code>
                        <span class="text-[11px] text-slate-500 mt-1 block">Paste into Shopify Admin &gt; Settings &gt; Notifications &gt; Webhooks for topic <strong>orders/create</strong></span>
                    </div>

                    <div class="md:col-span-2 p-4 bg-emerald-50/80 rounded-xl border border-emerald-200 shadow-sm">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <h3 class="text-xs font-bold text-emerald-950 uppercase tracking-wider flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    Live Webhook Simulator (orders/create)
                                </h3>
                                <p class="text-[11px] text-emerald-800 mt-0.5">Simulate a live payload from Shopify to verify customer matching, inventory reservation & order generation.</p>
                            </div>
                            <button type="button" 
                                    @click="runWebhookSimulation('shopify')" 
                                    :disabled="simLoading['shopify']"
                                    class="btn bg-emerald-700 hover:bg-emerald-600 text-white text-xs px-4 py-2 font-bold rounded-lg shadow-sm flex items-center gap-1.5 flex-shrink-0 cursor-pointer">
                                <svg x-show="simLoading['shopify']" class="w-3.5 h-3.5 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                <span x-text="simLoading['shopify'] ? 'Simulating...' : 'Trigger Sample Order Webhook'"></span>
                            </button>
                        </div>
                        <div x-show="simStatus['shopify']" x-transition class="mt-3 p-3 bg-white rounded-lg border text-xs" :class="simStatus['shopify']?.status === 'success' ? 'border-emerald-200 text-emerald-900' : 'border-rose-200 text-rose-800'">
                            <div class="font-bold flex items-center gap-1.5">
                                <span x-text="simStatus['shopify']?.status === 'success' ? '✓ Webhook Processed Successfully' : '✗ Simulation Failed'"></span>
                            </div>
                            <div class="mt-1 text-[11px] font-mono bg-slate-50 p-2 rounded border border-slate-200 overflow-x-auto" x-text="JSON.stringify(simStatus['shopify'], null, 2)"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB: Meta Ads (Facebook & Instagram) -->
        <div x-show="activeTab === 'meta'" class="space-y-6">
            <div class="card p-6 space-y-5">
                <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                    <div>
                        <h2 class="font-bold text-slate-800 text-base flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-blue-600 text-white flex items-center justify-center font-black text-xs">f</span>
                            Meta Ads (Facebook &amp; Instagram) Lead Generation
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">Instant lead synchronization from Facebook &amp; Instagram Lead Ads forms directly into sales pipeline</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('meta.sync.index') }}" class="btn bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-3 py-1.5 rounded-lg flex items-center gap-1.5 shadow-sm">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            Open Meta Ads Hub
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Facebook Page ID / Business Account</label>
                        <input type="text" name="meta_page_id" value="{{ $settings['meta_page_id'] ?? '10492837482' }}" class="form-control w-full text-xs font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Webhook Verify Token</label>
                        <input type="text" name="meta_verify_token" value="{{ $settings['meta_verify_token'] ?? 'mantraheal_meta_token_2026' }}" class="form-control w-full text-xs font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">App Secret</label>
                        <input type="password" name="meta_app_secret" value="{{ $settings['meta_app_secret'] ?? '••••••••••••••••' }}" class="form-control w-full text-xs font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">System User / Access Token</label>
                        <input type="password" name="meta_access_token" value="{{ $settings['meta_access_token'] ?? '••••••••••••••••' }}" class="form-control w-full text-xs font-mono">
                    </div>

                    <div class="md:col-span-2 p-4 bg-slate-50 rounded-xl border border-slate-200/80">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">MantraHeal Meta LeadGen Webhook URL</span>
                            <button type="button" @click="copyText('{{ url('api/meta/leadgen-webhook') }}')" class="text-xs text-blue-700 hover:text-blue-900 font-semibold flex items-center gap-1">
                                <span x-text="copied ? 'Copied!' : 'Copy URL'"></span>
                            </button>
                        </div>
                        <code class="text-xs text-blue-800 font-mono select-all bg-white px-3 py-1.5 rounded border border-slate-200 block">{{ url('api/meta/leadgen-webhook') }}</code>
                        <span class="text-[11px] text-slate-500 mt-1 block">Configure in Meta Developers Portal &gt; App Dashboard &gt; Webhooks &gt; Page (field: <strong>leadgen</strong>)</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 5: Shiprocket Logistics -->
        <div x-show="activeTab === 'shiprocket'" class="space-y-6">
            <div class="card p-6 space-y-5">
                <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                    <div>
                        <h2 class="font-bold text-slate-800 text-base flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-700 flex items-center justify-center font-bold text-xs">SR</span>
                            Shiprocket Automated Courier Logistics
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">Automate AWB booking, pickup scheduling, Delhivery/BlueDart dispatch, and RTO tracking</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('deliveries.index') }}" class="btn bg-indigo-700 hover:bg-indigo-600 text-white font-bold text-xs px-3 py-1.5 rounded-lg flex items-center gap-1.5 shadow-sm">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                            Open Dispatch Panel
                        </a>
                        <button type="button" 
                                @click="runTest('shiprocket', { shiprocket_email: $el.closest('form').querySelector('[name=shiprocket_email]').value })"
                                :disabled="testLoading['shiprocket']"
                                class="btn btn-secondary text-xs px-3 py-1.5 flex items-center gap-1.5 border border-slate-300 shadow-sm">
                            <svg x-show="testLoading['shiprocket']" class="w-3.5 h-3.5 animate-spin text-teal-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span x-text="testLoading['shiprocket'] ? 'Testing...' : 'Test Shiprocket Login'"></span>
                        </button>
                    </div>
                </div>

                <!-- Test Feedback -->
                <div x-show="testStatus['shiprocket']" x-transition class="p-3 rounded-lg text-xs" :class="testStatus['shiprocket']?.success ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200'">
                    <div class="font-bold" x-text="testStatus['shiprocket']?.success ? '✓ Shiprocket Verified' : '✗ Shiprocket Error'"></div>
                    <div x-text="testStatus['shiprocket']?.message"></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Shiprocket Account Email</label>
                        <input type="email" name="shiprocket_email" value="{{ $settings['shiprocket_email'] ?? env('SHIPROCKET_EMAIL', 'logistics@mantraheal.com') }}" class="form-control w-full text-xs">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Account Password</label>
                        <input type="password" name="shiprocket_password" value="{{ $settings['shiprocket_password'] ?? '••••••••••••' }}" class="form-control w-full text-xs">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Primary Warehouse Pickup Pincode</label>
                        <input type="text" name="shiprocket_pickup_pincode" value="{{ $settings['shiprocket_pickup_pincode'] ?? '122015' }}" class="form-control w-full text-xs font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Default Preferred Courier</label>
                        <select name="shiprocket_default_courier" class="form-control w-full text-xs">
                            <option value="Delhivery" {{ ($settings['shiprocket_default_courier'] ?? 'Delhivery') === 'Delhivery' ? 'selected' : '' }}>Delhivery Express</option>
                            <option value="Blue Dart" {{ ($settings['shiprocket_default_courier'] ?? '') === 'Blue Dart' ? 'selected' : '' }}>Blue Dart Air</option>
                            <option value="DTDC" {{ ($settings['shiprocket_default_courier'] ?? '') === 'DTDC' ? 'selected' : '' }}>DTDC Surface</option>
                            <option value="XpressBees" {{ ($settings['shiprocket_default_courier'] ?? '') === 'XpressBees' ? 'selected' : '' }}>XpressBees E-Com</option>
                        </select>
                    </div>

                    <div class="md:col-span-2 p-4 bg-slate-50 rounded-xl border border-slate-200/80">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">MantraHeal Tracking Webhook Endpoint</span>
                            <button type="button" @click="copyText('{{ url('api/shiprocket/tracking') }}')" class="text-xs text-teal-700 hover:text-teal-900 font-semibold flex items-center gap-1">
                                <span x-text="copied ? 'Copied!' : 'Copy URL'"></span>
                            </button>
                        </div>
                        <code class="text-xs text-teal-800 font-mono select-all bg-white px-3 py-1.5 rounded border border-slate-200 block">{{ url('api/shiprocket/tracking') }}</code>
                        <span class="text-[11px] text-slate-500 mt-1 block">Add to Shiprocket Webhook settings for status transitions & AWB delivery tracking</span>
                    </div>

                    <div class="md:col-span-2 p-4 bg-indigo-50/80 rounded-xl border border-indigo-200 shadow-sm">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <h3 class="text-xs font-bold text-indigo-950 uppercase tracking-wider flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    Live Courier Webhook Simulator
                                </h3>
                                <p class="text-[11px] text-indigo-800 mt-0.5">Simulate an AWB tracking status update callback from Shiprocket (e.g., In-Transit / Delivered).</p>
                            </div>
                            <button type="button" 
                                    @click="runWebhookSimulation('shiprocket')" 
                                    :disabled="simLoading['shiprocket']"
                                    class="btn bg-indigo-700 hover:bg-indigo-600 text-white text-xs px-4 py-2 font-bold rounded-lg shadow-sm flex items-center gap-1.5 flex-shrink-0 cursor-pointer">
                                <svg x-show="simLoading['shiprocket']" class="w-3.5 h-3.5 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                <span x-text="simLoading['shiprocket'] ? 'Simulating...' : 'Simulate Courier Status Webhook'"></span>
                            </button>
                        </div>
                        <div x-show="simStatus['shiprocket']" x-transition class="mt-3 p-3 bg-white rounded-lg border text-xs" :class="simStatus['shiprocket']?.status === 'success' ? 'border-indigo-200 text-indigo-900' : 'border-rose-200 text-rose-800'">
                            <div class="font-bold flex items-center gap-1.5">
                                <span x-text="simStatus['shiprocket']?.status === 'success' ? '✓ Tracking Webhook Processed' : '✗ Simulation Failed'"></span>
                            </div>
                            <div class="mt-1 text-[11px] font-mono bg-slate-50 p-2 rounded border border-slate-200 overflow-x-auto" x-text="JSON.stringify(simStatus['shiprocket'], null, 2)"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 6: Razorpay Payment Gateway -->
        <div x-show="activeTab === 'razorpay'" class="space-y-6">
            <div class="card p-6 space-y-5">
                <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                    <div>
                        <h2 class="font-bold text-slate-800 text-base flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center font-bold text-xs">RZ</span>
                            Razorpay Payment Gateway Integration
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">Collect prepaid UPI, credit/debit cards, net banking, and auto-verify order payments</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" 
                                @click="runTest('razorpay', { 
                                    razorpay_key_id: $el.closest('form').querySelector('[name=razorpay_key_id]').value,
                                    razorpay_mode: $el.closest('form').querySelector('[name=razorpay_mode]').value 
                                })"
                                :disabled="testLoading['razorpay']"
                                class="btn btn-secondary text-xs px-3 py-1.5 flex items-center gap-1.5 border border-slate-300 shadow-sm">
                            <svg x-show="testLoading['razorpay']" class="w-3.5 h-3.5 animate-spin text-teal-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span x-text="testLoading['razorpay'] ? 'Testing...' : 'Test Razorpay Keys'"></span>
                        </button>
                    </div>
                </div>

                <!-- Test Feedback -->
                <div x-show="testStatus['razorpay']" x-transition class="p-3 rounded-lg text-xs" :class="testStatus['razorpay']?.success ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200'">
                    <div class="font-bold" x-text="testStatus['razorpay']?.success ? '✓ Razorpay Verified' : '✗ Razorpay Error'"></div>
                    <div x-text="testStatus['razorpay']?.message"></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Razorpay Environment</label>
                        <select name="razorpay_mode" class="form-control w-full text-xs font-bold">
                            <option value="test" {{ ($settings['razorpay_mode'] ?? 'test') === 'test' ? 'selected' : '' }}>Test / Sandbox Mode (Recommended for Development)</option>
                            <option value="live" {{ ($settings['razorpay_mode'] ?? '') === 'live' ? 'selected' : '' }}>Live Production Gateway</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Key ID</label>
                        <input type="text" name="razorpay_key_id" value="{{ $settings['razorpay_key_id'] ?? env('RAZORPAY_KEY_ID', 'rzp_test_9812A8172b091') }}" class="form-control w-full text-xs font-mono">
                        <span class="text-[10px] text-slate-400 mt-1 block">Starts with <code>rzp_test_</code> or <code>rzp_live_</code></span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Key Secret</label>
                        <input type="password" name="razorpay_key_secret" value="{{ $settings['razorpay_key_secret'] ?? env('RAZORPAY_KEY_SECRET', '••••••••••••••••') }}" class="form-control w-full text-xs font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Webhook Signing Secret</label>
                        <input type="text" name="razorpay_webhook_secret" value="{{ $settings['razorpay_webhook_secret'] ?? 'whsec_982109283746' }}" class="form-control w-full text-xs font-mono">
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 7: Email / SMTP Delivery -->
        <div x-show="activeTab === 'mail'" class="space-y-6">
            <div class="card p-6 space-y-5">
                <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                    <div>
                        <h2 class="font-bold text-slate-800 text-base flex items-center gap-2">
                            <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            Email Dispatch & SMTP Relay
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">Configure transaction emails, dispatch confirmations, and automated invoice PDF attachments</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" 
                                @click="runTest('mail', { mail_from_address: $el.closest('form').querySelector('[name=mail_from_address]').value })"
                                :disabled="testLoading['mail']"
                                class="btn btn-secondary text-xs px-3 py-1.5 flex items-center gap-1.5 border border-slate-300 shadow-sm">
                            <svg x-show="testLoading['mail']" class="w-3.5 h-3.5 animate-spin text-teal-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span x-text="testLoading['mail'] ? 'Testing...' : 'Test Mail Dispatch'"></span>
                        </button>
                    </div>
                </div>

                <!-- Test Feedback -->
                <div x-show="testStatus['mail']" x-transition class="p-3 rounded-lg text-xs" :class="testStatus['mail']?.success ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200'">
                    <div class="font-bold" x-text="testStatus['mail']?.success ? '✓ Email Channel Ready' : '✗ Email Error'"></div>
                    <div x-text="testStatus['mail']?.message"></div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Mail Driver</label>
                        <select name="mail_mailer" class="form-control w-full text-xs font-bold">
                            <option value="smtp" {{ ($settings['mail_mailer'] ?? env('MAIL_MAILER', 'log')) === 'smtp' ? 'selected' : '' }}>SMTP Server</option>
                            <option value="log" {{ ($settings['mail_mailer'] ?? env('MAIL_MAILER', 'log')) === 'log' ? 'selected' : '' }}>Local Log (Storage/logs)</option>
                            <option value="ses" {{ ($settings['mail_mailer'] ?? '') === 'ses' ? 'selected' : '' }}>Amazon SES</option>
                            <option value="sendmail" {{ ($settings['mail_mailer'] ?? '') === 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">SMTP Host</label>
                        <input type="text" name="mail_host" value="{{ $settings['mail_host'] ?? env('MAIL_HOST', 'smtp.gmail.com') }}" class="form-control w-full text-xs font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">SMTP Port</label>
                        <input type="text" name="mail_port" value="{{ $settings['mail_port'] ?? env('MAIL_PORT', '587') }}" class="form-control w-full text-xs font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Encryption</label>
                        <select name="mail_encryption" class="form-control w-full text-xs">
                            <option value="tls" {{ ($settings['mail_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS</option>
                            <option value="ssl" {{ ($settings['mail_encryption'] ?? '') === 'ssl' ? 'selected' : '' }}>SSL</option>
                            <option value="null" {{ ($settings['mail_encryption'] ?? '') === 'null' ? 'selected' : '' }}>None</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">SMTP Username</label>
                        <input type="text" name="mail_username" value="{{ $settings['mail_username'] ?? 'smtp@mantraheal.com' }}" class="form-control w-full text-xs">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">SMTP Password</label>
                        <input type="password" name="mail_password" value="{{ $settings['mail_password'] ?? '••••••••••••' }}" class="form-control w-full text-xs">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Sender Email ("From" Address)</label>
                        <input type="email" name="mail_from_address" value="{{ $settings['mail_from_address'] ?? 'support@mantraheal.com' }}" class="form-control w-full text-xs">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Sender Name ("From" Name)</label>
                        <input type="text" name="mail_from_name" value="{{ $settings['mail_from_name'] ?? 'MantraHeal Customer Care' }}" class="form-control w-full text-xs font-bold">
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 8: Telephony & Call Center -->
        <div x-show="activeTab === 'telephony'" class="space-y-6">
            <div class="card p-6 space-y-5">
                <div class="border-b border-slate-100 pb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h2 class="font-bold text-slate-800 text-base flex items-center gap-2">
                            <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            Tele-calling, Cloud Telephony & Audio Recordings
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">Integrate Exotel, Knowlarity or Twilio for click-to-call, call logging, and automated recording playback</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="$dispatch('open-dialer')" class="btn bg-rose-700 hover:bg-rose-600 text-white font-bold text-xs px-3 py-1.5 rounded-lg flex items-center gap-1.5 shadow-sm cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            Launch Dialer Softphone
                        </button>
                        <a href="{{ route('calls.index') }}" class="btn btn-secondary text-xs px-3 py-1.5 flex items-center gap-1.5 border border-slate-300 shadow-sm">
                            Call Logs
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Telephony Gateway Provider</label>
                        <select name="telephony_provider" class="form-control w-full text-xs font-bold">
                            <option value="Manual" {{ ($settings['telephony_provider'] ?? 'Manual') === 'Manual' ? 'selected' : '' }}>Direct Tele-calling (Manual / SIM Call)</option>
                            <option value="Exotel" {{ ($settings['telephony_provider'] ?? '') === 'Exotel' ? 'selected' : '' }}>Exotel Cloud Telephony</option>
                            <option value="Knowlarity" {{ ($settings['telephony_provider'] ?? '') === 'Knowlarity' ? 'selected' : '' }}>Knowlarity SuperReceptionist</option>
                            <option value="Twilio" {{ ($settings['telephony_provider'] ?? '') === 'Twilio' ? 'selected' : '' }}>Twilio Voice</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Virtual Caller ID / Outbound DID</label>
                        <input type="text" name="telephony_caller_id" value="{{ $settings['telephony_caller_id'] ?? '+91 80 4719 2830' }}" class="form-control w-full text-xs font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Telephony API Key / Account SID</label>
                        <input type="text" name="telephony_api_key" value="{{ $settings['telephony_api_key'] ?? 'exo_sid_9812739485' }}" class="form-control w-full text-xs font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Telephony API Token / Secret</label>
                        <input type="password" name="telephony_api_secret" value="{{ $settings['telephony_api_secret'] ?? '••••••••••••••••' }}" class="form-control w-full text-xs font-mono">
                    </div>

                    <div class="md:col-span-2 p-4 bg-slate-50 rounded-xl border border-slate-200/80">
                        <span class="text-xs font-bold text-slate-700 uppercase tracking-wider block mb-1">Audio Recordings Sync Webhook</span>
                        <code class="text-xs text-teal-800 font-mono select-all bg-white px-3 py-1.5 rounded border border-slate-200 block">{{ url('api/telephony/recording-callback') }}</code>
                        <span class="text-[11px] text-slate-500 mt-1 block">Configured as call leg completion callback to auto-attach MP3 recordings to agent calls</span>
                    </div>

                    <div class="md:col-span-2 p-4 bg-rose-50/80 rounded-xl border border-rose-200 shadow-sm">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <h3 class="text-xs font-bold text-rose-950 uppercase tracking-wider flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    Telephony Call & Audio Webhook Simulator
                                </h3>
                                <p class="text-[11px] text-rose-800 mt-0.5">Simulate a call completion webhook with audio recording URL to verify call logging and MP3/WAV playback.</p>
                            </div>
                            <button type="button" 
                                    @click="runWebhookSimulation('telephony')" 
                                    :disabled="simLoading['telephony']"
                                    class="btn bg-rose-700 hover:bg-rose-600 text-white text-xs px-4 py-2 font-bold rounded-lg shadow-sm flex items-center gap-1.5 flex-shrink-0 cursor-pointer">
                                <svg x-show="simLoading['telephony']" class="w-3.5 h-3.5 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                <span x-text="simLoading['telephony'] ? 'Simulating...' : 'Simulate Call & Recording Webhook'"></span>
                            </button>
                        </div>
                        <div x-show="simStatus['telephony']" x-transition class="mt-3 p-3 bg-white rounded-lg border text-xs" :class="simStatus['telephony']?.status === 'success' ? 'border-rose-200 text-rose-900' : 'border-rose-200 text-rose-800'">
                            <div class="font-bold flex items-center gap-1.5">
                                <span x-text="simStatus['telephony']?.status === 'success' ? '✓ Call & Recording Synced' : '✗ Simulation Failed'"></span>
                            </div>
                            <div class="mt-1 text-[11px] font-mono bg-slate-50 p-2 rounded border border-slate-200 overflow-x-auto" x-text="JSON.stringify(simStatus['telephony'], null, 2)"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit & Save Actions Bar -->
        <div class="card p-4 bg-slate-900 text-white flex flex-col sm:flex-row items-center justify-between gap-4 sticky bottom-4 shadow-xl z-20">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-teal-600/30 text-teal-400 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                </div>
                <div>
                    <div class="text-sm font-bold text-white">Save All System Changes</div>
                    <div class="text-xs text-slate-400">Updates will take effect immediately across all CRM processes</div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="btn bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold px-8 py-2.5 rounded-lg shadow-lg hover:shadow-teal-500/25 transition">
                    Save System Settings
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
