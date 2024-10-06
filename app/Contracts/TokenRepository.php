<?php

namespace App\Contracts;

interface TokenRepository
{
    public function saveRefreshToken(int $userId, string $token, int $expiredTimestamp): bool;
    public function isRefreshTokenExists(string $token): bool;
    public function removeRefreshToken(string $token): bool;
}
