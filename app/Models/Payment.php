<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'payment_number',
        'order_id',
        'customer_id',
        'payment_method',
        'transaction_reference',
        'amount',
        'payment_date',
        'status',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
