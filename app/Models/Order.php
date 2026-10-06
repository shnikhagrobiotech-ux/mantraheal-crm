<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'customer_id',
        'order_date',
        'channel',
        'shopify_order_id',
        'subtotal',
        'discount_amount',
        'coupon_code',
        'cgst_amount',
        'sgst_amount',
        'igst_amount',
        'total_tax',
        'shipping_charge',
        'grand_total',
        'order_status',
        'payment_status',
        'payment_method',
        'fulfillment_status',
        'shipping_name',
        'shipping_phone',
        'shipping_address_line1',
        'shipping_address_line2',
        'shipping_city',
        'shipping_state',
        'shipping_pincode',
        'courier_name',
        'tracking_number',
        'shiprocket_shipment_id',
        'delivered_at',
        'assigned_user_id',
        'notes',
    ];

    protected $casts = [
        'order_date' => 'datetime',
        'delivered_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'cgst_amount' => 'decimal:2',
        'sgst_amount' => 'decimal:2',
        'igst_amount' => 'decimal:2',
        'total_tax' => 'decimal:2',
        'shipping_charge' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class)->orderByDesc('payment_date');
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class)->orderByDesc('created_at');
    }

    public function returns()
    {
        return $this->hasMany(OrderReturn::class)->orderByDesc('return_date');
    }

    public function rtoRecord()
    {
        return $this->hasOne(RtoRecord::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'Paid';
    }

    public function isDelivered(): bool
    {
        return $this->order_status === 'Delivered';
    }

    public function isRto(): bool
    {
        return $this->order_status === 'RTO';
    }

    public function isCancelled(): bool
    {
        return $this->order_status === 'Cancelled';
    }

    public function getStatusAttribute(): string
    {
        return $this->order_status ?? 'New';
    }

    public function getTotalAmountAttribute(): float
    {
        return (float) ($this->grand_total ?? 0);
    }

    public function getTaxAmountAttribute(): float
    {
        return (float) ($this->total_tax ?? 0);
    }

    public function getSalesChannelAttribute(): string
    {
        return $this->channel ?? 'direct';
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->order_status) {
            'New' => 'badge-new',
            'Confirmed' => 'badge-confirmed',
            'Processing' => 'badge-processing',
            'Packed' => 'badge-processing',
            'Shipped' => 'badge-shipped',
            'Out for Delivery' => 'badge-shipped',
            'Delivered' => 'badge-delivered',
            'Cancelled' => 'badge-cancelled',
            'Returned' => 'badge-returned',
            'Refunded' => 'badge-returned',
            'RTO' => 'badge-rto',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
