@extends('layouts.app')

@section('title', 'Employee Directory & Roles — MantraHeal CRM')

@section('content')
<div class="space-y-6" x-data="{ showCreateModal: false }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xs text-slate-500">Administration</span>
                <span class="text-slate-300">/</span>
                <span class="text-xs text-slate-500">Employees</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Employees & Role-Based Access</h1>
            <p class="text-sm text-slate-500">Manage internal staff, sales executives, inventory managers, and customer support</p>
        </div>
        <button type="button" @click="showCreateModal = true" class="btn btn-primary text-sm inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            Add New Employee
        </button>
    </div>

    <!-- Employees Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table w-full text-sm">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Designation</th>
                        <th>Assigned Role</th>
                        <th>Contact Mobile</th>
                        <th>Email Address</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($employees as $emp)
                        <tr class="hover:bg-slate-50/75 transition-colors">
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-emerald-100 text-emerald-800 font-bold flex items-center justify-center text-xs">
                                        {{ strtoupper(substr($emp->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-800">
                                            <a href="{{ route('employees.show', $emp) }}" class="hover:text-emerald-700">
                                                {{ $emp->name }}
                                            </a>
                                        </div>
                                        <div class="text-[11px] text-slate-400">Joined {{ $emp->created_at->format('M Y') }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-slate-700 font-medium">
                                {{ $emp->designation ?? $emp->role?->name }}
                            </td>
                            <td>
                                @php
                                    $roleBadge = match($emp->role_slug) {
                                        'super_admin' => 'bg-purple-100 text-purple-800',
                                        'admin' => 'bg-indigo-100 text-indigo-800',
                                        'sales_manager', 'sales_executive' => 'bg-emerald-100 text-emerald-800',
                                        'inventory_manager' => 'bg-amber-100 text-amber-800',
                                        'customer_support' => 'bg-blue-100 text-blue-800',
                                        default => 'bg-slate-100 text-slate-700'
                                    };
                                @endphp
                                <span class="badge {{ $roleBadge }} text-xs capitalize font-semibold">
                                    {{ $emp->role?->name ?? str_replace('_', ' ', $emp->role_slug) }}
                                </span>
                            </td>
                            <td class="text-xs font-mono text-slate-600">
                                {{ $emp->phone ?? '—' }}
                            </td>
                            <td class="text-xs text-slate-600">
                                {{ $emp->email }}
                            </td>
                            <td>
                                <span class="badge badge-success text-[10px] uppercase">
                                    {{ $emp->status ?? 'active' }}
                                </span>
                            </td>
                            <td class="text-right whitespace-nowrap">
                                <a href="{{ route('employees.show', $emp) }}" class="btn btn-secondary text-xs py-1 px-2.5">
                                    Activity & KPIs
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-12 text-slate-400 text-sm">No employees registered.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($employees->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $employees->links() }}
            </div>
        @endif
    </div>

    <!-- Create Employee Modal -->
    <div x-show="showCreateModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 flex items-center justify-center p-4" style="display: none;">
        <div class="card max-w-lg w-full p-6 space-y-4 shadow-2xl bg-white" @click.away="showCreateModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-800">Add New Team Member</h3>
                <button type="button" @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <form action="{{ route('employees.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Full Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Rahul Sharma" class="form-control w-full text-sm">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Work Email *</label>
                        <input type="email" name="email" required placeholder="rahul@mantraheal.com" class="form-control w-full text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Phone Number</label>
                        <input type="text" name="phone" placeholder="+91 98765 43210" class="form-control w-full text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">System Role *</label>
                        <select name="role_id" required class="form-control w-full text-sm">
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Designation Title</label>
                        <input type="text" name="designation" placeholder="Senior Wellness Consultant" class="form-control w-full text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Initial Password *</label>
                    <input type="password" name="password" required placeholder="Minimum 8 characters" class="form-control w-full text-sm">
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showCreateModal = false" class="btn btn-secondary text-xs">Cancel</button>
                    <button type="submit" class="btn btn-primary text-xs">Create Employee Account</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
