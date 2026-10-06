<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_variant_id',
        'batch_id',
        'product_name',
        'sku',
        'hsn_code',
        'unit_price',
        'quantity',
        'tax_percent',
        'tax_rate',
        'tax_amount',
        'discount_amount',
        'total_price',
    ];

    public function getTaxRateAttribute()
    {
        return $this->tax_percent;
    }

    public function setTaxRateAttribute($value)
    {
        $this->attributes['tax_percent'] = $value;
    }

    protected $casts = [
        'unit_price' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }
}
