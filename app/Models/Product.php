<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'product_code',
        'sku',
        'name',
        'slug',
        'category_id',
        'brand',
        'description',
        'mrp',
        'selling_price',
        'purchase_price',
        'gst_percent',
        'hsn_code',
        'barcode',
        'minimum_stock',
        'reorder_level',
        'is_active',
        'image_path',
    ];

    protected $casts = [
        'mrp' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'gst_percent' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function batches()
    {
        return $this->hasMany(Batch::class);
    }

    public function stockBalances()
    {
        return $this->hasMany(StockBalance::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class)->orderByDesc('created_at');
    }

    public function getTotalStockAttribute(): int
    {
        return (int) $this->stockBalances()->sum('quantity');
    }

    public function getStockQuantityAttribute(): int
    {
        return $this->total_stock;
    }

    public function isLowStock(): bool
    {
        return $this->total_stock <= $this->reorder_level && $this->total_stock > 0;
    }

    public function isOutOfStock(): bool
    {
        return $this->total_stock <= 0;
    }
}
