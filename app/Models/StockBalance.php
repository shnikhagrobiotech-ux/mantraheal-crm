<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockBalance extends Model
{
    protected $fillable = [
        'product_id',
        'product_variant_id',
        'warehouse_id',
        'quantity',
        'reserved_quantity',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function getAvailableQuantityAttribute(): int
    {
        return max(0, $this->quantity - $this->reserved_quantity);
    }
}
