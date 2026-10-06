@extends('layouts.app')

@section('title', 'Meta Ads Lead Sync Hub — MantraHeal CRM')

@section('content')
<div class="space-y-6" x-data="{
    copiedUrl: false,
    copiedToken: false,
    copy(text, target) {
        navigator.clipboard.writeText(text);
        if (target === 'url') {
            this.copiedUrl = true;
            setTimeout(() => this.copiedUrl = false, 2000);
        } else {
            this.copiedToken = true;
            setTimeout(() => this.copiedToken = false, 2000);
        }
    }
}">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('leads.index') }}" class="text-xs text-slate-500 hover:text-slate-700 font-medium">Leads</a>
                <span class="text-slate-300">/</span>
                <span class="text-xs text-blue-700 font-semibold">Meta Ads Hub</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-blue-600 text-white flex items-center justify-center font-black text-sm shadow-sm">
                    f
                </div>
                Meta Ads &amp; Lead Generation Sync Hub
            </h1>
            <p class="text-sm text-slate-500">Real-time instant ingestion of Facebook &amp; Instagram Instant Form leads into your sales pipeline</p>
        </div>

        <div class="flex items-center gap-3">
            <form action="{{ route('meta.sync.simulate') }}" method="POST">
                @csrf
                <button type="submit" class="btn bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-4 py-2 rounded-lg shadow-sm flex items-center gap-2 cursor-pointer transition">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Simulate Incoming Meta Lead
                </button>
            </form>
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

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="card p-4">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Meta Leads</div>
            <div class="mt-1 text-2xl font-black text-slate-900">{{ number_format($totalMetaLeads) }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">All-time Facebook &amp; IG Inquiries</div>
        </div>

        <div class="card p-4">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Synced Today</div>
            <div class="mt-1 text-2xl font-black text-blue-600">{{ number_format($syncedToday) }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Captured in last 24 hours</div>
        </div>

        <div class="card p-4">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Active Campaigns</div>
            <div class="mt-1 text-2xl font-black text-indigo-600">{{ number_format($campaignsCount) }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Tracked marketing sources</div>
        </div>

        <div class="card p-4">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Won Conversion Rate</div>
            <div class="mt-1 text-2xl font-black text-emerald-600">{{ $conversionRate }}%</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Closed order conversions</div>
        </div>
    </div>

    <!-- Webhook Integration & Credentials -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Live Webhook Configuration Box -->
        <div class="card p-6 space-y-4 bg-gradient-to-br from-slate-900 to-slate-800 text-white lg:col-span-1">
            <div class="flex items-center justify-between border-b border-slate-700/60 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-300">Live Meta Webhook</h3>
                </div>
                <span class="text-[10px] bg-blue-600 text-white font-bold px-2 py-0.5 rounded">Graph v19.0</span>
            </div>

            <div>
                <label class="block text-[11px] font-semibold text-slate-400 mb-1">Callback URL</label>
                <div class="flex items-center gap-1.5 bg-slate-950 p-2 rounded-lg border border-slate-700 text-xs font-mono select-all break-all">
                    <span class="flex-1 text-teal-400">{{ $webhookUrl }}</span>
                    <button type="button" @click="copy('{{ $webhookUrl }}', 'url')" class="text-[11px] text-slate-400 hover:text-white flex-shrink-0 px-1.5 py-0.5 rounded bg-slate-800 border border-slate-700">
                        <span x-text="copiedUrl ? 'Copied!' : 'Copy'"></span>
                    </button>
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-semibold text-slate-400 mb-1">Verify Token</label>
                <div class="flex items-center gap-1.5 bg-slate-950 p-2 rounded-lg border border-slate-700 text-xs font-mono select-all">
                    <span class="flex-1 text-amber-400">{{ $settings['meta_verify_token'] }}</span>
                    <button type="button" @click="copy('{{ $settings['meta_verify_token'] }}', 'token')" class="text-[11px] text-slate-400 hover:text-white flex-shrink-0 px-1.5 py-0.5 rounded bg-slate-800 border border-slate-700">
                        <span x-text="copiedToken ? 'Copied!' : 'Copy'"></span>
                    </button>
                </div>
            </div>

            <div class="p-3 rounded-lg bg-slate-800/80 border border-slate-700 text-[11px] text-slate-300 space-y-1.5">
                <div class="font-bold text-white flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Meta Setup Instructions
                </div>
                <ol class="list-decimal list-inside space-y-1 text-slate-400 text-[10px]">
                    <li>Open <strong>Meta App Dashboard</strong> &gt; <strong>Webhooks</strong>.</li>
                    <li>Select object <strong>Page</strong> or <strong>Leadgen</strong>.</li>
                    <li>Paste Callback URL &amp; Verify Token from above.</li>
                    <li>Subscribe to field <strong>leadgen</strong>.</li>
                </ol>
            </div>
        </div>

        <!-- Meta App Settings Form -->
        <div class="card p-6 lg:col-span-2 space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <h2 class="font-bold text-slate-800 text-base flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Meta Graph API &amp; App Credentials
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Configure access tokens to fetch full custom question answers and campaign names</p>
            </div>

            <form action="{{ route('meta.sync.settings') }}" method="POST" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Facebook Page ID / Business Account</label>
                        <input type="text" name="meta_page_id" value="{{ $settings['meta_page_id'] }}" class="form-control w-full text-xs font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Webhook Verify Token</label>
                        <input type="text" name="meta_verify_token" value="{{ $settings['meta_verify_token'] }}" required class="form-control w-full text-xs font-mono">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-600 mb-1">System User / Page Access Token (Long-Lived)</label>
                        <input type="password" name="meta_access_token" value="{{ $settings['meta_access_token'] }}" class="form-control w-full text-xs font-mono">
                        <span class="text-[10px] text-slate-400 mt-1 block">Requires <code>leads_retrieval, pages_manage_ads, pages_show_list</code> permissions.</span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">App Secret</label>
                        <input type="password" name="meta_app_secret" value="{{ $settings['meta_app_secret'] }}" class="form-control w-full text-xs font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Default Stage for Meta Inquiries</label>
                        <select name="meta_default_stage" class="form-control w-full text-xs font-bold">
                            @foreach($stages as $st)
                                <option value="{{ $st }}" {{ $settings['meta_default_stage'] === $st ? 'selected' : '' }}>{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="pt-2 flex justify-end">
                    <button type="submit" class="btn bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs px-5 py-2 rounded-lg shadow-sm">
                        Save Meta Integration
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Synced Meta Leads Table -->
    <div class="card overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-slate-800 text-sm">Recently Captured Meta Leads</h3>
                <p class="text-xs text-slate-500">Live feed of prospects captured from Facebook &amp; Instagram campaigns</p>
            </div>
            <a href="{{ route('leads.index', ['source' => 'Meta Ads']) }}" class="text-xs text-blue-600 hover:text-blue-800 font-semibold">View in Leads Table &rarr;</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4 font-semibold">Lead Info</th>
                        <th class="py-3 px-4 font-semibold">Contact</th>
                        <th class="py-3 px-4 font-semibold">Campaign &amp; Ad</th>
                        <th class="py-3 px-4 font-semibold">Stage</th>
                        <th class="py-3 px-4 font-semibold">Assigned Agent</th>
                        <th class="py-3 px-4 font-semibold">Captured At</th>
                        <th class="py-3 px-4 font-semibold text-right">Quick Dial</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($metaLeads as $lead)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900">{{ $lead->name }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $lead->lead_code }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-medium text-slate-900 flex items-center gap-1.5">
                                    {{ $lead->mobile }}
                                </div>
                                @if($lead->email)
                                    <div class="text-[10px] text-slate-400">{{ $lead->email }}</div>
                                @endif
                                @if($lead->city)
                                    <div class="text-[10px] text-slate-500">{{ $lead->city }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200 mb-0.5">
                                    {{ $lead->meta_campaign_name ?: 'Meta Lead Ads' }}
                                </span>
                                @if($lead->meta_ad_name)
                                    <div class="text-[10px] text-slate-500 truncate max-w-xs">{{ $lead->meta_ad_name }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $lead->stage === 'Won' ? 'bg-emerald-100 text-emerald-800' : ($lead->stage === 'Interested' ? 'bg-indigo-100 text-indigo-800' : 'bg-slate-100 text-slate-700') }}">
                                    {{ $lead->stage }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="text-slate-600 font-medium">{{ $lead->assignedUser?->name ?? 'Unassigned' }}</span>
                            </td>
                            <td class="py-3 px-4 text-slate-500 text-[11px]">
                                {{ $lead->created_at->diffForHumans() }}
                            </td>
                            <td class="py-3 px-4 text-right">
                                <button type="button"
                                        @click="$dispatch('open-dialer', { phone: '{{ $lead->mobile }}', name: '{{ addslashes($lead->name) }}', type: 'lead', id: {{ $lead->id }}, city: '{{ addslashes($lead->city ?? '') }}', badge: 'Meta Lead' })"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-teal-50 text-teal-700 hover:bg-teal-100 font-bold text-[11px] border border-teal-200 transition cursor-pointer">
                                    <svg class="w-3.5 h-3.5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    Dial
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">
                                No Meta Ads leads captured yet. Click "Simulate Incoming Meta Lead" to test!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($metaLeads->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $metaLeads->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
