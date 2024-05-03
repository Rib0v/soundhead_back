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

$photos = [
    'wired' => [
        'fullsize' => [5, 6, 8, 8, 4],
        'inear' => [5, 5, 7, 7, 5],
        'overhead' => [6, 5, 4, 4, 6],
    ],
    'wireless' => [
        'fullsize' => [8, 7, 6, 5, 4],
        'inear' => [6, 6, 3, 5, 4],
        'overhead' => [5, 6, 8, 5, 8],
    ]
];


$typesCounter = [
    'wired' => [
        'fullsize' => 1,
        'inear' => 1,
        'overhead' => 1,
    ],
    'wireless' => [
        'fullsize' => 1,
        'inear' => 1,
        'overhead' => 1,
    ]
];
