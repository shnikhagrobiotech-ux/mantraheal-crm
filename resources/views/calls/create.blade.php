@extends('layouts.app')

@section('title', 'Log Sales Call')
@section('subtitle', 'Record call outcome, duration, notes, and attach audio file')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="card-elevated p-6">
        <form method="POST" action="{{ route('calls.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            
            @if($customer)
                <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                <div class="p-3 bg-teal-50 border border-teal-200 rounded-lg text-xs text-teal-800 flex items-center justify-between">
                    <span>Logging call for Customer: <strong>{{ $customer->name }}</strong> ({{ $customer->mobile }})</span>
                    <a href="{{ route('customers.show', $customer->id) }}" class="underline font-semibold">View 360</a>
                </div>
            @elseif($lead)
                <input type="hidden" name="lead_id" value="{{ $lead->id }}">
                <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg text-xs text-blue-800 flex items-center justify-between">
                    <span>Logging call for Lead: <strong>{{ $lead->name }}</strong> ({{ $lead->mobile }})</span>
                    <a href="{{ route('leads.show', $lead->id) }}" class="underline font-semibold">View Lead</a>
                </div>
            @else
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Select Customer (Optional)</label>
                    <select name="customer_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white">
                        <option value="">-- Choose Customer --</option>
                        @foreach(\App\Models\Customer::orderBy('name')->get() as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->mobile }})</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Call Direction *</label>
                    <select name="direction" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white">
                        <option value="outgoing">Outgoing (We called)</option>
                        <option value="incoming">Incoming (Customer called)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Date & Time *</label>
                    <input type="datetime-local" name="call_datetime" value="{{ now()->format('Y-m-d\TH:i') }}" required
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Duration (Seconds) *</label>
                    <input type="number" name="duration_seconds" value="{{ old('duration_seconds', 180) }}" required min="0"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Call Status *</label>
                    <select name="status" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white">
                        <option value="Completed">Completed</option>
                        <option value="Missed">Missed</option>
                        <option value="Busy">Busy</option>
                        <option value="Rejected">Rejected</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Outcome *</label>
                    <select name="outcome" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white">
                        @foreach($outcomes as $out)
                            <option value="{{ $out }}" {{ $out === 'Interested' ? 'selected' : '' }}>{{ $out }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Call Discussion Notes</label>
                <textarea name="notes" rows="3" placeholder="Discussed product health benefits, recommended 3-month course of Triphala juice..."
                          class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs">{{ old('notes') }}</textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 border-t border-slate-100 pt-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Next Follow-up Date (Optional)</label>
                    <input type="date" name="follow_up_date" value="{{ old('follow_up_date') }}"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs">
                    <span class="text-[10px] text-slate-400">Will automatically create a follow-up task</span>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Attach Audio Recording (WAV/MP3)</label>
                    <input type="file" name="recording_file" accept="audio/*"
                           class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-xs bg-white">
                    <span class="text-[10px] text-slate-400">Stored in /storage/app/call-recordings/</span>
                </div>
            </div>

            <div class="pt-3 flex items-center justify-end space-x-3">
                <a href="{{ route('calls.index') }}" class="btn-secondary text-xs">Cancel</a>
                <button type="submit" class="btn-primary text-xs py-2 px-5">Save Call Record</button>
            </div>
        </form>
    </div>
</div>
@endsection
