<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesCall extends Model
{
    protected $fillable = [
        'customer_id',
        'lead_id',
        'user_id',
        'direction',
        'call_datetime',
        'duration_seconds',
        'status',
        'outcome',
        'notes',
        'follow_up_date',
        'recording_path',
    ];

    protected $casts = [
        'call_datetime' => 'datetime',
        'follow_up_date' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function recording()
    {
        return $this->hasOne(CallRecording::class);
    }

    public function getDurationFormattedAttribute(): string
    {
        $minutes = floor($this->duration_seconds / 60);
        $seconds = $this->duration_seconds % 60;
        return sprintf('%02d:%02d', $minutes, $seconds);
    }
}
