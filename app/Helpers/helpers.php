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

if (!function_exists('getBaseUrl')) {

    /**
     * Получение базового url
     * с подстановкой порта, если требуется
     * 
     * @return string
     */
    function getBaseUrl(): string
    {
        return (config('app.port') === '80')
            ? config('app.url')
            : config('app.url') . ':' . config('app.port');
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

if (!function_exists('toSlug')) {

    /**
     * Конвертация строки в slug
     * 
     * @param string $string
     * @return string
     */
    function toSlug(string $string): string
    {
        return \Illuminate\Support\Str::slug($string);
    }
}
