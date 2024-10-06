<?php

namespace App\Services;

use App\Models\Permission;
use Illuminate\Support\Facades\Redis;

class PermissionService
{
    public static function getPermissionId(string $permission): int
    {
        $permissions = self::getCachedPermissions();

        if (!isset($permissions[$permission])) {
            throw new \Exception("Invalid permission name: '$permission'");
        }

        return $permissions[$permission];
    }

    public static function getCachedPermissions(): array
    {
        if (!Redis::exists('user_permissions')) {
            self::cachePermissionsIds();
        }

        return Redis::get('user_permissions');
    }

    public static function cachePermissionsIds(): void
    {
        $permissionList = [];

        foreach (Permission::all() as $permission) {
            $permissionList[$permission['name']] = $permission['id'];
        }

        Redis::set('user_permissions', $permissionList);
    }
}
