<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    protected $fillable = [
        'refund_number',
        'order_id',
        'order_return_id',
        'customer_id',
        'amount',
        'refund_method',
        'transaction_reference',
        'status',
        'notes',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function orderReturn()
    {
        return $this->belongsTo(OrderReturn::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
