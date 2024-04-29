<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function getUser(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function checkPassword(?User $user, string $password): bool
    {
        return $user && Hash::check($password, $user->password);
    }

    public function getPermissions(User $user): array
    {
        return array_column($user->permissionUsers->toArray(), 'permission_id');
    }
}
