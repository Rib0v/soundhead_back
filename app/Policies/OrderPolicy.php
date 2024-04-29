<?php

namespace App\Policies;

use App\Models\Order;
use App\Services\JWTAuthService;
use App\Services\PermissionService;

class OrderPolicy
{
    public function __construct(private JWTAuthService $jwt)
    {
    }

    public function before(null $user, string $ability, Order $order, ?string $token): bool|null
    {
        $hasPermission = PermissionService::checkEditOrdersPermission($token);

        if ($hasPermission) return true;

        return null;
    }

    public function show(null $user, Order $order, ?string $token): bool
    {
        try {
            $checked = $this->jwt->checkAccess($token);
            return $order->user_id == $checked->sub;
        } catch (\Exception $e) {
            return false;
        }
    }


    public function showByUserId(null $user, Order $order, ?string $token, int $userId): bool
    {
        try {
            $checked = $this->jwt->checkAccess($token);
            return $userId == $checked->sub;
        } catch (\Exception $e) {
            return false;
        }
    }
}
