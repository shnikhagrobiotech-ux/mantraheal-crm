<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Customer extends Model
{
    protected $fillable = [
        'customer_code',
        'name',
        'mobile',
        'whatsapp',
        'email',
        'customer_source',
        'customer_type',
        'assigned_user_id',
        'status',
        'address_line1',
        'city',
        'state',
        'pincode',
        'tags',
        'notes',
        'total_orders',
        'total_spend',
        'average_order_value',
        'outstanding_amount',
        'first_order_at',
        'last_order_at',
    ];

    protected static function booted()
    {
        static::creating(function ($customer) {
            if (empty($customer->customer_code)) {
                $customer->customer_code = 'MH-CUST-' . strtoupper(Str::random(6));
            }
        });
    }

    public function getPhoneAttribute()
    {
        return $this->mobile;
    }

    public function setPhoneAttribute($value)
    {
        $this->attributes['mobile'] = $value;
    }

    protected $casts = [
        'first_order_at' => 'datetime',
        'last_order_at' => 'datetime',
        'total_spend' => 'decimal:2',
        'average_order_value' => 'decimal:2',
        'outstanding_amount' => 'decimal:2',
    ];

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function addresses()
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function defaultAddress()
    {
        return $this->hasOne(CustomerAddress::class)->where('is_default', true);
    }

    public function orders()
    {
        return $this->hasMany(Order::class)->orderByDesc('order_date');
    }

    public function salesCalls()
    {
        return $this->hasMany(SalesCall::class)->orderByDesc('call_datetime');
    }

    public function callRecordings()
    {
        return $this->hasMany(CallRecording::class)->orderByDesc('created_at');
    }

    public function followups()
    {
        return $this->hasMany(FollowUp::class)->orderByDesc('due_date');
    }

    public function tickets()
    {
        return $this->hasMany(SupportTicket::class)->orderByDesc('created_at');
    }

    public function communicationLogs()
    {
        return $this->hasMany(CommunicationLog::class)->orderByDesc('created_at');
    }

    public function returns()
    {
        return $this->hasMany(OrderReturn::class)->orderByDesc('return_date');
    }

    public function recalculateMetrics(): void
    {
        $deliveredOrders = $this->orders()->whereIn('order_status', ['Delivered', 'Shipped', 'Confirmed', 'Processing'])->get();
        $this->total_orders = $deliveredOrders->count();
        $this->total_spend = $deliveredOrders->sum('grand_total');
        $this->average_order_value = $this->total_orders > 0 ? round($this->total_spend / $this->total_orders, 2) : 0;
        
        $first = $this->orders()->oldest('order_date')->first();
        $last = $this->orders()->latest('order_date')->first();
        
        $this->first_order_at = $first?->order_date;
        $this->last_order_at = $last?->order_date;
        $this->save();
    }
}
