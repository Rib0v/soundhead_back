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
     * @OA\Post(
     *   tags={"Auth"},
     *   path="/api/auth/login",
     *   summary="LOGIN - Аутентификация пользователя и выдача пары токенов",
     *   @OA\RequestBody(required=true,
     *     @OA\JsonContent(type="object",
     *       @OA\Property(property="email", type="string", example="a@a.a"),
     *       @OA\Property(property="password", type="string", example="123"),
     *     )
     *   ),
     *   @OA\Response(response=200, description="OK",
     *     @OA\JsonContent(
     *       @OA\Property(property="user_id", type="integer", example="1"),
     *       @OA\Property(property="access", type="string", example="abcdefg...."),
     *       @OA\Property(property="access_exp", type="integer", example="1712756994"),
     *     )
     *   ),
     *   @OA\Response(response=401, description="Неверный логин или пароль."),
     *   @OA\Response(response=422, description="Валидация не пройдена",
     *     @OA\JsonContent(
     *       @OA\Property(property="errors", type="object",
     *         @OA\Property(property="email", type="arr", example="['error1', 'error2', ...]"),
     *         @OA\Property(property="password", type="arr", example="['error1', 'error2', ...]"),
     *       ),
     *     ),
     *   ),
     * )
     */
    public function login(LoginRequest $request, JWTAuthService $jwt, AuthService $service)
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
     * @OA\Get(
     *   tags={"Auth"},
     *   path="/api/auth/checkacc",
     *   summary="checkAccess - Проверка access токена",
     *   security={ { "jwt": {} } },
     *   @OA\Response(response=200, description="OK",
     *     @OA\JsonContent(
     *       @OA\Property(property="decoded", type="object",
     *         @OA\Property(property="iss", type="string", example="http://example.com"),
     *         @OA\Property(property="sub", type="integer", example="1"),
     *         @OA\Property(property="per", type="arr", example="[1, 2, 3]"),
     *         @OA\Property(property="exp", type="integer", example="1712757994"),
     *         @OA\Property(property="typ", type="string", example="AT"),
     *       ),
     *     )
     *   ),
     *   @OA\Response(response=401, description="Валидация провалена",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Токен не найден."),
     *     )
     *   ),
     *   @OA\Response(response=403, description="Токен просрочен")
     * )
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
     * @OA\Get(
     *   tags={"Auth"},
     *   path="/api/auth/checkref",
     *   summary="checkRefresh - Проверка refresh токена",
     *   description="Refresh токен хранится в куках",
     *   security={ { "jwt": {} } },
     *   @OA\Response(response=200, description="OK",
     *     @OA\JsonContent(
     *       @OA\Property(property="decoded", type="object",
     *         @OA\Property(property="iss", type="string", example="http://example.com"),
     *         @OA\Property(property="sub", type="integer", example="1"),
     *         @OA\Property(property="per", type="arr", example="[1, 2, 3]"),
     *         @OA\Property(property="exp", type="integer", example="1715349934"),
     *         @OA\Property(property="typ", type="string", example="RT"),
     *       ),
     *     )
     *   ),
     *   @OA\Response(response=401, description="Валидация провалена",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Токен не найден."),
     *     )
     *   ),
     *   @OA\Response(response=403, description="Токен просрочен, либо недействителен")
     * )
     */
    public function checkRefresh(Request $request, JWTAuthService $jwt)
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
     * @OA\Get(
     *   tags={"Auth"},
     *   path="/api/auth/refresh",
     *   summary="REFRESH - Обновление пары токенов",
     *   security={ { "jwt": {} } },
     *   @OA\Response(response=200, description="OK",
     *     @OA\JsonContent(
     *       @OA\Property(property="user_id", type="integer", example="1"),
     *       @OA\Property(property="access", type="string", example="abcdefg...."),
     *       @OA\Property(property="access_exp", type="integer", example="1712756994"),
     *     )
     *   ),
     *   @OA\Response(response=401, description="Валидация провалена",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Токен не найден.")
     *     )
     *   ),
     *   @OA\Response(response=403, description="Refresh токен просрочен, либо недействителен")
     * )
     */
    public function refresh(Request $request, JWTAuthService $jwt, AuthService $service)
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
     * @OA\Get(
     *   tags={"Auth"},
     *   path="/api/auth/logout",
     *   summary="LOGOUT - Удаление refresh токена из куков и белого списка в БД",
     *   security={ { "jwt": {} } },
     *   @OA\Response(response=200, description="Токен удалён из белого списка БД. Куки очищены.",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Токен успешно удалён из базы и куков."),
     *     )
     *   ),
     *   @OA\Response(response=401, description="Валидация провалена. Куки очищены."),
     *   @OA\Response(response=403, description="Refresh токен просрочен, либо недействителен. Куки очищены.")
     * )
     */
    public function logout(Request $request, JWTAuthService $jwt)
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
