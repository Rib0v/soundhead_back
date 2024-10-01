<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use App\Services\Auth\JWTAuthService;

class OrderPolicy
{
    public function __construct(private JWTAuthService $jwt) {}

    public function show(User $user, Order $order)
    {
        if ($user->hasPermission('edit_orders')) return true;

        return $this->isOwnOrder($user, $order);
    }

    public function showByUserId(User $user): bool
    {
        if ($user->hasPermission('edit_orders')) return true;

        return $this->isSameUser($user);
    }

    protected function isOwnOrder(User $user, Order $order): bool
    {
        return $user->id === $order->user_id;
    }

    protected function isSameUser(User $user): bool
    {
        return $user->id === (int)request()->route('user');
    }
}
