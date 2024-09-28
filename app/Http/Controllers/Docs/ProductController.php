<?php

namespace App\Http\Controllers\Docs;

use OpenApi\Attributes as OA;

class ProductController extends Controller
{
    #[OA\Get(
        tags: ['Product'],
        path: '/api/products',
        summary: 'INDEX - Массив товаров',
        description: 'Список фильтров динамический, берётся из БД. Приведены только некоторые параметры.',
    )]
    #[OA\Parameter(
        name: 'brand',
        in: 'query',
        description: "Пример: '1,2,3'",
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Parameter(
        name: 'minprice',
        in: 'query',
        description: 'Пример: 1000',
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Parameter(
        name: 'maxprice',
        in: 'query',
        description: 'Пример: 10000',
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Parameter(
        name: 'sort',
        in: 'query',
        description: 'Значения: lowprice/hiprice/older/newer',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Response(
        response: 200,
        description: 'OK',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/ProductIndexItem')
                ),
                new OA\Property(property: 'meta', ref: '#/components/schemas/Meta')
            ]
        )
    )]
    public function index() {}

    #[OA\Schema(
        schema: 'ProductIndexItem',
        properties: [
            new OA\Property(property: 'id', type: 'integer', example: 1),
            new OA\Property(property: 'name', type: 'string', example: 'Focal molestiae'),
            new OA\Property(property: 'slug', type: 'string', example: 'focal-molestiae'),
            new OA\Property(property: 'price', type: 'integer', example: 22900),
            new OA\Property(property: 'image', type: 'string', example: '/overhead/wired/0.jpg'),
            new OA\Property(property: 'description', type: 'string', example: 'Expedita eos earum eaque culpa iure quae.')
        ]
    )]
    public function productIndexItemSchema() {}



    #[OA\Get(
        tags: ['Product'],
        path: '/api/products/{identifier}',
        summary: 'SHOW - Информация о товаре',
    )]
    #[OA\Parameter(
        name: 'identifier',
        in: 'path',
        required: true,
        description: 'Можно искать как по id, так и по slug',
        schema: new OA\Schema(
            anyOf: [
                new OA\Schema(type: 'integer'),
                new OA\Schema(type: 'string'),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'OK',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'data',
                    ref: '#/components/schemas/ProductDetails'
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Not Found',
    )]
    public function show() {}

    #[OA\Schema(
        schema: 'ProductDetails',
        properties: [
            new OA\Property(property: 'id', type: 'integer', example: 1),
            new OA\Property(property: 'name', type: 'string', example: 'Focal molestiae'),
            new OA\Property(property: 'slug', type: 'string', example: 'focal-molestiae'),
            new OA\Property(property: 'price', type: 'integer', example: 22900),
            new OA\Property(property: 'image', type: 'string', example: '/overhead/wired/0.jpg'),
            new OA\Property(property: 'description', type: 'string', example: 'Expedita eos earum eaque culpa iure quae.'),
            new OA\Property(property: 'attributes', type: 'string', example: '[{...}, {...}, ...]'),
            new OA\Property(property: 'photos', type: 'string', example: "['url', 'url', ...]"),
        ]
    )]
    public function productDetailsSchema() {}



    #[OA\Get(
        tags: ['Product'],
        path: '/api/products/compare',
        summary: 'compare - Сравнение товаров',
    )]
    #[OA\Parameter(
        name: 'product',
        in: 'query',
        required: true,
        schema: new OA\Schema(
            type: 'string',
            example: '1,2,3'
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'OK',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/ProductDetails')
                ),
                new OA\Property(property: 'attributes', type: 'string', example: '{...}'),
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Not Found',
    )]
    public function compare() {}



    #[OA\Get(
        tags: ['Product'],
        path: '/api/products/cart',
        summary: 'cart - Актуальные цены и ссылки на фото для списка товаров из корзины пользователя',
    )]
    #[OA\Parameter(
        name: 'product',
        in: 'query',
        required: true,
        schema: new OA\Schema(
            type: 'string',
            example: '1,2,3'
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'OK',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/ProductDetails')
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Not Found',
    )]
    public function cart() {}

    #[OA\Schema(
        schema: 'ProductCartDetails',
        properties: [
            new OA\Property(property: 'id', type: 'integer', example: 1),
            new OA\Property(property: 'name', type: 'string', example: 'Focal molestiae'),
            new OA\Property(property: 'slug', type: 'string', example: 'focal-molestiae'),
            new OA\Property(property: 'price', type: 'integer', example: 22900),
            new OA\Property(property: 'image', type: 'string', example: '/overhead/wired/0.jpg'),
        ]
    )]
    public function productCartDetailsSchema() {}



    #[OA\Post(
        tags: ['Product'],
        path: '/api/products',
        summary: 'STORE - Создание товара',
        security: [['jwt' => []]],
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            ref: '#/components/schemas/ProductRequestBody'
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'OK',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Товар создан.'),
                new OA\Property(property: 'product', ref: '#/components/schemas/ProductCreatedWithId'),
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Нет прав = не знаешь о существовании страницы',
    )]
    #[OA\Response(
        response: 422,
        description: 'Валидация не пройдена',
    )]
    public function store() {}

    #[OA\Schema(
        schema: 'ProductRequestBody',
        properties: [
            new OA\Property(property: 'name', type: 'string', example: 'Тестовый товар'),
            new OA\Property(property: 'slug', type: 'string', example: 'testoviy-tovar'),
            new OA\Property(property: 'price', type: 'integer', example: 100500),
            new OA\Property(property: 'description', type: 'string', example: 'Тестовое описание'),
            new OA\Property(property: 'min_frequency', type: 'integer', example: 50),
            new OA\Property(property: 'max_frequency', type: 'integer', example: 19),
            new OA\Property(property: 'sensitivity', type: 'integer', example: 30),
            new OA\Property(property: 'image', type: 'string', example: 'test.jpg'),
        ]
    )]
    public function productRequestBodySchema() {}

    #[OA\Schema(
        schema: 'ProductCreatedWithId',
        properties: [
            new OA\Property(property: 'id', type: 'integer', example: 1),
            new OA\Property(property: 'name', type: 'string', example: 'Тестовый товар'),
            new OA\Property(property: 'slug', type: 'string', example: 'testoviy-tovar'),
            new OA\Property(property: 'price', type: 'integer', example: 100500),
            new OA\Property(property: 'description', type: 'string', example: 'Тестовое описание'),
            new OA\Property(property: 'min_frequency', type: 'integer', example: 50),
            new OA\Property(property: 'max_frequency', type: 'integer', example: 19),
            new OA\Property(property: 'sensitivity', type: 'integer', example: 30),
            new OA\Property(property: 'image', type: 'string', example: 'test.jpg'),
        ]
    )]
    public function productCreatedWithIdSchema() {}



    #[OA\Patch(
        tags: ['Product'],
        path: '/api/products/{id}',
        summary: 'UPDATE - Изменение товара',
        description: 'Это Patch, а не Put, так что можно отправлять на обновление отдельные свойства.',
        security: [['jwt' => []]],
    )]
    #[OA\Parameter(
        name: 'productId',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            ref: '#/components/schemas/ProductRequestBody'
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'OK',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Товар обновлён.'),
                new OA\Property(property: 'product', ref: '#/components/schemas/ProductCreatedWithId'),
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Отправлен запрос без параметров',
    )]
    #[OA\Response(
        response: 404,
        description: 'Нет прав = не знаешь о существовании страницы',
    )]
    #[OA\Response(
        response: 422,
        description: 'Валидация не пройдена',
    )]
    public function update() {}



    #[OA\Delete(
        tags: ['Product'],
        path: '/api/products/{product}',
        summary: 'DESTROY - Удаление товара',
        security: [['jwt' => []]],
    )]
    #[OA\Parameter(
        name: 'productId',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'OK',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Товар удалён.'),
                new OA\Property(property: 'product', ref: '#/components/schemas/ProductCreatedWithId'),
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Нет прав = не знаешь о существовании страницы',
    )]
    public function destroy() {}



    #[OA\Get(
        tags: ['Product'],
        path: '/api/products/search/{query}',
        summary: 'search - Поиск товаров по ключевому слову',
    )]
    #[OA\Parameter(
        name: 'query',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'string', example: 'sony')
    )]
    #[OA\Parameter(
        name: 'paginate',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', example: '5')
    )]
    #[OA\Response(
        response: 200,
        description: 'OK',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/ProductIndexItem')
                ),
                new OA\Property(property: 'links', ref: '#/components/schemas/Links'),
                new OA\Property(property: 'meta', ref: '#/components/schemas/Meta')
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Not Found',
    )]
    public function search() {}
}
