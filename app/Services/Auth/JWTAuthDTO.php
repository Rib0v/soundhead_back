<?php

namespace App\Services\Auth;

class JWTAuthDTO
{
    public function __construct(
        public string $issuer,
        public string $key,
        public int $access_ttl = 30,
        public int $refresh_ttl = 43200,
        public int $leeway = 60,
    ) {}
}
