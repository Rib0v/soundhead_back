<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\ChangeAddressRequest;
use App\Http\Requests\User\ChangeEmailRequest;
use App\Http\Requests\User\ChangePasswordRequest;
use App\Http\Requests\User\ChangeProfileRequest;
use App\Http\Requests\User\StoreRequest;
use App\Http\Resources\User\IndexResource;
use App\Models\User;
use App\Services\Auth\JWTAuthService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserController extends Controller
{
    /**
     * Список пользователей
     * 
     * @return Response
     */
    public function index(): Response
    {
        return response(IndexResource::collection(User::paginate(20)));
    }

    /**
     * Регистрация пользователя
     * и выдача пары токенов
     * 
     * @param StoreRequest $request
     * @param JWTAuthService $jwt
     * @return Response
     */
    public function store(StoreRequest $request, JWTAuthService $jwt): Response
    {
        $validated = $request->validated();
        $user = User::create($validated);
        $tokens = $jwt->create($user->id);

        return response([
            'user' => $user,
            'user_id' => $user->id,
            'access' => $tokens['access'],
            'access_exp' => $tokens['access_exp'],
        ], 201)
            ->cookie('refresh', $tokens['refresh'], $tokens['refresh_minutes'], '/', 'localhost', true, true);
    }

    /**
     * Информация о пользователе
     * 
     * @param User $user
     * @return Response
     */
    public function show(User $user): Response
    {
        return response(new IndexResource($user));
    }

    /**
     * Изменение пароля пользователя
     * 
     * @param User $user
     * @param ChangePasswordRequest $request
     * @return Response
     */
    public function changePassword(User $user, ChangePasswordRequest $request): Response
    {
        // Вся логика валидации находится внутри запроса
        $user->password = $request->newPassword; // пароль хешируется автоматически
        $user->save();

        return response(['message' => 'Пароль изменён.', 'user' => $user]);
    }

    /**
     * Изменение адреса пользователя
     * 
     * @param User $user
     * @param ChangeAddressRequest $request
     * @return Response
     */
    public function changeAddress(User $user, ChangeAddressRequest $request): Response
    {
        $user->address = $request->address;
        $user->save();

        return response(['message' => 'Адрес изменён.', 'user' => $user]);
    }

    /**
     * Изменение имени и телефона пользователя
     * 
     * @param User $user
     * @param ChangeProfileRequest $request
     * @return Response
     */
    public function changeProfile(User $user, ChangeProfileRequest $request): Response
    {
        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->save();

        return response(['message' => 'Имя и телефон изменены.', 'user' => $user]);
    }

    /**
     * Изменение email пользователя
     * 
     * @param User $user
     * @param ChangeEmailRequest $request
     * @return Response
     */
    public function changeEmail(User $user, ChangeEmailRequest $request): Response
    {
        $user->email = $request->email;
        $user->save();

        return response(['message' => 'Email изменён.', 'user' => $user]);
    }

    /**
     * Удаление пользователя
     * 
     * @param User $user
     * @return Response
     */
    public function destroy(User $user): Response
    {
        if ($user->token) {
            $user->token->delete();
        }

        $user->delete();

        return response(['message' => 'Пользователь удалён.', 'user' => $user]);
    }
}
