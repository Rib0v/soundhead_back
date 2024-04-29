<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\ChangeAddressRequest;
use App\Http\Requests\User\ChangeEmailRequest;
use App\Http\Requests\User\ChangePasswordRequest;
use App\Http\Requests\User\ChangeProfileRequest;
use App\Http\Requests\User\StoreRequest;
use App\Http\Resources\User\IndexRosource;
use App\Models\User;
use App\Services\AuthService;
use App\Services\JWTAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * @OA\Get(
     *   tags={"User"},
     *   path="/api/users",
     *   summary="INDEX - Список пользователей",
     *   security={{ "jwt": {} }},
     *   @OA\Response(response=200, description="OK",
     *     @OA\JsonContent(type="object",
     *       @OA\Property(property="data", type="array",
     *         @OA\Items(
     *           @OA\Property(property="id", type="integer", example=1),
     *           @OA\Property(property="name", type="string", example="Семён Семёныч"),
     *           @OA\Property(property="email", type="string", example="example@mail.org"),
     *           @OA\Property(property="address", type="string", example="ул. Кукурузная, д. 35"),
     *           @OA\Property(property="phone", type="string", example="+79999999999"),
     *           @OA\Property(property="orders", type="integer", example=3),
     *           @OA\Property(property="orders_total", type="integer", example=161100),
     *           @OA\Property(property="permissions", type="arr", example="[...]"),
     *         )
     *       ),
     *       @OA\Property(property="links", type="obj", example="{...}"),
     *       @OA\Property(property="meta", type="obj", example="{...}")
     *     )
     *   ),
     *   @OA\Response(response=404, description="Нет прав = не знаешь о существовании страницы"),
     * )
     */
    public function index(Request $request)
    {
        if (Gate::denies('admin', [$request->bearerToken()])) {
            abort(404, 'Not found');
        }

        return IndexRosource::collection(User::paginate(20));
    }

    /**
     * @OA\Post(
     *   tags={"User"},
     *   path="/api/users",
     *   summary="STORE - Регистрация пользователя и выдача пары токенов",
     *   @OA\RequestBody(required=true,
     *     @OA\JsonContent(type="object",
     *       @OA\Property(property="name", type="string", example="Василий Ложкин"),
     *       @OA\Property(property="email", type="string", example="vasya@mail.org"),
     *       @OA\Property(property="password", type="string", example="qwerty"),
     *       @OA\Property(property="password_confirmation", type="string", example="qwerty"),
     *     )
     *   ),
     *   @OA\Response(response=201, description="OK",
     *     @OA\JsonContent(
     *       @OA\Property(property="access", type="string", example="abcdefg..."),
     *       @OA\Property(property="access_exp", type="string", example=1535153),
     *       @OA\Property(property="user", type="obj", example="{id, ...}"),
     *     )
     *   ),
     *   @OA\Response(response=422, description="Валидация не пройдена",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Поле email обязательно. (and 1 more error)"),
     *       @OA\Property(property="errors", type="object",
     *         @OA\Property(property="email", type="arr", example="['Поле email обязательно.']"),
     *         @OA\Property(property="password", type="arr", example="['Поле пароль обязательно.']"),
     *       ),
     *     )
     *   ),
     * )
     */
    public function store(StoreRequest $request, JWTAuthService $jwt)
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
     * @OA\Get(
     *   tags={"User"},
     *   path="/api/users/{user}",
     *   summary="SHOW - Информация о пользователе",
     *   security={{ "jwt": {} }},
     *   @OA\Parameter(name="user", in="path", required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(response=200, description="OK",
     *     @OA\JsonContent(
     *       @OA\Property(property="data", type="object",
     *         @OA\Property(property="id", type="integer", example=1),
     *           @OA\Property(property="name", type="string", example="Семён Семёныч"),
     *           @OA\Property(property="email", type="string", example="example@mail.org"),
     *           @OA\Property(property="address", type="string", example="ул. Кукурузная, д. 35"),
     *           @OA\Property(property="phone", type="string", example="+79999999999"),
     *           @OA\Property(property="orders", type="integer", example=3),
     *           @OA\Property(property="orders_total", type="integer", example=161100),
     *           @OA\Property(property="permissions", type="arr", example="[...]"),
     *           @OA\Property(property="created_at", type="string", example="2024-04-09T16:08:39.000000Z"),
     *       )
     *     )
     *   ),
     *   @OA\Response(response=404, description="Нет прав = не знаешь о существовании страницы"),
     * )
     */
    public function show(User $user, Request $request)
    {
        if (Gate::denies('show', [$user, $request->bearerToken()])) {
            abort(404, 'Not found');
        }

        return new IndexRosource($user);
    }

    /**
     * @OA\Patch(
     *   tags={"User"},
     *   path="/api/users/{id}/password",
     *   summary="changePassword - Изменение пароля",
     *   security={{ "jwt": {} }},
     *   @OA\Parameter(name="id", in="path", required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       @OA\Property(property="old_password", type="string", example="qwerty"),
     *       @OA\Property(property="new_password", type="string", example="12345"),
     *       @OA\Property(property="new_password_confirmation", type="string", example="12345"),
     *     )
     *   ),
     *   @OA\Response(response=200, description="OK",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Пароль изменён."),
     *       @OA\Property(property="user", type="obj", example="{...}"),
     *     )
     *   ),
     *   @OA\Response(response=404, description="Пользователь не найден, либо нет прав."),
     *   @OA\Response(response=422, description="Валидация не пройдена"),
     * )
     */
    public function changePassword(int $id, User $user, ChangePasswordRequest $request, AuthService $service)
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
     * @OA\Patch(
     *   tags={"User"},
     *   path="/api/users/{id}/address",
     *   summary="changeAddress - Изменение адреса",
     *   security={{ "jwt": {} }},
     *   @OA\Parameter(name="id", in="path", required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       @OA\Property(property="address", type="string", example="г. Череповец, ул. Кирпичная, д. 3, кв. 1"),
     *     )
     *   ),
     *     @OA\Response(response=200, description="OK",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Адрес изменён."),
     *       @OA\Property(property="user", type="obj", example="{...}"),
     *     )
     *   ),
     *   @OA\Response(response=404, description="Пользователь не найден, либо нет прав."),
     *   @OA\Response(response=422, description="Валидация не пройдена"),
     * )
     */
    public function changeAddress(int $id, User $user, ChangeAddressRequest $request)
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
     * @OA\Patch(
     *   tags={"User"},
     *   path="/api/users/{id}/profile",
     *   summary="changeProfile - Изменение имени и телефона",
     *   security={{ "jwt": {} }},
     *   @OA\Parameter(name="id", in="path", required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       @OA\Property(property="name", type="string", example="Семён семёныч"),
     *       @OA\Property(property="phone", type="string", example="+70123456789"),
     *     )
     *   ),
     *   @OA\Response(response=200, description="OK",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Имя и телефон изменены."),
     *       @OA\Property(property="user", type="obj", example="{...}"),
     *     )
     *   ),
     *   @OA\Response(response=404, description="Пользователь не найден, либо нет прав."),
     *   @OA\Response(response=422, description="Валидация не пройдена"),
     * )
     */
    public function changeProfile(int $id, User $user, ChangeProfileRequest $request)
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
     * @OA\Patch(
     *   tags={"User"},
     *   path="/api/users/{id}/email",
     *   summary="changeEmail - Изменение email",
     *   description="password - действующий пароль для подтверждения операции", 
     *   security={{ "jwt": {} }},
     *   @OA\Parameter(name="id", in="path", required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       @OA\Property(property="email", type="string", example="example@mail.org"),
     *       @OA\Property(property="password", type="string", example="qwerty"),
     *     )
     *   ),
     *     @OA\Response(response=200, description="OK",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Email изменён."),
     *       @OA\Property(property="user", type="obj", example="{...}"),
     *     )
     *   ),
     *   @OA\Response(response=404, description="Пользователь не найден, либо нет прав."),
     *   @OA\Response(response=422, description="Валидация не пройдена, либо неверный пароль."),
     * )
     */
    public function changeEmail(int $id, User $user, ChangeEmailRequest $request, AuthService $service)
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
     * @OA\Delete(
     *   tags={"User"},
     *   path="/api/users/{id}",
     *   summary="DESTROY - Удаление пользователя",
     *   security={{ "jwt": {} }},
     *   @OA\Parameter(name="id", in="path", required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(response=200, description="OK",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Пользователь удалён."),
     *       @OA\Property(property="user", type="obj", example="{...}"),
     *     )
     *   ),
     *   @OA\Response(response=404, description="Нет прав, либо пользователь не существует")
     * )
     */
    public function destroy(Request $request, int $id)
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
