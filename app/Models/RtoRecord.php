<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RtoRecord extends Model
{
    protected $table = 'rto_records';

    protected $fillable = [
        'rto_code',
        'order_id',
        'customer_id',
        'courier_name',
        'tracking_number',
        'rto_initiated_date',
        'rto_delivered_date',
        'reason',
        'state',
        'city',
        'sales_channel',
        'total_amount',
        'status',
        'received_warehouse_id',
        'notes',
    ];

    protected $casts = [
        'rto_initiated_date' => 'date',
        'rto_delivered_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'received_warehouse_id');
    }
}
