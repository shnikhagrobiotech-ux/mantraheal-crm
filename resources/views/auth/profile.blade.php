@extends('layouts.app')

@section('title', 'User Profile')
@section('subtitle', 'Manage your account details and password')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="card-elevated p-6">
        <h3 class="text-base font-bold text-slate-800 border-b border-slate-100 pb-3 mb-4">Account Information</h3>
        <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Full Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Email (Read Only)</label>
                    <input type="email" value="{{ $user->email }}" disabled
                           class="w-full px-3 py-2 border border-slate-200 bg-slate-100 rounded-lg text-sm text-slate-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Mobile / WhatsApp</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Assigned Role</label>
                    <input type="text" value="{{ $user->role?->name ?? 'Sales Executive' }}" disabled
                           class="w-full px-3 py-2 border border-slate-200 bg-slate-100 rounded-lg text-sm text-slate-500">
                </div>
            </div>

            <div class="border-t border-slate-100 pt-4 mt-4">
                <h4 class="text-sm font-semibold text-slate-700 mb-2">Change Password</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">New Password</label>
                        <input type="password" name="password" placeholder="Leave blank to keep current"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Confirm Password</label>
                        <input type="password" name="password_confirmation" placeholder="Repeat new password"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    </div>
                </div>
            </div>

            <div class="pt-3 text-right">
                <button type="submit" class="btn-primary text-sm py-2 px-5">Save Profile Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection
