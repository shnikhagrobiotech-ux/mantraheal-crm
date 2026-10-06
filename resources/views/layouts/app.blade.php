<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ config('mantraheal.company_name', 'MantraHeal CRM') }}</title>
    
    <!-- Modern Typography (Google Fonts with System Fallbacks) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">

    <!-- Local CSS & Modern Theme -->
    <link rel="stylesheet" href="/css/tailwind.min.css">
    <link rel="stylesheet" href="/css/mantraheal.css">

    <!-- Local JavaScript -->
    <script src="/js/alpine.min.js" defer></script>
    <script src="/js/chart.min.js"></script>
    <script src="/js/mantraheal.js"></script>
</head>
<body class="h-full antialiased text-slate-800 bg-slate-50/70" x-data="{ sidebarOpen: false, userDropdown: false }">
    <div class="min-h-full flex">
        <!-- Sidebar Navigation -->
        <aside class="w-68 bg-[#0b1120] text-slate-300 flex flex-col flex-shrink-0 transition-all duration-300 z-30 fixed inset-y-0 left-0 lg:static border-r border-slate-800/80 shadow-2xl lg:shadow-none"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
            
            <!-- Brand Header -->
            <div class="h-16 flex items-center justify-between px-5 bg-[#070b14] border-b border-slate-800/90">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-teal-500 via-teal-600 to-emerald-500 flex items-center justify-center text-white font-extrabold text-base shadow-glow-teal ring-2 ring-teal-400/20 group-hover:scale-105 transition-transform duration-200">
                        MH
                    </div>
                    <div>
                        <div class="text-white font-bold tracking-tight text-base font-['Outfit'] flex items-center gap-1.5">
                            {{ config('mantraheal.company_name', 'MantraHeal') }}
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-teal-400 animate-pulse"></span>
                        </div>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <span class="text-[10px] text-teal-400 font-semibold tracking-wider uppercase">CRM Enterprise</span>
                            <span class="text-[9px] px-1.5 py-0.2 rounded bg-teal-500/10 text-teal-300 border border-teal-500/20 font-bold">v2.5</span>
                        </div>
                    </div>
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Navigation Links -->
            <div class="flex-1 overflow-y-auto px-3 py-4 space-y-1 text-xs custom-scrollbar">
                <!-- Dashboard -->
                <a href="{{ route('dashboard') }}" class="nav-item-transition flex items-center px-3 py-2.5 rounded-lg font-semibold transition {{ request()->routeIs('dashboard') ? 'bg-gradient-to-r from-teal-600 to-emerald-600 text-white shadow-glow-teal' : 'text-slate-400 hover:text-white hover:bg-slate-800/70 font-medium' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    Dashboard
                </a>

                <!-- SALES -->
                <div class="pt-4 pb-1.5 px-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest flex items-center gap-2">
                    <span>Sales & Leads</span>
                    <span class="flex-1 h-px bg-slate-800/60"></span>
                </div>
                <a href="{{ route('leads.index') }}" class="nav-item-transition flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('leads.index') || request()->routeIs('leads.show') || request()->routeIs('leads.create') ? 'bg-gradient-to-r from-teal-600 to-emerald-600 text-white shadow-glow-teal font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/70' }}">
                    <svg class="w-4 h-4 mr-3 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                    Leads Pipeline
                </a>
                <a href="{{ route('leads.import') }}" class="nav-item-transition flex items-center px-3 py-1.5 rounded-lg text-xs font-medium transition pl-8 {{ request()->routeIs('leads.import*') ? 'bg-teal-700/80 text-white font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/70' }}">
                    <svg class="w-3.5 h-3.5 mr-2 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Import CSV Leads
                </a>
                <a href="{{ route('customers.index') }}" class="nav-item-transition flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('customers.*') ? 'bg-gradient-to-r from-teal-600 to-emerald-600 text-white shadow-glow-teal font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/70' }}">
                    <svg class="w-4 h-4 mr-3 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    Customers 360
                </a>
                <a href="{{ route('calls.index') }}" class="nav-item-transition flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('calls.*') ? 'bg-gradient-to-r from-teal-600 to-emerald-600 text-white shadow-glow-teal font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/70' }}">
                    <svg class="w-4 h-4 mr-3 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                    Sales Calls Log
                </a>
                <a href="{{ route('followups.index') }}" class="nav-item-transition flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('followups.*') ? 'bg-gradient-to-r from-teal-600 to-emerald-600 text-white shadow-glow-teal font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/70' }}">
                    <svg class="w-4 h-4 mr-3 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    Follow-ups
                </a>

                <!-- ORDERS -->
                <div class="pt-4 pb-1.5 px-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest flex items-center gap-2">
                    <span>Order Lifecycle</span>
                    <span class="flex-1 h-px bg-slate-800/60"></span>
                </div>
                <a href="{{ route('orders.index') }}" class="nav-item-transition flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('orders.index') && !request('status') ? 'bg-gradient-to-r from-teal-600 to-emerald-600 text-white shadow-glow-teal font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/70' }}">
                    <svg class="w-4 h-4 mr-3 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    All Orders
                </a>
                <a href="{{ route('orders.index', ['status' => 'New']) }}" class="nav-item-transition flex items-center px-3 py-1.5 rounded-lg text-xs font-medium transition pl-8 {{ request('status') === 'New' ? 'bg-sky-600/80 text-white font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/70' }}">
                    <span class="w-2 h-2 rounded-full bg-sky-400 mr-2.5"></span>
                    Pending Orders
                </a>
                <a href="{{ route('orders.index', ['status' => 'Processing']) }}" class="nav-item-transition flex items-center px-3 py-1.5 rounded-lg text-xs font-medium transition pl-8 {{ request('status') === 'Processing' ? 'bg-amber-600/80 text-white font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/70' }}">
                    <span class="w-2 h-2 rounded-full bg-amber-400 mr-2.5"></span>
                    Processing
                </a>
                <a href="{{ route('orders.index', ['status' => 'Shipped']) }}" class="nav-item-transition flex items-center px-3 py-1.5 rounded-lg text-xs font-medium transition pl-8 {{ request('status') === 'Shipped' ? 'bg-indigo-600/80 text-white font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/70' }}">
                    <span class="w-2 h-2 rounded-full bg-indigo-400 mr-2.5"></span>
                    Shipped
                </a>
                <a href="{{ route('orders.index', ['status' => 'Delivered']) }}" class="nav-item-transition flex items-center px-3 py-1.5 rounded-lg text-xs font-medium transition pl-8 {{ request('status') === 'Delivered' ? 'bg-emerald-600/80 text-white font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/70' }}">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 mr-2.5"></span>
                    Delivered
                </a>
                <a href="{{ route('deliveries.index') }}" class="nav-item-transition flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('deliveries.*') ? 'bg-gradient-to-r from-indigo-600 to-blue-600 text-white shadow-glow-indigo font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/70' }}">
                    <svg class="w-4 h-4 mr-3 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                    Delivery & Dispatch
                </a>
                <a href="{{ route('returns.index') }}" class="nav-item-transition flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('returns.*') ? 'bg-gradient-to-r from-teal-600 to-emerald-600 text-white shadow-glow-teal font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/70' }}">
                    <svg class="w-4 h-4 mr-3 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                    Returns & QC
                </a>
                <a href="{{ route('refunds.index') }}" class="nav-item-transition flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('refunds.*') ? 'bg-gradient-to-r from-teal-600 to-emerald-600 text-white shadow-glow-teal font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/70' }}">
                    <svg class="w-4 h-4 mr-3 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Refunds
                </a>
                <a href="{{ route('rto.index') }}" class="nav-item-transition flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('rto.*') ? 'bg-gradient-to-r from-teal-600 to-emerald-600 text-white shadow-glow-teal font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/70' }}">
                    <svg class="w-4 h-4 mr-3 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    RTO Management
                </a>

                <!-- PRODUCTS & INVENTORY -->
                <div class="pt-4 pb-1.5 px-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest flex items-center gap-2">
                    <span>Products & Stock</span>
                    <span class="flex-1 h-px bg-slate-800/60"></span>
                </div>
                <a href="{{ route('products.index') }}" class="nav-item-transition flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('products.*') ? 'bg-gradient-to-r from-teal-600 to-emerald-600 text-white shadow-glow-teal font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/70' }}">
                    <svg class="w-4 h-4 mr-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    Catalog Products
                </a>
                <a href="{{ route('categories.index') }}" class="nav-item-transition flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('categories.*') ? 'bg-gradient-to-r from-teal-600 to-emerald-600 text-white shadow-glow-teal font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/70' }}">
                    <svg class="w-4 h-4 mr-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                    Categories
                </a>
                <a href="{{ route('inventory.index') }}" class="nav-item-transition flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('inventory.index') ? 'bg-gradient-to-r from-teal-600 to-emerald-600 text-white shadow-glow-teal font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/70' }}">
                    <svg class="w-4 h-4 mr-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    Inventory Live
                </a>
                <a href="{{ route('inventory.ledger') }}" class="nav-item-transition flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('inventory.ledger') ? 'bg-gradient-to-r from-teal-600 to-emerald-600 text-white shadow-glow-teal font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/70' }}">
                    <svg class="w-4 h-4 mr-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Stock Ledger
                </a>
                <a href="{{ route('warehouses.index') }}" class="nav-item-transition flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('warehouses.*') ? 'bg-gradient-to-r from-teal-600 to-emerald-600 text-white shadow-glow-teal font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/70' }}">
                    <svg class="w-4 h-4 mr-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    Warehouses
                </a>
                <a href="{{ route('batches.index') }}" class="nav-item-transition flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('batches.index') ? 'bg-gradient-to-r from-teal-600 to-emerald-600 text-white shadow-glow-teal font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/70' }}">
                    <svg class="w-4 h-4 mr-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    Batches & FEFO
                </a>
                <a href="{{ route('batches.expiry-alerts') }}" class="nav-item-transition flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('batches.expiry-alerts') ? 'bg-gradient-to-r from-teal-600 to-emerald-600 text-white shadow-glow-teal font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/70' }}">
                    <svg class="w-4 h-4 mr-3 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Expiry Alerts
                </a>

                <!-- PURCHASE -->
                <div class="pt-4 pb-1.5 px-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest flex items-center gap-2">
                    <span>Purchase & Supply</span>
                    <span class="flex-1 h-px bg-slate-800/60"></span>
                </div>
                <a href="{{ route('suppliers.index') }}" class="nav-item-transition flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('suppliers.*') ? 'bg-gradient-to-r from-teal-600 to-emerald-600 text-white shadow-glow-teal font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/70' }}">
                    <svg class="w-4 h-4 mr-3 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Suppliers
                </a>
                <a href="{{ route('purchase-orders.index') }}" class="nav-item-transition flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('purchase-orders.*') ? 'bg-gradient-to-r from-teal-600 to-emerald-600 text-white shadow-glow-teal font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/70' }}">
                    <svg class="w-4 h-4 mr-3 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    Purchase Orders
                </a>
                <a href="{{ route('grn.index') }}" class="nav-item-transition flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('grn.*') ? 'bg-gradient-to-r from-teal-600 to-emerald-600 text-white shadow-glow-teal font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/70' }}">
                    <svg class="w-4 h-4 mr-3 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    GRN (Goods Receipt)
                </a>

                <!-- COMMUNICATION -->
                <div class="pt-4 pb-1.5 px-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest flex items-center gap-2">
                    <span>Communication</span>
                    <span class="flex-1 h-px bg-slate-800/60"></span>
                </div>
                <a href="{{ route('communication.whatsapp') }}" class="nav-item-transition flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('communication.whatsapp*') ? 'bg-gradient-to-r from-teal-600 to-emerald-600 text-white shadow-glow-teal font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/70' }}">
                    <svg class="w-4 h-4 mr-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                    WhatsApp Messenger
                </a>
                <a href="{{ route('shopify.sync.index') }}" class="nav-item-transition flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('shopify.sync.*') ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-glow-emerald font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/70' }}">
                    <svg class="w-4 h-4 mr-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    Shopify Sync Hub
                </a>
                <a href="{{ route('call-recordings.index') }}" class="nav-item-transition flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('call-recordings.*') ? 'bg-gradient-to-r from-teal-600 to-emerald-600 text-white shadow-glow-teal font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/70' }}">
                    <svg class="w-4 h-4 mr-3 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z"></path></svg>
                    Call Recordings
                </a>

                <!-- MARKETING -->
                <div class="pt-4 pb-1.5 px-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest flex items-center gap-2">
                    <span>Marketing & Ads</span>
                    <span class="flex-1 h-px bg-slate-800/60"></span>
                </div>
                <a href="{{ route('marketing.index') }}" class="nav-item-transition flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('marketing.*') ? 'bg-gradient-to-r from-teal-600 to-emerald-600 text-white shadow-glow-teal font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/70' }}">
                    <svg class="w-4 h-4 mr-3 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path></svg>
                    Campaigns & Sources
                </a>
                <a href="{{ route('meta.sync.index') }}" class="nav-item-transition flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('meta.sync.*') ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-glow-indigo font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/70' }}">
                    <svg class="w-4 h-4 mr-3 text-blue-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.04c-5.5 0-10 4.49-10 10.02 0 5 3.66 9.15 8.44 9.9v-7H7.9v-2.9h2.54V9.85c0-2.51 1.49-3.89 3.78-3.89 1.09 0 2.23.19 2.23.19v2.47h-1.26c-1.24 0-1.63.77-1.63 1.56v1.88h2.78l-.45 2.9h-2.33v7a10 10 0 008.44-9.9c0-5.53-4.5-10.02-10-10.02z"/></svg>
                    Meta Ads Sync Hub
                </a>

                <!-- SUPPORT -->
                <div class="pt-4 pb-1.5 px-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest flex items-center gap-2">
                    <span>Helpdesk Support</span>
                    <span class="flex-1 h-px bg-slate-800/60"></span>
                </div>
                <a href="{{ route('support.index') }}" class="nav-item-transition flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('support.*') ? 'bg-gradient-to-r from-teal-600 to-emerald-600 text-white shadow-glow-teal font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/70' }}">
                    <svg class="w-4 h-4 mr-3 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    Tickets & Complaints
                </a>

                <!-- REPORTS -->
                <div class="pt-4 pb-1.5 px-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest flex items-center gap-2">
                    <span>Analytics & Reports</span>
                    <span class="flex-1 h-px bg-slate-800/60"></span>
                </div>
                <a href="{{ route('reports.sales') }}" class="nav-item-transition flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('reports.sales') ? 'bg-gradient-to-r from-teal-600 to-emerald-600 text-white shadow-glow-teal font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/70' }}">
                    <svg class="w-4 h-4 mr-3 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    Sales Reports
                </a>
                <a href="{{ route('reports.customers') }}" class="nav-item-transition flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('reports.customers') ? 'bg-gradient-to-r from-teal-600 to-emerald-600 text-white shadow-glow-teal font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/70' }}">
                    <svg class="w-4 h-4 mr-3 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    Customer Reports
                </a>
                <a href="{{ route('reports.calls') }}" class="nav-item-transition flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('reports.calls') ? 'bg-gradient-to-r from-teal-600 to-emerald-600 text-white shadow-glow-teal font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/70' }}">
                    <svg class="w-4 h-4 mr-3 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                    Call Reports
                </a>
                <a href="{{ route('reports.inventory') }}" class="nav-item-transition flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('reports.inventory') ? 'bg-gradient-to-r from-teal-600 to-emerald-600 text-white shadow-glow-teal font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/70' }}">
                    <svg class="w-4 h-4 mr-3 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    Inventory Reports
                </a>
                <a href="{{ route('reports.rto') }}" class="nav-item-transition flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('reports.rto') ? 'bg-gradient-to-r from-teal-600 to-emerald-600 text-white shadow-glow-teal font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/70' }}">
                    <svg class="w-4 h-4 mr-3 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path></svg>
                    RTO Reports
                </a>

                <!-- EMPLOYEES & AUDIT -->
                <div class="pt-4 pb-1.5 px-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest flex items-center gap-2">
                    <span>Administration</span>
                    <span class="flex-1 h-px bg-slate-800/60"></span>
                </div>
                <a href="{{ route('employees.index') }}" class="nav-item-transition flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('employees.*') ? 'bg-gradient-to-r from-teal-600 to-emerald-600 text-white shadow-glow-teal font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/70' }}">
                    <svg class="w-4 h-4 mr-3 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    Employees & Roles
                </a>
                <a href="{{ route('audit.index') }}" class="nav-item-transition flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('audit.*') ? 'bg-gradient-to-r from-teal-600 to-emerald-600 text-white shadow-glow-teal font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/70' }}">
                    <svg class="w-4 h-4 mr-3 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Audit Logs
                </a>
                <a href="{{ route('settings.index') }}" class="nav-item-transition flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('settings.*') ? 'bg-gradient-to-r from-teal-600 to-emerald-600 text-white shadow-glow-teal font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/70' }}">
                    <svg class="w-4 h-4 mr-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    System Settings
                </a>
            </div>

            <!-- Current User Footer -->
            <div class="p-3 bg-[#070b14] border-t border-slate-800/90">
                <div class="p-2.5 rounded-xl bg-slate-900/90 border border-slate-800/90 flex items-center justify-between shadow-sm">
                    <div class="flex items-center space-x-3 truncate">
                        <div class="relative flex-shrink-0">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-teal-500 to-emerald-700 text-white flex items-center justify-center font-bold text-xs uppercase shadow-sm">
                                {{ substr(auth()->user()->name ?? 'U', 0, 2) }}
                            </div>
                            <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 bg-emerald-500 border-2 border-slate-900 rounded-full"></span>
                        </div>
                        <div class="truncate">
                            <div class="text-xs font-semibold text-slate-100 truncate">{{ auth()->user()->name ?? 'User' }}</div>
                            <div class="text-[10px] text-teal-400 font-medium capitalize truncate">{{ str_replace('_', ' ', auth()->user()->role_slug ?? 'Admin') }}</div>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Logout" class="text-slate-400 hover:text-rose-400 p-1.5 rounded-lg hover:bg-slate-800 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Top Navigation Bar -->
            <header class="h-16 glass-header sticky top-0 border-b border-slate-200/80 flex items-center justify-between px-4 sm:px-6 lg:px-8 z-20">
                <div class="flex items-center space-x-4">
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden text-slate-600 hover:text-slate-900 p-2 rounded-xl border border-slate-200 hover:bg-slate-100 transition shadow-2xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-lg font-bold text-slate-900 leading-tight">@yield('title', 'Dashboard')</h1>
                            <span class="hidden md:inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                Live Engine
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 hidden sm:block">@yield('subtitle', 'MantraHeal E-Commerce Management System')</p>
                    </div>
                </div>

                <!-- Center Quick Order / Customer Search -->
                <form method="GET" action="{{ route('orders.index') }}" class="hidden lg:flex items-center relative max-w-xs w-full">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="search" placeholder="Search orders, phone, tracking..." value="{{ request('search') }}"
                           class="w-full text-xs pl-9 pr-8 py-1.5 rounded-lg bg-slate-100/90 hover:bg-slate-50 focus:bg-white border border-slate-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 text-slate-800 placeholder-slate-400 transition" />
                    <span class="absolute right-2.5 px-1 py-0.2 text-[9px] font-semibold text-slate-400 bg-white border border-slate-200 rounded">↵</span>
                </form>

                <!-- Right Quick Actions -->
                <div class="flex items-center space-x-2.5">
                    <a href="{{ route('orders.create') }}" class="btn-primary text-xs py-1.5 px-3 rounded-lg shadow-glow-teal flex items-center gap-1.5 font-semibold">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        <span>New Order</span>
                    </a>
                    <a href="{{ route('leads.create') }}" class="btn-secondary text-xs py-1.5 px-3 rounded-lg flex items-center gap-1.5 font-semibold hidden sm:inline-flex">
                        <svg class="w-3.5 h-3.5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                        <span>New Lead</span>
                    </a>

                    <!-- Softphone Quick Dialer -->
                    <button type="button" 
                            @click="$dispatch('open-dialer')" 
                            class="p-1.5 text-slate-600 hover:text-teal-700 bg-slate-50 hover:bg-teal-50/60 rounded-lg transition flex items-center gap-1.5 border border-slate-200 shadow-2xs cursor-pointer" 
                            title="Open Softphone Dialer">
                        <svg class="w-4 h-4 text-teal-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        <span class="text-xs font-bold text-slate-700 hidden xl:inline">Dialer</span>
                    </button>

                    <!-- Profile Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center gap-1.5 text-sm text-slate-700 hover:text-slate-900 focus:outline-none p-1 rounded-xl hover:bg-slate-100 transition border border-transparent hover:border-slate-200">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-teal-600 to-emerald-600 text-white font-bold flex items-center justify-center text-xs shadow-sm">
                                {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                            </div>
                            <svg class="w-3.5 h-3.5 text-slate-400 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-slate-200 py-1.5 z-50 divide-y divide-slate-100" style="display: none;">
                            <div class="px-4 py-2.5">
                                <div class="font-bold text-xs text-slate-800">{{ auth()->user()->name ?? 'User' }}</div>
                                <div class="text-[11px] text-slate-500 truncate mt-0.5">{{ auth()->user()->email ?? '' }}</div>
                                <div class="mt-1.5"><span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-teal-50 text-teal-700 border border-teal-200 capitalize">{{ str_replace('_', ' ', auth()->user()->role_slug ?? 'Admin') }}</span></div>
                            </div>
                            <div class="py-1">
                                <a href="{{ route('profile') }}" class="flex items-center px-4 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-teal-700 transition gap-2">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    My Profile
                                </a>
                                <a href="{{ route('settings.index') }}" class="flex items-center px-4 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-teal-700 transition gap-2">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    System Settings
                                </a>
                            </div>
                            <div class="py-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center px-4 py-2 text-xs font-medium text-rose-600 hover:bg-rose-50 transition gap-2">
                                        <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                        Sign out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Notification Messages -->
            @if(session('success'))
                <div class="mx-4 sm:mx-6 lg:mx-8 mt-4 auto-dismiss bg-emerald-50/90 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl flex items-center justify-between shadow-sm backdrop-blur-sm">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-600 flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span class="text-sm font-semibold">{{ session('success') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800 p-1 rounded-lg hover:bg-emerald-100 transition">&times;</button>
                </div>
            @endif

            @if(session('error') || $errors->any())
                <div class="mx-4 sm:mx-6 lg:mx-8 mt-4 bg-rose-50/90 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl flex items-center justify-between shadow-sm backdrop-blur-sm">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg bg-rose-100 flex items-center justify-center text-rose-600 flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <span class="text-sm font-semibold">{{ session('error') ?? $errors->first() }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-800 p-1 rounded-lg hover:bg-rose-100 transition">&times;</button>
                </div>
            @endif

            <!-- Main Dynamic Page Content -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 custom-scrollbar">
                @yield('content')
            </main>
        </div>
    </div>

    @include('partials.dialer')

    @yield('scripts')
    @stack('scripts')
</body>
</html>
