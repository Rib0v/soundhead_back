<?php

namespace App\Services;

use App\Models\Permission;
use Illuminate\Support\Facades\Redis;

class PermissionService
{
    private static function getPermissionId(string $permission): int
    {
        $permissions = Redis::get('user_permissions');

        if (!isset($permissions[$permission])) {
            throw new \Exception("Invalid permission name");
        }

        return $permissions[$permission];
    }

    /**
     * @param string|null $token
     * 
     * @return array list of user permissions
     */
    private static function getCurrentUserPermsId(?string $token): array
    {
        try {
            $checked = (new JWTAuthService)->checkAccess($token);
            return $checked->per;
        } catch (\Exception $e) {
            return [];
        }
    }

    public static function checkEditOrdersPermission(?string $token): bool
    {
        return in_array(
            self::getPermissionId('edit_orders'),
            self::getCurrentUserPermsId($token)
        );
    }

    public static function checkEditContentPermission(?string $token): bool
    {
        return in_array(
            self::getPermissionId('edit_content'),
            self::getCurrentUserPermsId($token)
        );
    }

    public static function checkEditUsersPermission(?string $token): bool
    {
        return in_array(
            self::getPermissionId('edit_users'),
            self::getCurrentUserPermsId($token)
        );
    }
}
