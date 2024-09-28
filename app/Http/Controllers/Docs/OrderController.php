<?php

namespace App\Http\Controllers\Docs;

use OpenApi\Attributes as OA;

class OrderController extends Controller
{
    #[OA\Get(
        tags: ['Order'],
        path: '/api/orders',
        summary: 'INDEX - Список всех заказов',
        security: [['jwt' => []]]
    )]
    #[OA\Response(
        response: 200,
        description: 'OK',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/OrderIndex')
                ),
                new OA\Property(property: 'links', ref: '#/components/schemas/Links'),
                new OA\Property(property: 'meta', ref: '#/components/schemas/Meta')
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Нет прав = не знаешь о существовании страницы'
    )]
    public function index() {}

    #[OA\Schema(
        schema: 'OrderIndex',
        type: 'object',
        properties: [
            new OA\Property(property: 'id', type: 'integer', example: 1),
            new OA\Property(property: 'total', type: 'integer', example: 46200),
            new OA\Property(property: 'name', type: 'string', example: 'Семён Семёныч'),
            new OA\Property(property: 'user_id', type: 'integer', example: 1),
            new OA\Property(property: 'phone', type: 'string', example: '+79999999999'),
            new OA\Property(property: 'email', type: 'string', example: 'example@mail.org'),
            new OA\Property(property: 'address', type: 'string', example: 'ул. Кукурузная, д. 35'),
            new OA\Property(property: 'comment', type: 'string', example: 'Побыстрее!'),
            new OA\Property(property: 'status', type: 'string', example: 'Создан, ожидает подтверждения.'),
        ]
    )]
    public function orderIndexSchema() {}



    #[OA\Post(
        tags: ['Order'],
        path: '/api/orders',
        summary: 'STORE - Создание заказа',
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: '#/components/schemas/OrderStoreRequestBody')
    )]
    #[OA\Response(
        response: 201,
        description: 'OK',
        content: new OA\JsonContent(ref: '#/components/schemas/OrderStoreResponseCreated')
    )]
    #[OA\Response(
        response: 422,
        description: 'Валидация не пройдена'
    )]
    public function store() {}

    #[OA\Schema(
        schema: 'OrderStoreRequestBody',
        type: 'object',
        properties: [
            new OA\Property(property: 'name', type: 'string', example: 'Василий Иваныч'),
            new OA\Property(property: 'phone', type: 'string', example: '+70123456789'),
            new OA\Property(property: 'email', type: 'string', example: 'ivanich@mail.org'),
            new OA\Property(property: 'address', type: 'string', example: 'ул. Ленина, д. 1'),
            new OA\Property(property: 'comment', type: 'string', example: 'Хочу скидку побольше!'),
            new OA\Property(
                property: 'products',
                type: 'array',
                items: new OA\Items(
                    properties: [
                        new OA\Property(property: 'product_id', type: 'integer', example: 1),
                        new OA\Property(property: 'count', type: 'integer', example: 3),
                    ]
                )
            )
        ]
    )]
    public function orderStoreRequestBodySchema() {}

    #[OA\Schema(
        schema: 'OrderStoreResponseCreated',
        type: 'object',
        properties: [
            new OA\Property(property: 'message', type: 'string', example: 'Заказ успешно создан.'),
            new OA\Property(property: 'order', type: 'string', example: '{ id, ... }'),
            new OA\Property(property: 'errors', type: 'string', example: '{...}')
        ]
    )]
    public function orderStoreResponseCreatedSchema() {}



    #[OA\Patch(
        tags: ['Order'],
        path: '/api/orders/{order}/status',
        summary: 'changeStatus - Изменение статуса заказа',
        security: [['jwt' => []]],
    )]
    #[OA\Parameter(
        name: 'order',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 2),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'OK',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Статус заказа #1 успешно изменён'),
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Неверный id статуса',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Указан id несуществующего статуса.'),
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Нет прав = не знаешь о существовании страницы'
    )]
    public function changeStatus() {}



    #[OA\Get(
        tags: ['Order'],
        path: '/api/orders/{order}',
        summary: 'SHOW - Отображение заказа',
        security: [['jwt' => []]],
    )]
    #[OA\Parameter(
        name: 'order',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'OK',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/OrderShowData'),
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Нет прав = не знаешь о существовании страницы'
    )]
    public function show() {}

    #[OA\Schema(
        schema: 'OrderShowData',
        type: 'object',
        properties: [
            new OA\Property(property: 'id', type: 'integer', example: 1),
            new OA\Property(property: 'total', type: 'integer', example: 46200),
            new OA\Property(property: 'name', type: 'string', example: 'Василий Иваныч'),
            new OA\Property(property: 'phone', type: 'string', example: '+70123456789'),
            new OA\Property(property: 'email', type: 'string', example: 'example@mail.org'),
            new OA\Property(property: 'address', type: 'string', example: 'ул. Ленина, д. 1'),
            new OA\Property(property: 'comment', type: 'string', example: 'Поживее!'),
            new OA\Property(property: 'status', type: 'string', example: 'Подтверждён, ожидает оплаты.'),
            new OA\Property(property: 'created_at', type: 'string', example: '2024-04-09T20:08:18.000000Z'),
            new OA\Property(property: 'updated_at', type: 'string', example: '2024-04-09T16:08:40.000000Z'),
            new OA\Property(
                property: 'products',
                type: 'array',
                items: new OA\Items(ref: '#/components/schemas/OrderShowProductItem')
            ),
        ]
    )]
    public function orderShowDataSchema() {}

    #[OA\Schema(
        schema: 'OrderShowProductItem',
        type: 'object',
        properties: [
            new OA\Property(property: 'id', type: 'integer', example: 1),
            new OA\Property(property: 'name', type: 'string', example: 'JBL pariatur'),
            new OA\Property(property: 'slug', type: 'string', example: 'jbl-pariatur'),
            new OA\Property(property: 'image', type: 'string', example: 'http://localhost:8000/storage/photos/products/overhead/wired/0.jpg'),
            new OA\Property(property: 'count', type: 'integer', example: 2),
            new OA\Property(property: 'price', type: 'integer', example: 23300),
        ]
    )]
    public function orderShowProductItemSchema() {}



    #[OA\Get(
        tags: ['Order'],
        path: '/api/users/{id}/orders',
        summary: 'showByUserId - Список заказов пользователя',
        security: [['jwt' => []]],
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'OK',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/OrderShowByUserIdItem')
                ),
                new OA\Property(property: 'links', ref: '#/components/schemas/Links'),
                new OA\Property(property: 'meta', ref: '#/components/schemas/Meta')
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Нет прав = не знаешь о существовании страницы'
    )]
    public function showByUserId() {}

    #[OA\Schema(
        schema: 'OrderShowByUserIdItem',
        type: 'object',
        properties: [
            new OA\Property(property: 'id', type: 'integer', example: 1),
            new OA\Property(property: 'total', type: 'integer', example: 46200),
            new OA\Property(property: 'address', type: 'string', example: 'ул. Ленина, д. 1'),
            new OA\Property(property: 'comment', type: 'string', example: 'Поживее!'),
            new OA\Property(property: 'status', type: 'string', example: 'Подтверждён, ожидает оплаты.'),
            new OA\Property(property: 'created_at', type: 'string', example: '2024-04-09T20:08:18.000000Z'),
            new OA\Property(property: 'updated_at', type: 'string', example: '2024-04-09T16:08:40.000000Z'),
        ]
    )]
    public function orderShowByUserIdItemSchema() {}



    #[OA\Delete(
        tags: ['Order'],
        path: '/api/orders/{order}',
        summary: 'DESTROY - Удаление заказа',
        security: [['jwt' => []]],
    )]
    #[OA\Parameter(
        name: 'order',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'OK',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Заказ удалён.'),
                new OA\Property(property: 'order', ref: '#/components/schemas/OrderDestroyOrderDetails')
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Нет прав = не знаешь о существовании страницы'
    )]
    public function destroy() {}

    #[OA\Schema(
        schema: 'OrderDestroyOrderDetails',
        type: 'object',
        properties: [
            new OA\Property(property: 'id', type: 'integer', example: 1),
            new OA\Property(property: 'user_id', type: 'integer', example: 1),
            new OA\Property(property: 'status_id', type: 'integer', example: 1),
            new OA\Property(property: 'total', type: 'integer', example: 46200),
            new OA\Property(property: 'name', type: 'string', example: 'Василий Иваныч'),
            new OA\Property(property: 'phone', type: 'string', example: '+70123456789'),
            new OA\Property(property: 'email', type: 'string', example: 'ivanich@mail.org'),
            new OA\Property(property: 'address', type: 'string', example: 'ул. Ленина, д. 1'),
            new OA\Property(property: 'comment', type: 'string', example: 'Поживее!'),
            new OA\Property(property: 'created_at', type: 'string', example: '2024-04-09T20:08:18.000000Z'),
            new OA\Property(property: 'updated_at', type: 'string', example: '2024-04-09T16:08:40.000000Z')
        ]
    )]
    public function orderDestroyOrderDetailsSchema() {}
}
