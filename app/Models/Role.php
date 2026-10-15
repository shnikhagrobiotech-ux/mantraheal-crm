<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['name', 'slug', 'description'];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    public function hasPermission(string $permissionSlug): bool
    {
        return $this->permissions->contains('slug', $permissionSlug);
    }

    public function givePermissionTo(Permission|string $permission): void
    {
        if (is_string($permission)) {
            $permission = Permission::where('slug', $permission)->firstOrFail();
        }
        $this->permissions()->syncWithoutDetaching([$permission->id]);
    }

    public function syncPermissions(array $permissionIdsOrSlugs): void
    {
        $ids = [];
        foreach ($permissionIdsOrSlugs as $item) {
            if (is_numeric($item)) {
                $ids[] = (int) $item;
            } else {
                $p = Permission::where('slug', $item)->first();
                if ($p) {
                    $ids[] = $p->id;
                }
            }
        }
        $this->permissions()->sync($ids);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
