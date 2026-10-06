<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingCampaign extends Model
{
    protected $fillable = [
        'name',
        'code',
        'marketing_source_id',
        'budget',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'budget' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function source()
    {
        return $this->belongsTo(MarketingSource::class, 'marketing_source_id');
    }
}
