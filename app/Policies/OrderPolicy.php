<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Order $order): bool
    {
        if ($user->isAdmin() || $user->isSalesManager() || $user->isAccounts() || $user->isCustomerSupport()) {
            return true;
        }

        return $order->assigned_user_id === $user->id || $order->customer?->assigned_user_id === $user->id;
    }

    public function update(User $user, Order $order): bool
    {
        if ($user->isAdmin() || $user->isSalesManager() || $user->isAccounts()) {
            return true;
        }

        return $order->assigned_user_id === $user->id;
    }

    public function delete(User $user, Order $order): bool
    {
        return $user->isAdmin();
    }
}
