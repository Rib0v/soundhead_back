<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\ChangeAddressRequest;
use App\Http\Requests\User\ChangeEmailRequest;
use App\Http\Requests\User\ChangePasswordRequest;
use App\Http\Requests\User\ChangeProfileRequest;
use App\Http\Requests\User\StoreRequest;
use App\Http\Resources\User\IndexResource;
use App\Models\User;
use App\Services\AuthService;
use App\Services\JWTAuthService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    /**
     * Список пользователей
     * 
     * @param Request $request
     * 
     * @return Response
     */
    public function index(Request $request): Response
    {
        if (Gate::denies('admin', [$request->bearerToken()])) {
            abort(404, 'Not found');
        }

        return response(IndexResource::collection(User::paginate(20)));
    }

    /**
     * Регистрация пользователя
     * и выдача пары токенов
     * 
     * @param StoreRequest $request
     * @param JWTAuthService $jwt
     * 
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
     * @param Request $request
     * 
     * @return Response
     */
    public function show(User $user, Request $request): Response
    {
        if (Gate::denies('show', [$user, $request->bearerToken()])) {
            abort(404, 'Not found');
        }

        return response(new IndexResource($user));
    }

    /**
     * Изменение пароля пользователя
     * 
     * @param int $id
     * @param User $user
     * @param ChangePasswordRequest $request
     * @param AuthService $service
     * 
     * @return Response
     */
    public function changePassword(int $id, User $user, ChangePasswordRequest $request, AuthService $service): Response
    {
        if (Gate::denies('changePassword', [$user, $request->bearerToken(), $id])) {
            abort(404, 'Not found');
        }

        $validated = $request->validated();
        $user = User::find($id);

        if (!$user) return response(['message' => 'Пользователь не существует'], 404);

        $checkedPassword = $service->checkPassword($user, $validated['old_password']);

        if (!$checkedPassword) {
            return response([
                'message' => 'Введён неверный пароль.',
                'errors' => ['old_password' => ['Введён неверный пароль.']]
            ], 422);
        }

        $user->password = $validated['new_password']; // пароль хешируется автоматически
        $user->save();

        return response(['message' => 'Пароль изменён.', 'user' => $user]);
    }

    /**
     * Изменение адреса пользователя
     * 
     * @param int $id
     * @param User $user
     * @param ChangeAddressRequest $request
     * 
     * @return Response
     */
    public function changeAddress(int $id, User $user, ChangeAddressRequest $request): Response
    {
        if (Gate::denies('changePassword', [$user, $request->bearerToken(), $id])) {
            abort(404, 'Not found');
        }

        $validated = $request->validated();
        $user = User::find($id);

        if (!$user) return response(['message' => 'Пользователь не существует'], 404);

        $user->address = $validated['address'];
        $user->save();

        return response(['message' => 'Адрес изменён.', 'user' => $user]);
    }

    /**
     * Изменение имени и телефона пользователя
     * 
     * @param int $id
     * @param User $user
     * @param ChangeProfileRequest $request
     * 
     * @return Response
     */
    public function changeProfile(int $id, User $user, ChangeProfileRequest $request): Response
    {
        if (Gate::denies('changePassword', [$user, $request->bearerToken(), $id])) {
            abort(404, 'Not found');
        }

        $validated = $request->validated();
        $user = User::find($id);

        if (!$user) return response(['message' => 'Пользователь не существует'], 404);

        $user->name = $validated['name'];
        $user->phone = $validated['phone'];
        $user->save();

        return response(['message' => 'Имя и телефон изменены.', 'user' => $user]);
    }

    /**
     * Изменение email пользователя
     * 
     * @param int $id
     * @param User $user
     * @param ChangeEmailRequest $request
     * @param AuthService $service
     * 
     * @return Response
     */
    public function changeEmail(int $id, User $user, ChangeEmailRequest $request, AuthService $service): Response
    {
        if (Gate::denies('changePassword', [$user, $request->bearerToken(), $id])) {
            abort(404, 'Not found');
        }

        $validated = $request->validated();
        $user = User::find($id);

        if (!$user) return response(['message' => 'Пользователь не существует'], 404);

        $checkedPassword = $service->checkPassword($user, $validated['password']);

        if (!$checkedPassword) {
            return response([
                'message' => 'Введён неверный пароль.',
                'errors' => ['password' => ['Введён неверный пароль.']]
            ], 422);
        }

        $user->email = $validated['email'];
        $user->save();

        return response(['message' => 'Email изменён.', 'user' => $user]);
    }

    /**
     * Удаление пользователя
     * 
     * @param Request $request
     * @param int $id
     * 
     * @return Response
     */
    public function destroy(Request $request, int $id): Response
    {
        if (Gate::denies('admin', [$request->bearerToken()])) {
            abort(404, 'Not found');
        }

        $user = User::find($id);

        if (!$user) return response(['error' => 'Пользователь не существует'], 404);

        if ($user->token) $user->token->delete();
        $user->delete();

        return response(['message' => 'Пользователь удалён.', 'user' => $user]);
    }
}
