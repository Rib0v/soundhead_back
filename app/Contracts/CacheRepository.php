<?php

namespace App\Contracts;

interface CacheRepository
{
    public function set(string $key, mixed $value, int $seconds = 0): bool;
    public function get(string $key): mixed;
    public function has(string $key): bool;
    public function del(string $key): bool;
    public function keys(string $searchQuery): array;
    public function getCollectionKeys(string $name): array;
}
