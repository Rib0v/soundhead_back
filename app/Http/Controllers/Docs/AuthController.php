<?php

namespace App\Http\Controllers\Docs;

use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    #[OA\Post(
        tags: ['Auth'],
        path: '/api/auth/login',
        summary: 'LOGIN - Аутентификация пользователя и выдача пары токенов'
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: '#/components/schemas/AuthLoginRequestBody')
    )]
    #[OA\Response(
        response: 200,
        description: 'OK',
        content: new OA\JsonContent(ref: '#/components/schemas/AuthToken')
    )]
    #[OA\Response(response: 401, description: 'Неверный логин или пароль.')]
    #[OA\Response(
        response: 422,
        description: 'Валидация не пройдена',
        content: new OA\JsonContent(ref: '#/components/schemas/AuthLoginResponseNotValid')
    )]
    public function login() {}

    #[OA\Schema(schema: 'AuthLoginRequestBody')]
    #[OA\Property(property: 'email', type: 'string', example: 'a@a.a')]
    #[OA\Property(property: 'password', type: 'string', example: '123')]
    public function authLoginRequestBodySchema() {}

    #[OA\Schema(schema: 'AuthToken')]
    #[OA\Property(property: 'user_id', type: 'integer', example: 1)]
    #[OA\Property(property: 'access', type: 'string', example: 'abcdefg....')]
    #[OA\Property(property: 'access_exp', type: 'integer', example: 1712756994)]
    public function authTokenSchema(): void {}

    #[OA\Schema(schema: 'AuthLoginResponseNotValid')]
    #[OA\Property(
        property: 'errors',
        type: 'object',
        properties: [
            new OA\Property(property: 'email', type: 'string', example: ['error1', 'error2']),
            new OA\Property(property: 'password', type: 'string', example: ['error1', 'error2']),
        ]
    )]
    public function authLoginResponseNotValidSchema() {}



    #[OA\Get(
        tags: ['Auth'],
        path: '/api/auth/checkacc',
        summary: 'checkAccess - Проверка access токена',
        security: [['jwt' => []]]
    )]
    #[OA\Response(
        response: 200,
        description: 'OK',
        content: new OA\JsonContent(ref: '#/components/schemas/AuthJWTServiceCheck')
    )]
    #[OA\Response(
        response: 401,
        description: 'Валидация провалена',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Токен не найден.'),
            ]
        )
    )]
    #[OA\Response(response: 403, description: 'Токен просрочен')]
    public function checkAccess() {}

    #[OA\Schema(schema: 'AuthJWTServiceCheck')]
    #[OA\Property(
        property: 'decoded',
        type: 'object',
        properties: [
            new OA\Property(property: 'iss', type: 'string', example: 'http://example.com'),
            new OA\Property(property: 'sub', type: 'integer', example: 1),
            new OA\Property(property: 'per', type: 'string', example: '[1, 2, 3]'),
            new OA\Property(property: 'exp', type: 'integer', example: 1712757994),
            new OA\Property(property: 'typ', type: 'string', example: 'AT'),
        ]
    )]
    public function authJWTServiceCheckSchema() {}



    #[OA\Get(
        tags: ['Auth'],
        path: '/api/auth/checkref',
        summary: 'checkRefresh - Проверка refresh токена',
        description: 'Refresh токен хранится в куках',
        security: [['jwt' => []]]
    )]
    #[OA\Response(
        response: 200,
        description: 'OK',
        content: new OA\JsonContent(ref: '#/components/schemas/AuthJWTServiceCheck')
    )]
    #[OA\Response(
        response: 401,
        description: 'Валидация провалена',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Токен не найден.'),
            ]
        )
    )]
    #[OA\Response(response: 403, description: 'Токен просрочен, либо недействителен')]
    public function checkRefresh() {}



    #[OA\Get(
        tags: ['Auth'],
        path: '/api/auth/refresh',
        summary: 'REFRESH - Обновление пары токенов',
        security: [['jwt' => []]]
    )]
    #[OA\Response(
        response: 200,
        description: 'OK',
        content: new OA\JsonContent(ref: '#/components/schemas/AuthToken')
    )]
    #[OA\Response(
        response: 401,
        description: 'Валидация провалена',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Токен не найден.'),
            ]
        )
    )]
    #[OA\Response(response: 403, description: 'Refresh токен просрочен, либо недействителен')]
    public function refresh() {}



    #[OA\Get(
        tags: ['Auth'],
        path: '/api/auth/logout',
        summary: 'LOGOUT - Удаление refresh токена из куков и белого списка в БД',
        security: [['jwt' => []]]
    )]
    #[OA\Response(
        response: 200,
        description: 'Токен удалён из белого списка БД. Куки очищены.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Токен успешно удалён из базы и куков.'),
            ]
        )
    )]
    #[OA\Response(response: 401, description: 'Валидация провалена. Куки очищены.')]
    #[OA\Response(response: 403, description: 'Refresh токен просрочен, либо недействителен. Куки очищены.')]
    public function logout() {}
}
