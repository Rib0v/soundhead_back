<?php

return [
    /**
     * Относительный адрес, по которому 
     * доступна спецификация
     */
    'uri' => '/api/docs',

    /**
     * Путь до папки со стилями и скриптами 
     */
    'path' => '/storage/api-docs/assets',

    /**
     * Путь до yaml-файла
     */
    'yaml' => '/storage/api-docs/openapi.yaml',

    /**
     * Директории, которые нужно сканировать
     */
    'dirs4scan' => [
        '/app/Http/Controllers/Docs',
    ],
];
