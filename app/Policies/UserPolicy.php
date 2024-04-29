<?php

namespace App\Policies;

use App\Models\User;
use App\Services\JWTAuthService;
use App\Services\PermissionService;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    public function __construct(private JWTAuthService $jwt)
    {
    }

    public function show(null $user, User $model, ?string $token): bool
    {
        /**
         * Не использую before, т.к. order-manager
         * не должен иметь право менять пароль юзера
         */
        $hasPermission = PermissionService::checkEditOrdersPermission($token);
        if ($hasPermission) return true;


        try {
            $checked = $this->jwt->checkAccess($token);
            return $model->id == $checked->sub;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function changePassword(null $user, User $model, ?string $token, int $userId): bool
    {
        try {
            $checked = $this->jwt->checkAccess($token);
            return $userId == $checked->sub;
        } catch (\Exception $e) {
            return false;
        }
    }
}
