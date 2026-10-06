<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class FollowUp extends Model
{
    protected $table = 'followups';

    protected $fillable = [
        'customer_id',
        'lead_id',
        'user_id',
        'due_date',
        'due_time',
        'reason',
        'priority',
        'status',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
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

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('due_date', Carbon::today())
            ->where('status', 'Pending');
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->whereDate('due_date', '<', Carbon::today())
            ->where('status', 'Pending');
    }

    public function scopeTomorrow(Builder $query): Builder
    {
        return $query->whereDate('due_date', Carbon::tomorrow())
            ->where('status', 'Pending');
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->whereDate('due_date', '>', Carbon::today())
            ->where('status', 'Pending');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'Completed');
    }
}
