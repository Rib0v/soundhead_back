<?php

namespace App\Policies;

use App\Models\User;
use App\Services\Auth\JWTAuthService;

class UserPolicy
{
    public function __construct(private JWTAuthService $jwt) {}

    public function show(User $user, User $model): bool
    {
        if ($user->hasPermission('edit_orders')) return true;

        return $this->isSameUser($user, $model);
    }

    public function changeProfile(User $user, User $model): bool
    {
        if ($user->hasPermission('edit_users')) return true;

        return $this->isSameUser($user, $model);
    }

    protected function isSameUser(User $user, User $model): bool
    {
        return $user->id === $model->id;
    }
}
