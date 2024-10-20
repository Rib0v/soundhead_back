<?php

namespace App\Services;

use App\Models\Permission;
use App\Services\Cache\CacheService;

class PermissionService
{
    public static function getPermissionId(string $permission): int
    {
        $permissions = app(CacheService::class)->cacheAndGet('user_permissions', fn() => self::getPermissions());

        if (!isset($permissions[$permission])) {
            throw new \Exception("Invalid permission name: '$permission'");
        }

        return $permissions[$permission];
    }

    public static function cachePermissionsIds(): void
    {
        app(CacheService::class)->cacheOnEveryCall('user_permissions', fn() => self::getPermissions());
    }

    protected static function getPermissions(): array
    {
        return Permission::select('id', 'name')->get()->pluck('id', 'name')->toArray() ?? [];
    }
}
