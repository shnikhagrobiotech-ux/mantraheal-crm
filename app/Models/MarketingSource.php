<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingSource extends Model
{
    protected $fillable = [
        'name',
        'code',
        'channel_type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function campaigns()
    {
        return $this->hasMany(MarketingCampaign::class);
    }
}
