<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'movement_code',
        'product_id',
        'product_variant_id',
        'batch_id',
        'warehouse_id',
        'movement_type',
        'quantity',
        'balance_after',
        'reference_type',
        'reference_id',
        'user_id',
        'notes',
    ];

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

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFormattedTypeAttribute(): string
    {
        return match ($this->movement_type) {
            'opening' => 'Opening Stock',
            'purchase' => 'Purchase Receiving',
            'customer_return' => 'Customer Return',
            'transfer_in' => 'Transfer In',
            'sale' => 'Sales Dispatch',
            'damage' => 'Damaged / Expired',
            'transfer_out' => 'Transfer Out',
            'adjustment' => 'Manual Adjustment',
            default => ucfirst($this->movement_type),
        };
    }
}
