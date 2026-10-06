<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerAddress extends Model
{
    protected $fillable = [
        'customer_id',
        'type',
        'address_line1',
        'address_line2',
        'landmark',
        'city',
        'state',
        'pincode',
        'country',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function getFullAddressAttribute(): string
    {
        return collect([$this->address_line1, $this->address_line2, $this->landmark, $this->city, $this->state . ' - ' . $this->pincode])
            ->filter()
            ->implode(', ');
    }
}
