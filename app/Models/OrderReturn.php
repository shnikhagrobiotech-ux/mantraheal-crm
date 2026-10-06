<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderReturn extends Model
{
    protected $fillable = [
        'return_number',
        'order_id',
        'customer_id',
        'return_date',
        'reason',
        'status',
        'qc_status',
        'restocking_warehouse_id',
        'refund_action',
        'notes',
        'processed_by',
    ];

    protected $casts = [
        'return_date' => 'date',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(ReturnItem::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'restocking_warehouse_id');
    }

    public function refund()
    {
        return $this->hasOne(Refund::class);
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
