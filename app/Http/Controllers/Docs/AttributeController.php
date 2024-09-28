<?php

namespace App\Http\Controllers\Docs;

use OpenApi\Attributes as OA;

class AttributeController extends Controller
{
    #[OA\Get(
        path: '/api/attributes',
        tags: ['Attribute'],
        summary: 'INDEX - Список всех доступных характеристик товаров'
    )]
    #[OA\Response(
        response: 200,
        description: 'OK',
        content: new OA\JsonContent(ref: '#/components/schemas/AttributeIndex')
    )]
    public function index() {}

    #[OA\Schema(schema: 'AttributeIndex')]
    #[OA\Property(property: 'id', type: 'integer', example: 1)]
    #[OA\Property(property: 'name', type: 'string', example: 'Марка')]
    #[OA\Property(property: 'slug', type: 'string', example: 'brand')]
    #[OA\Property(
        property: 'vals',
        type: 'array',
        items: new OA\Items(
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'name', type: 'string', example: 'Marshall'),
            ]
        )
    )]
    public function indexSchema() {}
}
