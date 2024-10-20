<?php

namespace App\Helpers;

class AttributeHelper
{
    public const BRAND = 1;
    public const FORM = 2;
    public const CONNECT = 3;
    public const AC_TYPE = 4;
    public const DENOISE = 5;
    public const WIRE_LEN = 6;
    public const BLUETOOTH = 7;
    public const CONNECTOR = 8;
    public const F_CHARGE = 9;

    public static function getAllValues(): array
    {
        $reflection = new \ReflectionClass(static::class);
        return array_values($reflection->getConstants());
    }
}
