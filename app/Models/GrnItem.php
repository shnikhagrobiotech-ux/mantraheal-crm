<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrnItem extends Model
{
    protected $fillable = [
        'goods_received_note_id',
        'purchase_order_item_id',
        'product_id',
        'product_variant_id',
        'ordered_quantity',
        'received_quantity',
        'damaged_quantity',
        'accepted_quantity',
        'batch_number',
        'manufacturing_date',
        'expiry_date',
        'unit_cost',
    ];

    protected $casts = [
        'manufacturing_date' => 'date',
        'expiry_date' => 'date',
        'unit_cost' => 'decimal:2',
    ];

    public function goodsReceivedNote()
    {
        return $this->belongsTo(GoodsReceivedNote::class);
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
