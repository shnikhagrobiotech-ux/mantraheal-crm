<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'user_name',
        'action',
        'auditable_type',
        'auditable_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function log(string $action, ?Model $model = null, ?string $description = null, ?array $oldValues = null, ?array $newValues = null): self
    {
        $user = Auth::user();

        return self::create([
            'user_id' => $user?->id,
            'user_name' => $user ? $user->name : 'System',
            'action' => $action,
            'auditable_type' => $model ? get_class($model) : 'System',
            'auditable_id' => $model ? ($model->getKey() ?? 0) : 0,
            'description' => $description ?? ($model ? sprintf('%s on %s #%s', ucfirst($action), class_basename($model), $model->getKey()) : $action),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'created_at' => now(),
        ]);
    }
}
