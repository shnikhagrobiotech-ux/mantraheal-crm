<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Order;
use App\Models\SupportTicket;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SupportTicketController extends Controller
{
    public function index(Request $request)
    {
        $query = SupportTicket::with(['customer', 'order', 'assignedUser']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        if ($priority = $request->input('priority')) {
            $query->where('priority', $priority);
        }

        $tickets = $query->latest()->paginate(15)->withQueryString();
        $supportAgents = User::whereIn('role_slug', ['customer_support', 'admin', 'super_admin'])->get();

        return view('support.index', compact('tickets', 'supportAgents'));
    }

    public function create(Request $request)
    {
        $customerId = $request->input('customer_id');
        $customer = $customerId ? Customer::find($customerId) : null;
        $orderId = $request->input('order_id');
        $order = $orderId ? Order::find($orderId) : null;
        $customers = Customer::orderBy('name')->get();
        $supportAgents = User::whereIn('role_slug', ['customer_support', 'admin', 'super_admin'])->get();

        return view('support.create', compact('customer', 'order', 'customers', 'supportAgents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'order_id' => 'nullable|exists:orders,id',
            'subject' => 'required|string|max:255',
            'category' => 'required|string',
            'priority' => 'required|in:Low,Medium,High,Urgent',
            'assigned_user_id' => 'nullable|exists:users,id',
            'description' => 'required|string',
        ]);

        $ticketNumber = 'TICK-' . strtoupper(Str::random(6));

        $ticket = SupportTicket::create([
            'ticket_number' => $ticketNumber,
            'customer_id' => $validated['customer_id'],
            'order_id' => $validated['order_id'] ?? null,
            'subject' => $validated['subject'],
            'category' => $validated['category'],
            'priority' => $validated['priority'],
            'status' => 'Open',
            'assigned_user_id' => $validated['assigned_user_id'] ?? Auth::id(),
            'description' => $validated['description'],
        ]);

        TicketMessage::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => $ticket->description,
            'is_internal_note' => false,
        ]);

        AuditLog::log('ticket_created', $ticket, "Support Ticket #{$ticket->ticket_number} created for {$ticket->customer->name}");

        return redirect()->route('support.show', $ticket->id)->with('success', 'Ticket created successfully.');
    }

    public function show(SupportTicket $support)
    {
        $support->load(['customer.addresses', 'order', 'assignedUser', 'messages.user']);
        $supportAgents = User::whereIn('role_slug', ['customer_support', 'admin', 'super_admin'])->get();

        return view('support.show', compact('support', 'supportAgents'));
    }

    public function addMessage(Request $request, SupportTicket $support)
    {
        $validated = $request->validate([
            'message' => 'required|string',
            'is_internal_note' => 'boolean',
        ]);

        TicketMessage::create([
            'support_ticket_id' => $support->id,
            'user_id' => Auth::id(),
            'message' => $validated['message'],
            'is_internal_note' => $request->boolean('is_internal_note'),
        ]);

        if ($status = $request->input('status')) {
            $support->status = $status;
            if ($status === 'Resolved' || $status === 'Closed') {
                $support->resolved_at = now();
            }
            $support->save();
        }

        return back()->with('success', 'Message added.');
    }

    public function updateStatus(Request $request, SupportTicket $support)
    {
        $validated = $request->validate([
            'status' => 'required|in:Open,In Progress,Resolved,Closed',
            'assigned_user_id' => 'nullable|exists:users,id',
        ]);

        $support->update($validated);
        if (in_array($validated['status'], ['Resolved', 'Closed'])) {
            $support->resolved_at = now();
            $support->save();
        }

        AuditLog::log('ticket_updated', $support, "Ticket #{$support->ticket_number} status updated to {$support->status}");

        return back()->with('success', 'Ticket updated successfully.');
    }
}
