<?php

$attributes = [
    ['name' => 'Марка', 'slug' => 'brand'],
    ['name' => 'Форма', 'slug' => 'form'],
    ['name' => 'Подключение', 'slug' => 'connect'],
    ['name' => 'Аккустическое оформление', 'slug' => 'actype'],
    ['name' => 'Активное шумоподавление', 'slug' => 'denoise'],
    ['name' => 'Длина провода', 'slug' => 'wirelen'],
    ['name' => 'Версия bluetooth', 'slug' => 'bluetooth'],
    ['name' => 'Разъем для зарядки', 'slug' => 'connector'],
    ['name' => 'Быстрая зарядка', 'slug' => 'fcharge'],
];

//1-20
$brands = [
    'Marshall',
    'Beyerdynamic',
    'Sony',
    'Sennheiser',
    'Audio-Technica',
    'AKG',
    'Apple',
    'Beats',
    'Behringer',
    'Corsair',
    'Edifier',
    'Focal',
    'JBL',
    'Koss',
    'Philips',
    'Pioneer',
    'Ritmix',
    'Ultrasone',
    'Westone',
    'ZMW',
];

//21-23
$forms = [
    'Вставные',
    'Накладные',
    'Полноразмерные',
];

//24-25
$wireTypes = [
    'Проводные',
    'Бесроводные',
];

//26-28
$acousticTypes = [
    'Закрытые',
    'Открытые',
    'Полуоткрытые',
];

//29-30
$deNoise = ['Да', 'Нет'];

//31-34
// для проводных
$wireLength = [1.5, 2, 2.5, 3];

//35-40
// для беспроводных
$bluetooth = [4.2, 5.0, 5.1, 5.2, 5.3, 5.4];

//41-44
$connector = [
    'usb-c',
    'usb-b',
    'usb-micro',
    'lighting',
];

//45-46
$fastCharge = ['Да', 'Нет'];

$values = [$brands, $forms, $wireTypes, $acousticTypes, $deNoise, $wireLength, $bluetooth, $connector, $fastCharge];

// print_r($values);

$thumbnails = [
    'fullsize_wired' => '/fullsize/wired/0.webp',
    'fullsize_wireless' => '/fullsize/wireless/0.jpg',
    'overhead_wired' => '/overhead/wired/0.jpg',
    'overhead_wireless' => '/overhead/wireless/0.webp',
    'inear_wired' => '/inear/wired/0.webp',
    'inear_wireless' => '/inear/wireless/0.jpg',
];

$photos = [
    'fullsize_wired' => [
        '/fullsize/wired/1.webp',
        '/fullsize/wired/2.webp',
        '/fullsize/wired/3.webp',
        '/fullsize/wired/4.webp',
        '/fullsize/wired/5.webp',
    ],
    'fullsize_wireless' => [
        '/fullsize/wireless/1.webp',
        '/fullsize/wireless/2.webp',
        '/fullsize/wireless/3.webp',
        '/fullsize/wireless/4.webp',
        '/fullsize/wireless/5.webp',
        '/fullsize/wireless/6.webp',
        '/fullsize/wireless/7.webp',
        '/fullsize/wireless/8.webp',
    ],
    'overhead_wired' => [
        '/overhead/wired/1.webp',
        '/overhead/wired/2.webp',
        '/overhead/wired/3.webp',
        '/overhead/wired/4.webp',
        '/overhead/wired/5.webp',
        '/overhead/wired/6.webp',

    ],
    'overhead_wireless' => [
        '/overhead/wireless/1.webp',
        '/overhead/wireless/2.webp',
        '/overhead/wireless/3.webp',
        '/overhead/wireless/4.webp',
        '/overhead/wireless/5.webp',

    ],
    'inear_wired' => [
        '/inear/wired/1.jpg',
        '/inear/wired/2.jpg',
        '/inear/wired/3.jpg',
        '/inear/wired/4.jpg',
        '/inear/wired/5.jpg',
    ],
    'inear_wireless' => [
        '/inear/wireless/1.webp',
        '/inear/wireless/2.webp',
        '/inear/wireless/3.webp',
        '/inear/wireless/4.webp',
        '/inear/wireless/5.webp',
        '/inear/wireless/6.webp',
    ],
];
