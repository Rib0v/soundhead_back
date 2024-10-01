<?php

if (!function_exists('getAppUrl')) {

    /**
     * Получение url приложения без http
     * 
     * @return string
     */
    function getAppUrl(): string
    {
        $parsedUrl = parse_url(config('app.url'));
        return $parsedUrl['host'];
    }
}

if (!function_exists('strParamsToArr')) {

    /**
     * Обрезка лишних запятых по краям
     * и разбивка строки параметров на массив
     * 
     * @param array $queryParams
     * @return array
     */
    function strParamsToArr(array $queryParams): array
    {
        return array_map(fn($value) => explode(',', trim($value, ',')), $queryParams);
    }
}
