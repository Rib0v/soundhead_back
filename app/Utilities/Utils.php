<?php

namespace App\Utilities;

class Utils
{
    /**
     * Обрезка лишних запятых по краям
     * и разбивка строки параметров на массив
     * 
     * @param array $queryParams
     * 
     * @return array
     */
    public static function strParamsToArr(array $queryParams): array
    {
        return array_map(fn ($value) => explode(',', trim($value, ',')), $queryParams);
    }
}
