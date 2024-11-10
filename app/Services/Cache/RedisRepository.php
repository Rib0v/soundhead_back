<?php

namespace App\Services\Cache;

use App\Contracts\CacheRepository;
use Illuminate\Support\Facades\Redis;

class RedisRepository implements CacheRepository
{
    public function __construct(protected string $databasePrefix) {}

    public function set(string $key, mixed $value, int $seconds = 0): bool
    {
        return ($seconds > 0)
            ? (bool)Redis::setex($key, $seconds, $value)
            : (bool)Redis::set($key, $value);
    }

    public function get(string $key): mixed
    {
        return Redis::get($key);
    }

    public function has(string $key): bool
    {
        return (bool)Redis::exists($key);
    }

    public function del(string $key): bool
    {
        return (bool)Redis::del($key);
    }

    public function keys(string $searchQuery): array
    {
        return Redis::keys($searchQuery);
    }

    public function getCollectionKeys(string $name): array
    {
        $keysWithoutPrefixes = [];

        foreach ($this->keys("$name:*") as $key) {
            $keysWithoutPrefixes[] = ltrim($key, $this->databasePrefix);
        }

        return $keysWithoutPrefixes;
    }
}
