<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Services\Auth\JWTAuthService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Аутентификация пользователя
     * и выдача пары токенов
     * 
     * @param LoginRequest $request
     * @param JWTAuthService $jwt
     * 
     * @return Response
     */
    public function login(LoginRequest $request, JWTAuthService $jwt): Response
    {
        if (!Auth::validate(['email' => $request->email, 'password' => $request->password])) {
            return response(['errors' => ['email' => ['Неверный логин или пароль.']]], 401);
        }

        $user = Auth::user();
        $tokens = $jwt->create($user->id, $user->permissionIds);

        return response([
            'user_id' => $user->id,
            'access' => $tokens['access'],
            'access_exp' => $tokens['access_exp'],
        ])
            ->cookie('refresh', $tokens['refresh'], $tokens['refresh_minutes'], '/', getAppUrl(), false, true);
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
        /**
         * В случае неудачной проверки токена
         * выбрасывается кастомное исключение
         * App\Exceptions\JWTValidationException
         * которое генерирует Response.
         * Это касается всех методов, в которых
         * происходит проверка токена.
         */
        $token = $request->bearerToken();
        $checked = $jwt->checkAccess($token);
        return response(['decoded' => $checked]);
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
        $token = $request->cookie('refresh');
        $checked = $jwt->checkRefresh($token);
        return response(['decoded' => $checked]);
    }

    /**
     * Обновление пары токенов
     * 
     * @param Request $request
     * @param JWTAuthService $jwt
     * 
     * @return Response
     */
    public function refresh(Request $request, JWTAuthService $jwt): Response
    {
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
            getAppUrl(),
            false,
            true
        );
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

    public function test()
    {
        return response(['message' => 'test']);
    }
}
