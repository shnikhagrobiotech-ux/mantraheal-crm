@extends('layouts.app')

@section('title', 'System Audit Log & Compliance Trail — MantraHeal CRM')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xs text-slate-500">Security & Governance</span>
                <span class="text-slate-300">/</span>
                <span class="text-xs text-slate-500">Audit Trail</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">System Audit & Compliance Log</h1>
            <p class="text-sm text-slate-500">Immutable forensic record of who changed what, previous vs new values, and client IP addresses</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="card p-4">
        <form method="GET" action="{{ route('audit.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search description, user or IP..." class="form-control text-sm w-full">
            </div>
            <div>
                <select name="user_id" class="form-control text-sm w-full">
                    <option value="">All Performing Users</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="action" class="form-control text-sm w-full">
                    <option value="">All Action Types</option>
                    @foreach($actions as $act)
                        <option value="{{ $act }}" {{ request('action') == $act ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $act)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="btn btn-primary text-sm py-2 px-4 flex-1">Filter</button>
                <a href="{{ route('audit.index') }}" class="btn btn-secondary text-sm py-2 px-3">Reset</a>
            </div>
        </form>
    </div>

    <!-- Audit Log Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table w-full text-xs">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>User (Who)</th>
                        <th>Action</th>
                        <th>Entity (What)</th>
                        <th>Description / Activity Note</th>
                        <th>Changes (Old → New)</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-sans">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/75 transition-colors">
                            <td class="font-mono text-slate-500 whitespace-nowrap">
                                {{ $log->created_at->format('d M Y, h:i:s A') }}
                            </td>
                            <td class="whitespace-nowrap">
                                <div class="font-bold text-slate-800">{{ $log->user_name ?? $log->user?->name ?? 'System Process' }}</div>
                                @if($log->user)
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $log->user->role_slug }}</div>
                                @endif
                            </td>
                            <td class="whitespace-nowrap">
                                @php
                                    $actBadge = match($log->action) {
                                        'created', 'batch_created' => 'badge-success',
                                        'updated', 'ticket_updated', 'settings_updated' => 'badge-info',
                                        'deleted' => 'badge-danger',
                                        'stock_adjusted' => 'badge-warning',
                                        default => 'badge-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $actBadge }} text-[10px] uppercase font-mono">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap font-mono text-slate-700">
                                {{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}
                            </td>
                            <td class="text-slate-800 font-medium max-w-sm">
                                {{ $log->description }}
                            </td>
                            <td class="max-w-xs truncate text-[11px] text-slate-500">
                                @if($log->old_values && $log->new_values)
                                    <details class="cursor-pointer">
                                        <summary class="text-emerald-700 font-medium hover:underline">View Field Diffs</summary>
                                        <div class="mt-1 p-2 bg-slate-50 rounded border border-slate-200 text-[10px] font-mono space-y-1">
                                            <div><strong class="text-rose-600">Old:</strong> {{ json_encode($log->old_values) }}</div>
                                            <div><strong class="text-emerald-600">New:</strong> {{ json_encode($log->new_values) }}</div>
                                        </div>
                                    </details>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="font-mono text-slate-500 whitespace-nowrap">
                                {{ $log->ip_address ?? '127.0.0.1' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-12 text-slate-400 text-sm">
                                No audit events matching your criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
