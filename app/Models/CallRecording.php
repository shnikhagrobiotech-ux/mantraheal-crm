<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CallRecording extends Model
{
    protected $fillable = [
        'sales_call_id',
        'customer_id',
        'user_id',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'duration_seconds',
        'is_protected',
    ];

    protected $casts = [
        'is_protected' => 'boolean',
    ];

    public function salesCall()
    {
        return $this->belongsTo(SalesCall::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function canBeAccessedBy(User $user): bool
    {
        if ($user->isAdmin() || $user->isSalesManager()) {
            return true;
        }
        return $this->user_id === $user->id;
    }

    public function getDurationFormattedAttribute(): string
    {
        $minutes = floor($this->duration_seconds / 60);
        $seconds = $this->duration_seconds % 60;
        return sprintf('%02d:%02d', $minutes, $seconds);
    }
}
