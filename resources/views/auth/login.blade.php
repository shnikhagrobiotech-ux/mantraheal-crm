@extends('layouts.guest')

@section('title', 'Sign In')

@section('content')
<div x-data="{
    showPassword: false,
    email: '{{ old('email', 'admin@mantraheal.com') }}',
    password: 'password123',
    selectedRole: 'admin',
    setRole(role, email) {
        this.selectedRole = role;
        this.email = email;
        this.password = 'password123';
    },
    instantLogin(role, email) {
        this.setRole(role, email);
        this.$nextTick(() => {
            this.$refs.loginForm.submit();
        });
    }
}">
    <!-- Brand Header -->
    <div class="text-center">
        <div class="inline-flex justify-center mb-3">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-teal-500 to-emerald-600 flex items-center justify-center text-white font-black text-2xl shadow-xl shadow-teal-500/30 border border-teal-400/20">
                MH
            </div>
        </div>
        <h1 class="text-2xl font-black tracking-tight text-white">
            {{ str_ends_with(config('mantraheal.company_name', 'MantraHeal'), 'CRM') ? config('mantraheal.company_name', 'MantraHeal') : config('mantraheal.company_name', 'MantraHeal') . ' CRM' }}
        </h1>
        <p class="mt-1 text-xs text-teal-400 font-semibold tracking-wider uppercase">
            Ayurvedic E-Commerce & Tele-Sales Operations
        </p>
    </div>

    <!-- Validation / Error Alert -->
    @if ($errors->any())
        <div class="mt-4 p-3.5 rounded-xl bg-rose-500/20 border border-rose-500/40 text-rose-200 text-xs flex items-center gap-2.5">
            <svg class="w-4 h-4 text-rose-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span class="font-medium">{{ $errors->first() }}</span>
        </div>
    @endif

    <!-- Login Form -->
    <form x-ref="loginForm" class="mt-5 space-y-4" action="{{ route('login.post') }}" method="POST">
        @csrf
        <div>
            <label for="email" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                Staff Email Address
            </label>
            <div class="relative">
                <input id="email" name="email" type="email" autocomplete="email" required
                       x-model="email"
                       class="dark-input w-full block text-white font-medium"
                       placeholder="user@mantraheal.com">
                <span class="absolute right-3 top-2.5 text-slate-400 pointer-events-none">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                </span>
            </div>
        </div>

        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-xs font-bold text-slate-300 uppercase tracking-wider">
                    Password
                </label>
                <button type="button" @click="showPassword = !showPassword" class="text-[11px] text-teal-400 hover:text-teal-300 font-medium">
                    <span x-text="showPassword ? 'Hide password' : 'Show password'"></span>
                </button>
            </div>
            <div class="relative">
                <input id="password" name="password" :type="showPassword ? 'text' : 'password'" required
                       x-model="password"
                       class="dark-input w-full block text-white font-medium pr-10"
                       placeholder="••••••••">
                <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-2.5 text-slate-400 hover:text-white">
                    <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <svg x-show="showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                </button>
            </div>
        </div>

        <div class="flex items-center justify-between text-xs pt-1">
            <label class="flex items-center text-slate-300 cursor-pointer">
                <input type="checkbox" name="remember" checked class="rounded bg-slate-800 border-slate-700 text-teal-500 focus:ring-teal-500 h-4 w-4">
                <span class="ml-2 font-medium">Keep me signed in</span>
            </label>
            <span class="text-teal-400 font-mono text-[11px] bg-teal-950/60 px-2 py-0.5 rounded border border-teal-800/40">
                Default: password123
            </span>
        </div>

        <button type="submit" class="btn-login w-full text-center flex items-center justify-center gap-2 mt-2">
            <span>Sign in to MantraHeal</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
        </button>
    </form>

    <!-- Quick Role Switch (One-Click Local Dev Login) -->
    <div class="mt-6 pt-5 border-t border-slate-800/80">
        <div class="flex items-center justify-between mb-2.5">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Quick Fill & Login Presets:</span>
            <span class="text-[10px] text-teal-400 font-medium">Click to fill or double-click to login</span>
        </div>

        <div class="grid grid-cols-2 gap-2">
            <button type="button" 
                    @click="setRole('admin', 'admin@mantraheal.com')"
                    @dblclick="instantLogin('admin', 'admin@mantraheal.com')"
                    :class="selectedRole === 'admin' ? 'border-teal-500 bg-slate-800 text-teal-300 ring-1 ring-teal-500/50' : 'bg-slate-800/80 text-slate-300 border-slate-700'"
                    class="btn-role-preset flex flex-col p-2 rounded-lg border text-left transition">
                <div class="font-bold flex items-center justify-between text-xs">
                    <span>👑 Super Admin</span>
                    <span class="text-[9px] px-1.5 py-0.2 rounded bg-teal-900/60 text-teal-300">All Access</span>
                </div>
                <div class="text-[10px] text-slate-400 truncate mt-0.5">admin@mantraheal.com</div>
            </button>

            <button type="button" 
                    @click="setRole('sales', 'rahul.sales@mantraheal.com')"
                    @dblclick="instantLogin('sales', 'rahul.sales@mantraheal.com')"
                    :class="selectedRole === 'sales' ? 'border-teal-500 bg-slate-800 text-teal-300 ring-1 ring-teal-500/50' : 'bg-slate-800/80 text-slate-300 border-slate-700'"
                    class="btn-role-preset flex flex-col p-2 rounded-lg border text-left transition">
                <div class="font-bold flex items-center justify-between text-xs">
                    <span>📞 Sales Exec</span>
                    <span class="text-[9px] px-1.5 py-0.2 rounded bg-blue-900/60 text-blue-300">Telesales</span>
                </div>
                <div class="text-[10px] text-slate-400 truncate mt-0.5">rahul.sales@mantraheal.com</div>
            </button>

            <button type="button" 
                    @click="setRole('inventory', 'stock@mantraheal.com')"
                    @dblclick="instantLogin('inventory', 'stock@mantraheal.com')"
                    :class="selectedRole === 'inventory' ? 'border-teal-500 bg-slate-800 text-teal-300 ring-1 ring-teal-500/50' : 'bg-slate-800/80 text-slate-300 border-slate-700'"
                    class="btn-role-preset flex flex-col p-2 rounded-lg border text-left transition">
                <div class="font-bold flex items-center justify-between text-xs">
                    <span>📦 Inventory Mgr</span>
                    <span class="text-[9px] px-1.5 py-0.2 rounded bg-amber-900/60 text-amber-300">Warehouses</span>
                </div>
                <div class="text-[10px] text-slate-400 truncate mt-0.5">stock@mantraheal.com</div>
            </button>

            <button type="button" 
                    @click="setRole('support', 'support@mantraheal.com')"
                    @dblclick="instantLogin('support', 'support@mantraheal.com')"
                    :class="selectedRole === 'support' ? 'border-teal-500 bg-slate-800 text-teal-300 ring-1 ring-teal-500/50' : 'bg-slate-800/80 text-slate-300 border-slate-700'"
                    class="btn-role-preset flex flex-col p-2 rounded-lg border text-left transition">
                <div class="font-bold flex items-center justify-between text-xs">
                    <span>🎧 Support Staff</span>
                    <span class="text-[9px] px-1.5 py-0.2 rounded bg-purple-900/60 text-purple-300">Helpdesk</span>
                </div>
                <div class="text-[10px] text-slate-400 truncate mt-0.5">support@mantraheal.com</div>
            </button>
        </div>
    </div>
</div>
@endsection
