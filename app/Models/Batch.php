<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    protected $fillable = [
        'batch_number',
        'product_id',
        'product_variant_id',
        'manufacturing_date',
        'expiry_date',
        'cost_price',
        'mrp',
        'selling_price',
        'warehouse_id',
        'initial_quantity',
        'current_quantity',
    ];

    protected $casts = [
        'manufacturing_date' => 'date',
        'expiry_date' => 'date',
        'cost_price' => 'decimal:2',
        'mrp' => 'decimal:2',
        'selling_price' => 'decimal:2',
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

    public function isExpired(): bool
    {
        return $this->expiry_date->isPast();
    }

    public function getDaysUntilExpiryAttribute(): int
    {
        return (int) Carbon::today()->diffInDays($this->expiry_date, false);
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->whereDate('expiry_date', '<', Carbon::today());
    }

    public function scopeExpiringIn30Days(Builder $query): Builder
    {
        return $query->whereBetween('expiry_date', [Carbon::today(), Carbon::today()->addDays(30)]);
    }

    public function scopeExpiringIn60Days(Builder $query): Builder
    {
        return $query->whereBetween('expiry_date', [Carbon::today()->addDays(31), Carbon::today()->addDays(60)]);
    }

    public function scopeExpiringIn90Days(Builder $query): Builder
    {
        return $query->whereBetween('expiry_date', [Carbon::today()->addDays(61), Carbon::today()->addDays(90)]);
    }

    // FEFO: First-Expired First-Out
    public function scopeFefo(Builder $query): Builder
    {
        return $query->where('current_quantity', '>', 0)
            ->whereDate('expiry_date', '>=', Carbon::today())
            ->orderBy('expiry_date', 'asc');
    }
}
