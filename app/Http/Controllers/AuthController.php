<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;
use App\Services\JWTAuthService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    /**
     * Аутентификация пользователя
     * и выдача пары токенов
     * 
     * @param LoginRequest $request
     * @param JWTAuthService $jwt
     * @param AuthService $service
     * 
     * @return Response
     */
    public function login(LoginRequest $request, JWTAuthService $jwt, AuthService $service): Response
    {
        $validated = $request->validated();

        $user = $service->getUser($validated['email']);
        $checkedPassword = $service->checkPassword($user, $validated['password']);

        if (!$checkedPassword) {
            return response(['errors' => ['email' => ['Неверный логин или пароль.']]], 401);
        }

        $userPermissions = $service->getPermissions($user);

        $tokens = $jwt->create($user->id, $userPermissions);

        return response([
            'user_id' => $user->id,
            'access' => $tokens['access'],
            'access_exp' => $tokens['access_exp'],
        ])
            ->cookie('refresh', $tokens['refresh'], $tokens['refresh_minutes'], '/', $service->getAppUrl(), false, true);
    }

    /**
     * Проверка access токена
     * 
     * @param Request $request
     * @param JWTAuthService $jwt
     * 
     * @return Response
     */
    public function checkAccess(Request $request, JWTAuthService $jwt): Response
    {
        try {
            $token = $request->bearerToken();
            $checked = $jwt->checkAccess($token);
            return response(['decoded' => $checked]);
        } catch (\Exception $e) {
            return response(['message' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Проверка refresh токена
     * 
     * @param Request $request
     * @param JWTAuthService $jwt
     * 
     * @return Response
     */
    public function checkRefresh(Request $request, JWTAuthService $jwt): Response
    {
        try {
            $token = $request->cookie('refresh');
            $checked = $jwt->checkRefresh($token);
            return response(['decoded' => $checked]);
        } catch (\Exception $e) {
            return response(['message' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Обновление пары токенов
     * 
     * @param Request $request
     * @param JWTAuthService $jwt
     * @param AuthService $service
     * 
     * @return Response
     */
    public function refresh(Request $request, JWTAuthService $jwt, AuthService $service): Response
    {
        try {
            $refreshed = $jwt->refresh($request->cookie('refresh'));

            return response([

                'user_id' => $refreshed['decoded']->sub,
                'access' => $refreshed['tokens']['access'],
                'access_exp' => $refreshed['tokens']['access_exp']

            ])->cookie(
                'refresh',
                $refreshed['tokens']['refresh'],
                $refreshed['tokens']['refresh_minutes'],
                '/',
                $service->getAppUrl(),
                false,
                true
            );
        } catch (\Exception $e) {
            return response(['message' => $e->getMessage()], $e->getCode())->withoutCookie('refresh');
        }
    }

    /**
     * Удаление refresh токена из куков
     * и белого списка в БД
     * 
     * @param Request $request
     * @param JWTAuthService $jwt
     * 
     * @return Response
     */
    public function logout(Request $request, JWTAuthService $jwt): Response
    {
        try {
            $token = $request->cookie('refresh');
            $jwt->destroy($token);

            return response(['message' => 'Токен успешно удалён из базы и куков.'])->withoutCookie('refresh');
        } catch (\Exception $e) {
            return response(['message' => $e->getMessage() . 'Удалён из куков.'], $e->getCode())->withoutCookie('refresh');
        }
    }
}
