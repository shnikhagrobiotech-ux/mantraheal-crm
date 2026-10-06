<?php

namespace App\Services;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CustomerService
{
    /**
     * Builds a comprehensive Customer 360 chronological activity timeline.
     */
    public function getCustomerTimeline(Customer $customer): Collection
    {
        $events = collect();

        // 1. Orders Placed & Delivered
        foreach ($customer->orders as $order) {
            $events->push([
                'type' => 'order_placed',
                'title' => 'Order Placed #' . $order->order_number,
                'datetime' => $order->order_date,
                'badge' => 'badge-new',
                'description' => 'Channel: ' . $order->channel . ' | Amount: ₹' . number_format($order->grand_total, 2) . ' (' . $order->payment_method . ')',
                'actor' => $order->assignedUser?->name ?? 'Online',
                'icon' => 'shopping-bag',
                'link' => route('orders.show', $order->id),
            ]);

            if ($order->delivered_at) {
                $events->push([
                    'type' => 'order_delivered',
                    'title' => 'Order Delivered #' . $order->order_number,
                    'datetime' => $order->delivered_at,
                    'badge' => 'badge-delivered',
                    'description' => 'Delivered via ' . ($order->courier_name ?? 'Courier') . ' Tracking: ' . ($order->tracking_number ?? 'N/A'),
                    'actor' => 'Logistics',
                    'icon' => 'check-circle',
                    'link' => route('orders.show', $order->id),
                ]);
            }
        }

        // 2. Sales Calls
        foreach ($customer->salesCalls as $call) {
            $events->push([
                'type' => 'call',
                'title' => ucfirst($call->direction) . ' Call - ' . $call->outcome,
                'datetime' => $call->call_datetime,
                'badge' => in_array($call->outcome, ['Connected', 'Order Taken', 'Interested']) ? 'badge-confirmed' : 'badge-processing',
                'description' => 'Duration: ' . $call->duration_formatted . ($call->notes ? ' | Notes: ' . $call->notes : ''),
                'actor' => $call->user?->name ?? 'Sales Exec',
                'icon' => 'phone',
                'has_audio' => (bool) $call->recording,
                'audio_url' => $call->recording ? route('call-recordings.play', $call->recording->id) : null,
            ]);
        }

        // 3. Follow-ups
        foreach ($customer->followups as $followup) {
            $events->push([
                'type' => 'followup',
                'title' => 'Follow-up ' . ($followup->status === 'Completed' ? 'Completed' : 'Scheduled'),
                'datetime' => $followup->status === 'Completed' ? ($followup->completed_at ?? Carbon::parse($followup->due_date)) : Carbon::parse($followup->due_date),
                'badge' => $followup->status === 'Completed' ? 'badge-delivered' : ($followup->priority === 'High' ? 'badge-cancelled' : 'badge-processing'),
                'description' => 'Reason: ' . $followup->reason . ($followup->notes ? ' | ' . $followup->notes : ''),
                'actor' => $followup->user?->name ?? 'Staff',
                'icon' => 'calendar',
            ]);
        }

        // 4. Returns & RTO
        foreach ($customer->returns as $return) {
            $events->push([
                'type' => 'return',
                'title' => 'Return ' . $return->status . ' (#' . $return->return_number . ')',
                'datetime' => Carbon::parse($return->return_date),
                'badge' => 'badge-returned',
                'description' => 'Reason: ' . $return->reason . ' | QC: ' . $return->qc_status . ' | Action: ' . $return->refund_action,
                'actor' => $return->processor?->name ?? 'Support Team',
                'icon' => 'corner-down-left',
                'link' => route('returns.show', $return->id),
            ]);
        }

        // 5. Support Tickets
        foreach ($customer->tickets as $ticket) {
            $events->push([
                'type' => 'ticket',
                'title' => 'Ticket #' . $ticket->ticket_number . ' - ' . $ticket->subject,
                'datetime' => $ticket->created_at,
                'badge' => $ticket->status === 'Closed' ? 'badge-delivered' : 'badge-new',
                'description' => 'Category: ' . $ticket->category . ' | Priority: ' . $ticket->priority . ' | Status: ' . $ticket->status,
                'actor' => $ticket->assignedUser?->name ?? 'Support Rep',
                'icon' => 'help-circle',
                'link' => route('support.show', $ticket->id),
            ]);
        }

        // 6. Communications (WhatsApp / SMS / Email)
        foreach ($customer->communicationLogs as $comm) {
            $events->push([
                'type' => 'communication',
                'title' => $comm->channel . ' Message Sent',
                'datetime' => $comm->created_at,
                'badge' => 'badge-shipped',
                'description' => Str::limit($comm->message_body, 120),
                'actor' => $comm->user?->name ?? 'System',
                'icon' => 'message-square',
            ]);
        }

        // Sort descending by datetime
        return $events->sortByDesc(fn ($item) => Carbon::parse($item['datetime'])->timestamp)->values();
    }
}
