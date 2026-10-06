<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    protected $fillable = [
        'order_id',
        'courier_name',
        'tracking_number',
        'awb_code',
        'shipped_date',
        'expected_delivery_date',
        'actual_delivery_date',
        'status',
        'shipping_label_url',
        'notes',
    ];

    protected $casts = [
        'shipped_date' => 'datetime',
        'expected_delivery_date' => 'datetime',
        'actual_delivery_date' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
