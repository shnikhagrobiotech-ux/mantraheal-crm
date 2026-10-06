@extends('layouts.app')

@section('title', 'Bulk Lead Importer — MantraHeal CRM')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    assignmentMode: 'round_robin',
    fileName: '',
    onFileSelected(e) {
        if (e.target.files && e.target.files[0]) {
            this.fileName = e.target.files[0].name;
        }
    }
}">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('leads.index') }}" class="text-xs text-slate-500 hover:text-slate-700 font-medium">Leads</a>
                <span class="text-slate-300">/</span>
                <span class="text-xs text-teal-700 font-semibold">Bulk Importer</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2.5">
                <svg class="w-7 h-7 text-teal-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                Bulk Lead Importer
            </h1>
            <p class="text-sm text-slate-500">Upload CSV files of prospective buyers, inquiries, exhibition contacts, or downloaded ad leads</p>
        </div>

        <div>
            <a href="{{ route('leads.import.template') }}" class="btn bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 font-bold text-xs px-4 py-2 rounded-lg shadow-sm flex items-center gap-2 transition">
                <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Download Sample CSV Template
            </a>
        </div>
    </div>

    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center justify-between shadow-sm">
            <span>{{ session('error') }}</span>
            <button @click="$el.parentElement.remove()" class="text-rose-500 hover:text-rose-700">&times;</button>
        </div>
    @endif

    <!-- Main Import Form -->
    <form action="{{ route('leads.import.process') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div class="card p-6 space-y-6">
            <!-- Step 1: Upload File -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Step 1: Choose CSV File</label>
                <div class="border-2 border-dashed border-slate-300 hover:border-teal-500 rounded-2xl p-8 text-center bg-slate-50/50 hover:bg-teal-50/30 transition cursor-pointer relative">
                    <input type="file" name="csv_file" accept=".csv,.txt" required @change="onFileSelected"
                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                    <div class="space-y-3 pointer-events-none">
                        <div class="w-12 h-12 rounded-full bg-teal-100 text-teal-700 mx-auto flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div class="text-sm font-semibold text-slate-700" x-text="fileName ? fileName : 'Click to browse or drag and drop your CSV file here'"></div>
                        <div class="text-xs text-slate-400">Supported format: .csv, .txt (Comma, Semicolon, or Tab delimited; up to 5 MB)</div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Configuration & Duplicate Rules -->
            <div class="border-t border-slate-100 pt-6">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-4">Step 2: Import & Assignment Settings</label>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Duplicate Action -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Duplicate Handling (Match by Mobile / Email)</label>
                        <select name="duplicate_action" class="form-control w-full text-xs font-bold">
                            <option value="skip" selected>Skip Duplicate (Keep Existing Lead intact)</option>
                            <option value="update">Update Existing Lead (Merge data & log activity)</option>
                            <option value="allow">Allow Duplicates (Always create new lead)</option>
                        </select>
                        <span class="text-[10px] text-slate-400 mt-1 block">Phone numbers are sanitized to match 10-digit national format.</span>
                    </div>

                    <!-- Assignment Strategy -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Sales Executive Assignment</label>
                        <select name="assignment_mode" x-model="assignmentMode" class="form-control w-full text-xs font-bold">
                            <option value="round_robin">Round-Robin Auto-Distribution (Even split across agents)</option>
                            <option value="specific">Assign to Specific Sales Executive</option>
                            <option value="unassigned">Leave Unassigned (Open Pool)</option>
                        </select>
                    </div>

                    <!-- Specific User Select (Conditional) -->
                    <div x-show="assignmentMode === 'specific'" x-transition class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Select Sales Executive</label>
                        <select name="assigned_user_id" class="form-control w-full text-xs">
                            <option value="">-- Choose Sales Agent --</option>
                            @foreach($salesExecutives as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->role_slug }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Default Stage -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Default Stage for New Leads</label>
                        <select name="default_stage" class="form-control w-full text-xs font-bold">
                            @foreach($stages as $stage)
                                <option value="{{ $stage }}" {{ $stage === 'New' ? 'selected' : '' }}>{{ $stage }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Default Source -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Default Source (If missing in CSV)</label>
                        <select name="default_source" class="form-control w-full text-xs font-bold">
                            <option value="Bulk Import" selected>Bulk Import</option>
                            @foreach($sources as $source)
                                @if($source !== 'Bulk Import')
                                    <option value="{{ $source }}">{{ $source }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Submit Action -->
            <div class="border-t border-slate-100 pt-5 flex items-center justify-between">
                <a href="{{ route('leads.index') }}" class="btn btn-secondary text-xs">Cancel</a>
                <button type="submit" class="btn bg-teal-600 hover:bg-teal-500 text-white font-bold text-xs px-6 py-2.5 rounded-lg shadow-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Start Lead Import
                </button>
            </div>
        </div>
    </form>

    <!-- Field Specifications & Help -->
    <div class="card p-5 bg-white border border-slate-200">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-3 flex items-center gap-2">
            <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Supported CSV Column Header Aliases
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-100 text-slate-400 font-semibold">
                        <th class="pb-2">Field</th>
                        <th class="pb-2">Required?</th>
                        <th class="pb-2">Accepted Column Names (Case-Insensitive)</th>
                        <th class="pb-2">Example Value</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-600">
                    <tr>
                        <td class="py-2 font-bold text-slate-800">Full Name</td>
                        <td class="py-2"><span class="text-rose-600 font-semibold">Required</span></td>
                        <td class="py-2 font-mono text-[11px]">Name, Full Name, Customer Name, Lead Name</td>
                        <td class="py-2">Vikram Singhania</td>
                    </tr>
                    <tr>
                        <td class="py-2 font-bold text-slate-800">Mobile / Phone</td>
                        <td class="py-2"><span class="text-rose-600 font-semibold">Required</span></td>
                        <td class="py-2 font-mono text-[11px]">Mobile, Phone, Phone Number, Contact, Cell</td>
                        <td class="py-2">+91 98210 44556</td>
                    </tr>
                    <tr>
                        <td class="py-2 font-bold text-slate-800">Email</td>
                        <td class="py-2 text-slate-400">Optional</td>
                        <td class="py-2 font-mono text-[11px]">Email, Email Address, Mail</td>
                        <td class="py-2">vikram@example.com</td>
                    </tr>
                    <tr>
                        <td class="py-2 font-bold text-slate-800">City / State / PIN</td>
                        <td class="py-2 text-slate-400">Optional</td>
                        <td class="py-2 font-mono text-[11px]">City, Location, State, Province, Pincode, Zip</td>
                        <td class="py-2">Jaipur, Rajasthan, 302001</td>
                    </tr>
                    <tr>
                        <td class="py-2 font-bold text-slate-800">Estimated Value</td>
                        <td class="py-2 text-slate-400">Optional</td>
                        <td class="py-2 font-mono text-[11px]">Estimated Value, Value, Budget, Amount</td>
                        <td class="py-2">2499</td>
                    </tr>
                    <tr>
                        <td class="py-2 font-bold text-slate-800">Notes / Query</td>
                        <td class="py-2 text-slate-400">Optional</td>
                        <td class="py-2 font-mono text-[11px]">Notes, Remarks, Comment, Query, Inquiry</td>
                        <td class="py-2">Inquired about Himalayan Shilajit</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
