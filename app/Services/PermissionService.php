<?php

namespace App\Services;

use App\Models\Permission;

class PermissionService
{
    /**
     * Можно либо permission_id вынести
     * в конфиг/enums и брать оттуда,
     * либо закешировать таблицу permissions
     * и брать из неё. Пока сделал 2й вариант.
     **/
    private static function getPermissionId(string $permission): int
    {
        return Permission::where('name', $permission)->firstOrFail()->id;
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
