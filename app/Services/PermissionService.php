<?php

namespace App\Services;

use App\Models\Permission;
use Illuminate\Support\Facades\Redis;

class PermissionService
{
    public static function getPermissionId(string $permission): int
    {
        $permissions = Redis::get('user_permissions');

        if (!isset($permissions[$permission])) {
            throw new \Exception("Invalid permission name");
        }

        return $permissions[$permission];
    }
}
