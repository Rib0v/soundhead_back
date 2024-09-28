<?php

namespace App\Http\Controllers\Docs;

use OpenApi\Attributes as OA;

#[OA\Info(title: "OpenApi спецификация", version: "1.0")]
#[OA\SecurityScheme(securityScheme: "jwt", type: "http", scheme: "bearer")]
abstract class Controller
{
    #[OA\Schema(
        schema: 'Links',
        type: 'string',
        example: '{...}'
    )]
    public function linksSchema() {}

    #[OA\Schema(
        schema: 'Meta',
        type: 'object',
        properties: [
            new OA\Property(property: 'current_page', type: 'integer', example: 1),
            new OA\Property(property: 'last_page', type: 'integer', example: 5),
            new OA\Property(property: 'total', type: 'integer', example: 100),
        ]
    )]
    public function metaSchema() {}
}
