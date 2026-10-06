<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;

class CustomerPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Customer $customer): bool
    {
        if ($user->isAdmin() || $user->isSalesManager() || $user->isCustomerSupport() || $user->isAccounts()) {
            return true;
        }

        // Sales executive can view assigned customer or unassigned
        return $customer->assigned_user_id === $user->id || is_null($customer->assigned_user_id);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Customer $customer): bool
    {
        if ($user->isAdmin() || $user->isSalesManager() || $user->isCustomerSupport()) {
            return true;
        }

        return $customer->assigned_user_id === $user->id;
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $user->isAdmin();
    }
}
