<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('user_name', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        if ($action = $request->input('action')) {
            $query->where('action', $action);
        }

        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        $logs = $query->latest('created_at')->paginate(20)->withQueryString();
        $users = User::orderBy('name')->get();
        $actions = AuditLog::select('action')->distinct()->pluck('action');

        return view('audit.index', compact('logs', 'users', 'actions'));
    }
}
