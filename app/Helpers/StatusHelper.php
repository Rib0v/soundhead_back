<?php

namespace App\Helpers;

class StatusHelper
{
    public const CREATED = 1;
    public const CONFIRMED = 2;
    public const PAID = 3;
    public const SHIPPED = 4;
    public const COMPLETED = 5;

    public static function getAllValues(): array
    {
        $reflection = new \ReflectionClass(static::class);
        return array_values($reflection->getConstants());
    }
}
