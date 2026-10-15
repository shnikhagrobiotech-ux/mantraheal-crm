@extends('layouts.app')

@section('title', 'Employees & Roles Management — MantraHeal CRM')

@section('content')
<div class="space-y-6" x-data="{
    activeTab: 'employees',
    showCreateModal: false,
    showCreateRoleModal: false,
    selectedRoleForEdit: null,
    editEmployeeModal: false,
    editingEmployee: { id: null, name: '', phone: '', role_id: '', designation: '', status: 'active' },
    openEditEmployee(emp) {
        this.editingEmployee = {
            id: emp.id,
            name: emp.name,
            phone: emp.phone || '',
            role_id: emp.role_id,
            designation: emp.designation || '',
            status: emp.status || 'active'
        };
        this.editEmployeeModal = true;
    },
    openRolePermissions(role) {
        this.selectedRoleForEdit = role;
    }
}">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xs text-slate-500">Administration</span>
                <span class="text-slate-300">/</span>
                <span class="text-xs text-slate-500">Access Control</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Employees & Role Permissions</h1>
            <p class="text-sm text-slate-500">Manage internal team members, system security roles, and granular module permissions</p>
        </div>
        <div class="flex items-center gap-2">
            <button x-show="activeTab === 'employees'" type="button" @click="showCreateModal = true" class="btn btn-primary text-sm inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                Add New Employee
            </button>
            <button x-show="activeTab === 'roles'" type="button" @click="showCreateRoleModal = true" class="btn btn-primary text-sm inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Create Custom Role
            </button>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="border-b border-slate-200">
        <nav class="flex space-x-6" aria-label="Tabs">
            <button type="button" @click="activeTab = 'employees'"
                    :class="activeTab === 'employees' ? 'border-teal-600 text-teal-700 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium'"
                    class="py-3 px-1 border-b-2 text-sm flex items-center gap-2 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>Staff Directory</span>
                <span class="px-2 py-0.5 rounded-full text-xs bg-slate-100 text-slate-600 font-semibold">{{ $employees->total() }}</span>
            </button>

            <button type="button" @click="activeTab = 'roles'"
                    :class="activeTab === 'roles' ? 'border-teal-600 text-teal-700 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium'"
                    class="py-3 px-1 border-b-2 text-sm flex items-center gap-2 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                <span>Roles & Permissions Matrix</span>
                <span class="px-2 py-0.5 rounded-full text-xs bg-teal-100 text-teal-800 font-semibold">{{ $roles->count() }} Roles</span>
            </button>
        </nav>
    </div>

    <!-- TAB 1: EMPLOYEES DIRECTORY -->
    <div x-show="activeTab === 'employees'" class="space-y-4">
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
                                            'super_admin' => 'bg-purple-100 text-purple-800 border-purple-200',
                                            'admin' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                                            'sales_manager', 'sales_executive' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                            'inventory_manager' => 'bg-amber-100 text-amber-800 border-amber-200',
                                            'customer_support' => 'bg-blue-100 text-blue-800 border-blue-200',
                                            'accounts' => 'bg-rose-100 text-rose-800 border-rose-200',
                                            default => 'bg-slate-100 text-slate-700 border-slate-200'
                                        };
                                    @endphp
                                    <span class="badge {{ $roleBadge }} text-xs capitalize font-semibold border px-2.5 py-0.5">
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
                                    <span class="badge {{ $emp->status === 'active' ? 'badge-success' : 'badge-danger' }} text-[10px] uppercase font-bold">
                                        {{ $emp->status ?? 'active' }}
                                    </span>
                                </td>
                                <td class="text-right whitespace-nowrap space-x-1">
                                    <button type="button" @click="openEditEmployee({{ $emp }})" class="btn btn-secondary text-xs py-1 px-2.5">
                                        Edit
                                    </button>
                                    <a href="{{ route('employees.show', $emp) }}" class="btn btn-secondary text-xs py-1 px-2.5">
                                        KPIs
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
    </div>

    <!-- TAB 2: ROLES & PERMISSIONS -->
    <div x-show="activeTab === 'roles'" class="space-y-6" style="display: none;">
        <!-- Roles Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($roles as $role)
                @php
                    $roleColor = match($role->slug) {
                        'super_admin' => 'from-purple-500/10 to-indigo-500/10 border-purple-300 text-purple-900',
                        'admin' => 'from-indigo-500/10 to-blue-500/10 border-indigo-300 text-indigo-900',
                        'sales_manager', 'sales_executive' => 'from-emerald-500/10 to-teal-500/10 border-emerald-300 text-emerald-900',
                        'inventory_manager' => 'from-amber-500/10 to-yellow-500/10 border-amber-300 text-amber-900',
                        'customer_support' => 'from-blue-500/10 to-cyan-500/10 border-blue-300 text-blue-900',
                        'accounts' => 'from-rose-500/10 to-pink-500/10 border-rose-300 text-rose-900',
                        default => 'from-slate-500/10 to-slate-500/10 border-slate-300 text-slate-900'
                    };
                    $badgeStyle = match($role->slug) {
                        'super_admin' => 'bg-purple-100 text-purple-800 border-purple-200',
                        'admin' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                        'sales_manager', 'sales_executive' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                        'inventory_manager' => 'bg-amber-100 text-amber-800 border-amber-200',
                        'customer_support' => 'bg-blue-100 text-blue-800 border-blue-200',
                        'accounts' => 'bg-rose-100 text-rose-800 border-rose-200',
                        default => 'bg-slate-100 text-slate-700 border-slate-200'
                    };
                    $totalPermsCount = \App\Models\Permission::count();
                    $grantedCount = $role->slug === 'super_admin' ? $totalPermsCount : $role->permissions->count();
                @endphp
                <div class="card p-5 border bg-gradient-to-br {{ $roleColor }} flex flex-col justify-between hover:shadow-md transition">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="badge {{ $badgeStyle }} border text-xs font-bold uppercase tracking-wider px-2 py-0.5">
                                {{ $role->name }}
                            </span>
                            <span class="text-xs font-semibold text-slate-500 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                {{ $role->users->count() }} staff
                            </span>
                        </div>
                        <p class="text-xs text-slate-600 line-clamp-2 mb-3">
                            {{ $role->description ?? 'Standard system operational security role.' }}
                        </p>
                    </div>

                    <div class="pt-3 border-t border-slate-200/60 flex items-center justify-between">
                        <div>
                            <span class="text-[11px] font-bold text-slate-700 block">
                                {{ $grantedCount }} / {{ $totalPermsCount }} Permissions
                            </span>
                            <div class="w-28 bg-slate-200 rounded-full h-1.5 mt-1 overflow-hidden">
                                <div class="bg-teal-600 h-1.5 rounded-full" style="width: {{ ($grantedCount / max(1, $totalPermsCount)) * 100 }}%"></div>
                            </div>
                        </div>

                        <button type="button" 
                                @click="openRolePermissions({
                                    id: {{ $role->id }},
                                    name: '{{ addslashes($role->name) }}',
                                    slug: '{{ $role->slug }}',
                                    description: '{{ addslashes($role->description ?? '') }}',
                                    permission_ids: {{ json_encode($role->permissions->pluck('id')->toArray()) }}
                                })"
                                class="btn btn-secondary text-xs py-1 px-3 inline-flex items-center gap-1.5 shadow-sm">
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Configure
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Full Interactive Permissions Matrix Table -->
        <div class="card overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">Complete Permission Matrix</h3>
                    <p class="text-xs text-slate-500">Cross-reference active permissions across all 7 operational roles</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 text-[11px] text-emerald-700 font-semibold bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        Granted
                    </span>
                    <span class="inline-flex items-center gap-1 text-[11px] text-slate-400 font-semibold bg-slate-100 px-2 py-0.5 rounded border border-slate-200">
                        — Restriced
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="table w-full text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-600">
                            <th class="w-1/3 py-2.5 px-4 font-bold text-left">Module & Permission</th>
                            @foreach($roles as $role)
                                <th class="text-center py-2.5 px-2 font-bold whitespace-nowrap">
                                    {{ $role->name }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($permissionsByModule as $module => $modulePermissions)
                            <tr class="bg-slate-100/70">
                                <td colspan="{{ $roles->count() + 1 }}" class="py-2 px-4 font-black uppercase text-[10px] tracking-widest text-slate-600">
                                    {{ ucfirst($module) }} Management
                                </td>
                            </tr>
                            @foreach($modulePermissions as $perm)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="py-2 px-4">
                                        <div class="font-bold text-slate-800">{{ $perm->name }}</div>
                                        <div class="text-[10px] text-slate-400 font-mono">{{ $perm->slug }}</div>
                                    </td>
                                    @foreach($roles as $role)
                                        @php
                                            $hasPerm = $role->slug === 'super_admin' || $role->permissions->contains('id', $perm->id);
                                        @endphp
                                        <td class="text-center py-2 px-2">
                                            @if($hasPerm)
                                                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-emerald-100 text-emerald-700">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                </span>
                                            @else
                                                <span class="text-slate-300 font-bold">—</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODAL: Role Permissions Configuration Modal -->
    <div x-show="selectedRoleForEdit !== null" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" 
         style="display: none;">
        <div class="card max-w-3xl w-full p-6 space-y-5 shadow-2xl bg-white max-h-[90vh] flex flex-col" @click.away="selectedRoleForEdit = null">
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="font-bold text-slate-800 text-lg flex items-center gap-2">
                        <span>Configure Role:</span>
                        <span class="text-teal-700" x-text="selectedRoleForEdit ? selectedRoleForEdit.name : ''"></span>
                    </h3>
                    <p class="text-xs text-slate-500">Toggle permissions granted to staff accounts assigned to this role</p>
                </div>
                <button type="button" @click="selectedRoleForEdit = null" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <!-- Super Admin Notice -->
            <template x-if="selectedRoleForEdit && selectedRoleForEdit.slug === 'super_admin'">
                <div class="p-3 bg-purple-50 border border-purple-200 rounded-lg text-purple-800 text-xs flex items-center gap-2">
                    <svg class="w-4 h-4 text-purple-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Super Admin has permanent root bypass. All permissions are automatically granted by system kernel.</span>
                </div>
            </template>

            <!-- Permissions Form -->
            <form x-show="selectedRoleForEdit" 
                  :action="'/roles/' + (selectedRoleForEdit ? selectedRoleForEdit.id : '') + '/permissions'" 
                  method="POST" 
                  class="space-y-4 overflow-y-auto flex-1 pr-1 custom-scrollbar">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Role Description</label>
                    <input type="text" name="description" :value="selectedRoleForEdit ? selectedRoleForEdit.description : ''" class="form-control w-full text-xs">
                </div>

                <div class="space-y-4 pt-2">
                    @foreach($permissionsByModule as $module => $modulePerms)
                        <div class="border border-slate-200 rounded-xl p-3.5 bg-slate-50/50">
                            <div class="flex items-center justify-between mb-2.5 pb-2 border-b border-slate-200/80">
                                <span class="font-bold text-xs uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-teal-500"></span>
                                    {{ ucfirst($module) }} Permissions
                                </span>
                                <span class="text-[11px] text-slate-400 font-medium">{{ count($modulePerms) }} actions</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @foreach($modulePerms as $perm)
                                    <label class="flex items-start gap-2.5 p-2 rounded-lg bg-white border border-slate-200/70 hover:border-teal-400 cursor-pointer transition text-xs">
                                        <input type="checkbox" 
                                               name="permissions[]" 
                                               value="{{ $perm->id }}"
                                               :checked="selectedRoleForEdit && (selectedRoleForEdit.slug === 'super_admin' || selectedRoleForEdit.permission_ids.includes({{ $perm->id }}))"
                                               :disabled="selectedRoleForEdit && selectedRoleForEdit.slug === 'super_admin'"
                                               class="rounded border-slate-300 text-teal-600 focus:ring-teal-500 mt-0.5 h-4 w-4">
                                        <div>
                                            <div class="font-semibold text-slate-800">{{ $perm->name }}</div>
                                            <div class="text-[10px] text-slate-400 font-mono">{{ $perm->slug }}</div>
                                            @if($perm->description)
                                                <div class="text-[10px] text-slate-500 mt-0.5">{{ $perm->description }}</div>
                                            @endif
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="selectedRoleForEdit = null" class="btn btn-secondary text-xs">Cancel</button>
                    <button type="submit" 
                            :disabled="selectedRoleForEdit && selectedRoleForEdit.slug === 'super_admin'"
                            class="btn btn-primary text-xs">
                        Save Role Permissions
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Add New Employee -->
    <div x-show="showCreateModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="card max-w-lg w-full p-6 space-y-4 shadow-2xl bg-white" @click.away="showCreateModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-800">Add New Team Member</h3>
                <button type="button" @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
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

    <!-- MODAL: Edit Existing Employee -->
    <div x-show="editEmployeeModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="card max-w-lg w-full p-6 space-y-4 shadow-2xl bg-white" @click.away="editEmployeeModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-800">Edit Team Member</h3>
                <button type="button" @click="editEmployeeModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>
            <form :action="'/employees/' + editingEmployee.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Full Name *</label>
                    <input type="text" name="name" x-model="editingEmployee.name" required class="form-control w-full text-sm">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Phone Number</label>
                        <input type="text" name="phone" x-model="editingEmployee.phone" class="form-control w-full text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Status *</label>
                        <select name="status" x-model="editingEmployee.status" required class="form-control w-full text-sm">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Assigned Role *</label>
                        <select name="role_id" x-model="editingEmployee.role_id" required class="form-control w-full text-sm">
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Designation Title</label>
                        <input type="text" name="designation" x-model="editingEmployee.designation" class="form-control w-full text-sm">
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="editEmployeeModal = false" class="btn btn-secondary text-xs">Cancel</button>
                    <button type="submit" class="btn btn-primary text-xs">Update Employee</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Create Custom Role -->
    <div x-show="showCreateRoleModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="card max-w-2xl w-full p-6 space-y-4 shadow-2xl bg-white max-h-[90vh] flex flex-col" @click.away="showCreateRoleModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-800">Create Custom Security Role</h3>
                <button type="button" @click="showCreateRoleModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>
            <form action="{{ route('roles.store') }}" method="POST" class="space-y-4 overflow-y-auto flex-1 pr-1 custom-scrollbar">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Role Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Quality Auditor" class="form-control w-full text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Role Slug (identifier) *</label>
                        <input type="text" name="slug" required placeholder="e.g. quality_auditor" class="form-control w-full text-sm font-mono">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Description</label>
                    <input type="text" name="description" placeholder="Brief explanation of responsibilities" class="form-control w-full text-sm">
                </div>

                <div class="space-y-3 pt-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Initial Permissions</label>
                    @foreach($permissionsByModule as $module => $modulePerms)
                        <div class="border border-slate-200 rounded-lg p-3 bg-slate-50/50">
                            <span class="font-bold text-xs uppercase text-slate-600 block mb-2">{{ ucfirst($module) }}</span>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach($modulePerms as $perm)
                                    <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer">
                                        <input type="checkbox" name="permissions[]" value="{{ $perm->id }}" class="rounded text-teal-600">
                                        <span>{{ $perm->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="showCreateRoleModal = false" class="btn btn-secondary text-xs">Cancel</button>
                    <button type="submit" class="btn btn-primary text-xs">Create Role</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
