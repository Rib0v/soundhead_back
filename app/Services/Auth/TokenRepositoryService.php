<?php

namespace App\Services\Auth;

use App\Contracts\TokenRepository;
use App\Models\Token;

class TokenRepositoryService implements TokenRepository
{
    public function saveRefreshToken(int $userId, string $token, int $expiredTimestamp): bool
    {
        $result = Token::updateOrCreate(
            ['user_id' => $userId],
            ['token' => $token, 'expired_at' => date('Y-m-d H:i:s', $expiredTimestamp)],
        );

        return (bool)$result;
    }

    public function isRefreshTokenExists(string $token): bool
    {
        return Token::where('token', $token)->exists();
    }

    public function removeRefreshToken(string $token): bool
    {
        return (bool)Token::where('token', $token)->delete();
    }
}
