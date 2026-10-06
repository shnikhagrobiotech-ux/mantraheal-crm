<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnItem extends Model
{
    protected $fillable = [
        'order_return_id',
        'order_item_id',
        'product_id',
        'quantity',
        'reason',
        'condition_notes',
    ];

    public function orderReturn()
    {
        return $this->belongsTo(OrderReturn::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
