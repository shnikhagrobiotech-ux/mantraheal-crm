<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'product_variant_id',
        'quantity',
        'rate',
        'tax_percent',
        'tax_amount',
        'total_amount',
        'received_quantity',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
