@extends('layouts.app')

@section('title', 'Open Support Ticket — MantraHeal CRM')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header -->
    <div>
        <a href="{{ route('support.index') }}" class="text-sm font-medium text-emerald-700 hover:text-emerald-800 flex items-center gap-1 mb-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Support Tickets
        </a>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Open Support Ticket / Log Complaint</h1>
        <p class="text-sm text-slate-500">Record customer queries, formulation inquiries, or shipping escalations</p>
    </div>

    @if($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
            <div class="font-semibold mb-1">Please fix the following errors:</div>
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('support.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="card p-6 space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Customer *</label>
                    <select name="customer_id" required class="form-control w-full text-sm">
                        <option value="">-- Choose Customer --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ (old('customer_id', $customer?->id) == $c->id) ? 'selected' : '' }}>
                                {{ $c->name }} ({{ $c->phone }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Related Order Number</label>
                    @if($order)
                        <input type="hidden" name="order_id" value="{{ $order->id }}">
                        <input type="text" readonly value="{{ $order->order_number }}" class="form-control w-full text-sm bg-slate-50">
                    @else
                        <input type="number" name="order_id" placeholder="Order ID (optional)" value="{{ old('order_id') }}" class="form-control w-full text-sm font-mono">
                    @endif
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Ticket Subject *</label>
                    <input type="text" name="subject" value="{{ old('subject') }}" required placeholder="e.g. Inquiry regarding dosage timing for Shilajit Resin" class="form-control w-full text-sm">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Category *</label>
                    <select name="category" required class="form-control w-full text-sm">
                        <option value="Delivery Delay">Delivery Delay / Courier Issue</option>
                        <option value="Damaged Product">Damaged / Leaked Product</option>
                        <option value="Dosage Inquiry">Dosage & Ayurvedic Consultation</option>
                        <option value="Payment Issue">Payment / Billing Discrepancy</option>
                        <option value="Wrong Item">Wrong Formulation Received</option>
                        <option value="Other">General Inquiry</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Priority Level *</label>
                    <select name="priority" required class="form-control w-full text-sm">
                        <option value="Low">Low (General Query)</option>
                        <option value="Medium" selected>Medium (Standard Support)</option>
                        <option value="High">High (Transit Delay)</option>
                        <option value="Urgent">Urgent (Customer Escalation)</option>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Assign Support Executive</label>
                    <select name="assigned_user_id" class="form-control w-full text-sm">
                        <option value="">-- Assign to Me / Auto --</option>
                        @foreach($supportAgents as $agent)
                            <option value="{{ $agent->id }}" {{ old('assigned_user_id') == $agent->id ? 'selected' : '' }}>
                                {{ $agent->name }} ({{ $agent->role_slug }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Detailed Description *</label>
                    <textarea name="description" rows="4" required placeholder="Detailed notes on customer statement, symptoms, or shipping issue..." class="form-control w-full text-sm">{{ old('description') }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('support.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary px-6 py-2.5">
                Create Support Ticket
            </button>
        </div>
    </form>
</div>
@endsection
