<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role_id',
        'role_slug',
        'designation',
        'status',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function hasRole(string|array $roles): bool
    {
        $roleSlug = $this->role_slug ?? $this->role?->slug;
        if (is_array($roles)) {
            return in_array($roleSlug, $roles);
        }
        return $roleSlug === $roles;
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(['super_admin', 'admin']);
    }

    public function isSalesManager(): bool
    {
        return $this->hasRole(['super_admin', 'admin', 'sales_manager']);
    }

    public function isSalesExecutive(): bool
    {
        return $this->hasRole('sales_executive');
    }

    public function isInventoryManager(): bool
    {
        return $this->hasRole(['super_admin', 'admin', 'inventory_manager']);
    }

    public function isCustomerSupport(): bool
    {
        return $this->hasRole(['super_admin', 'admin', 'customer_support']);
    }

    public function isAccounts(): bool
    {
        return $this->hasRole(['super_admin', 'admin', 'accounts']);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class, 'assigned_user_id');
    }

    public function customers()
    {
        return $this->hasMany(Customer::class, 'assigned_user_id');
    }

    public function salesCalls()
    {
        return $this->hasMany(SalesCall::class);
    }

    public function followups()
    {
        return $this->hasMany(FollowUp::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'assigned_user_id');
    }
}
