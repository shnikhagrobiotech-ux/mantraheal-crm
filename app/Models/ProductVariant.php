<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'variant_name',
        'sku',
        'barcode',
        'mrp',
        'selling_price',
        'purchase_price',
        'is_active',
    ];

    protected $casts = [
        'mrp' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function stockBalances()
    {
        return $this->hasMany(StockBalance::class);
    }
}
