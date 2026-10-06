<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'supplier_code',
        'name',
        'contact_person',
        'phone',
        'email',
        'gstin',
        'address',
        'city',
        'state',
        'pincode',
        'payment_terms',
        'status',
        'notes',
    ];

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class)->orderByDesc('po_date');
    }

    public function goodsReceivedNotes()
    {
        return $this->hasMany(GoodsReceivedNote::class)->orderByDesc('grn_date');
    }
}
