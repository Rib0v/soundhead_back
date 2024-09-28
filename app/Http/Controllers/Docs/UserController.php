<?php

namespace App\Http\Controllers\Docs;

use OpenApi\Attributes as OA;

class UserController extends Controller
{
    #[OA\Get(
        tags: ['User'],
        path: '/api/users',
        summary: 'INDEX - Список пользователей',
        security: [['jwt' => []]],
    )]
    #[OA\Response(
        response: 200,
        description: 'OK',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/UserIndexItem')
                ),
                new OA\Property(property: 'links', ref: '#/components/schemas/Links'),
                new OA\Property(property: 'meta', ref: '#/components/schemas/Meta')
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Нет прав = не знаешь о существовании страницы',
    )]
    public function index() {}

    #[OA\Schema(
        schema: 'UserIndexItem',
        properties: [
            new OA\Property(property: 'id', type: 'integer', example: 1),
            new OA\Property(property: 'name', type: 'string', example: 'Семён Семёныч'),
            new OA\Property(property: 'email', type: 'string', example: 'example@mail.org'),
            new OA\Property(property: 'address', type: 'string', example: 'ул. Кукурузная, д. 35'),
            new OA\Property(property: 'phone', type: 'string', example: '+79999999999'),
            new OA\Property(property: 'orders', type: 'integer', example: 3),
            new OA\Property(property: 'orders_total', type: 'integer', example: 161100),
            new OA\Property(property: 'permissions', type: 'string', example: '[...]'),
        ]
    )]
    public function userIndexItemSchema() {}



    #[OA\Post(
        tags: ['User'],
        path: '/api/users',
        summary: 'STORE - Регистрация пользователя и выдача пары токенов',
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            ref: '#/components/schemas/UserRequestBody'
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'OK',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'access', type: 'string', example: 'abcdefg...'),
                new OA\Property(property: 'access_exp', type: 'integer', example: 1535153),
                new OA\Property(property: 'user', ref: '#/components/schemas/UserIndexItem')
            ]
        )
    )]
    #[OA\Response(
        response: 422,
        description: 'Валидация не пройдена',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Поле email обязательно. (and 1 more error)'),
                new OA\Property(
                    property: 'errors',
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'email', type: 'string', example: "['Поле email обязательно.']"),
                        new OA\Property(property: 'password', type: 'string', example: "['Поле пароль обязательно.']"),
                    ]
                ),
            ]
        )
    )]
    public function store() {}

    #[OA\Schema(
        schema: 'UserRequestBody',
        properties: [
            new OA\Property(property: 'name', type: 'string', example: 'Василий Ложкин'),
            new OA\Property(property: 'email', type: 'string', example: 'vasya@mail.org'),
            new OA\Property(property: 'password', type: 'string', example: 'qwerty'),
            new OA\Property(property: 'password_confirmation', type: 'string', example: 'qwerty'),
        ]
    )]
    public function userRequestBodySchema() {}



    #[OA\Get(
        tags: ['User'],
        path: '/api/users/{user}',
        summary: 'SHOW - Информация о пользователе',
        security: [['jwt' => []]],
    )]
    #[OA\Parameter(
        name: 'user',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'OK',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/UserIndexItem')
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Нет прав = не знаешь о существовании страницы',
    )]
    public function show() {}



    #[OA\Patch(
        tags: ['User'],
        path: '/api/users/{id}/password',
        summary: 'changePassword - Изменение пароля',
        security: [['jwt' => []]],
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            ref: '#/components/schemas/UserChangePasswordRequestBody'
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'OK',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Пароль изменён.'),
                new OA\Property(property: 'user', ref: '#/components/schemas/UserIndexItem')
            ]
        )
    )]
    #[OA\Response(response: 404, description: 'Пользователь не найден, либо нет прав.')]
    #[OA\Response(response: 422, description: 'Валидация не пройдена')]
    public function changePassword() {}

    #[OA\Schema(
        schema: 'UserChangePasswordRequestBody',
        properties: [
            new OA\Property(property: 'old_password', type: 'string', example: 'qwerty'),
            new OA\Property(property: 'new_password', type: 'string', example: '12345'),
            new OA\Property(property: 'new_password_confirmation', type: 'string', example: '12345'),
        ]
    )]
    public function userChangePasswordRequestBodySchema() {}



    #[OA\Patch(
        tags: ['User'],
        path: '/api/users/{id}/address',
        summary: 'changeAddress - Изменение адреса',
        security: [['jwt' => []]],
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'address', type: 'string', example: 'г. Череповец, ул. Кирпичная, д. 3, кв. 1')
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'OK',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Адрес изменён.'),
                new OA\Property(property: 'user', ref: '#/components/schemas/UserIndexItem')
            ]
        )
    )]
    #[OA\Response(response: 404, description: 'Пользователь не найден, либо нет прав.')]
    #[OA\Response(response: 422, description: 'Валидация не пройдена')]
    public function changeAddress() {}



    #[OA\Patch(
        tags: ['User'],
        path: '/api/users/{id}/profile',
        summary: 'changeProfile - Изменение имени и телефона',
        security: [['jwt' => []]],
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'Семён семёныч'),
                new OA\Property(property: 'phone', type: 'string', example: '+70123456789')
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'OK',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Имя и телефон изменены.'),
                new OA\Property(property: 'user', ref: '#/components/schemas/UserIndexItem')
            ]
        )
    )]
    #[OA\Response(response: 404, description: 'Пользователь не найден, либо нет прав.')]
    #[OA\Response(response: 422, description: 'Валидация не пройдена')]
    public function changeProfile() {}



    #[OA\Patch(
        tags: ['User'],
        path: '/api/users/{id}/email',
        summary: 'changeEmail - Изменение email',
        description: 'password - действующий пароль для подтверждения операции',
        security: [['jwt' => []]],
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'email', type: 'string', example: 'example@mail.org'),
                new OA\Property(property: 'password', type: 'string', example: 'qwerty')
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'OK',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Email изменён.'),
                new OA\Property(property: 'user', ref: '#/components/schemas/UserIndexItem')
            ]
        )
    )]
    #[OA\Response(response: 404, description: 'Пользователь не найден, либо нет прав.')]
    #[OA\Response(response: 422, description: 'Валидация не пройдена, либо неверный пароль.')]
    public function changeEmail() {}



    #[OA\Delete(
        tags: ['User'],
        path: '/api/users/{id}',
        summary: 'DESTROY - Удаление пользователя',
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
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Пользователь удалён.'),
                new OA\Property(property: 'user', ref: '#/components/schemas/UserIndexItem')
            ]
        )
    )]
    #[OA\Response(response: 404, description: 'Пользователь не найден, либо нет прав.')]
    public function destroy() {}
}
