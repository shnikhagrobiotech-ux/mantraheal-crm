<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsReceivedNote extends Model
{
    protected $fillable = [
        'grn_number',
        'purchase_order_id',
        'supplier_id',
        'warehouse_id',
        'grn_date',
        'invoice_number',
        'invoice_date',
        'remarks',
        'received_by',
        'status',
    ];

    protected $casts = [
        'grn_date' => 'date',
        'invoice_date' => 'date',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(GrnItem::class);
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
