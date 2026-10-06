<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditService
{
    public function log(string $action, Model $model, ?string $description = null, ?array $oldValues = null, ?array $newValues = null): AuditLog
    {
        return AuditLog::log($action, $model, $description, $oldValues, $newValues);
    }
}
