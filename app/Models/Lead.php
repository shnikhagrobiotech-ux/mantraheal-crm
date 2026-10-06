<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Lead extends Model
{
    protected $fillable = [
        'lead_code',
        'name',
        'mobile',
        'whatsapp',
        'email',
        'city',
        'state',
        'pincode',
        'source',
        'stage',
        'lost_reason',
        'estimated_value',
        'assigned_user_id',
        'next_followup_at',
        'notes',
        'meta_lead_id',
        'meta_form_id',
        'meta_campaign_name',
        'meta_ad_name',
        'converted_to_customer_id',
        'converted_to_order_id',
        'converted_at',
    ];

    protected static function booted()
    {
        static::creating(function ($lead) {
            if (empty($lead->lead_code)) {
                $lead->lead_code = 'MH-LEAD-' . strtoupper(Str::random(6));
            }
        });
    }

    public function getPhoneAttribute()
    {
        return $this->mobile;
    }

    public function setPhoneAttribute($value)
    {
        $this->attributes['mobile'] = $value;
    }

    protected $casts = [
        'next_followup_at' => 'datetime',
        'converted_at' => 'datetime',
        'estimated_value' => 'decimal:2',
    ];

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function activities()
    {
        return $this->hasMany(LeadActivity::class)->orderByDesc('created_at');
    }

    public function convertedCustomer()
    {
        return $this->belongsTo(Customer::class, 'converted_to_customer_id');
    }

    public function salesCalls()
    {
        return $this->hasMany(SalesCall::class)->orderByDesc('call_datetime');
    }

    public function followups()
    {
        return $this->hasMany(FollowUp::class)->orderByDesc('due_date');
    }

    public function isWon(): bool
    {
        return $this->stage === 'Won';
    }

    public function isLost(): bool
    {
        return $this->stage === 'Lost';
    }
}
