<?php

namespace App\Services\Auth;

use App\Exceptions\JWTValidationException;
use App\Services\Auth\JWTAuthService;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;

class JWTTokenGuard implements Guard
{
    use GuardHelpers;

    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var \Illuminate\Auth\EloquentUserProvider
     */
    protected $provider;

    protected JWTAuthService $jwt; //TODO сделать инъекцию через интерфейс

    /**
     * Да возникнет новый guard!
     *
     * @param  \Illuminate\Auth\EloquentUserProvider  $provider
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function __construct(UserProvider $provider, Request $request)
    {
        $this->request = $request;
        $this->provider = $provider;
        $this->user = null;
        $this->jwt = new JWTAuthService;
    }

    /**
     * Проверяем JWT-токен и возвращаем модель пользователя.
     * На самом деле модель пустая и в ней указан только id.
     * Это сделано для сокращения количества запросов к БД.
     * Все реляции из этой модели доступны как и в обычной.
     * А полную модель пользователя можно загрузить из БД,
     * вызвав метод $user->get()
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function user()
    {
        if ($this->user) {
            return $this->user;
        }

        $token = $this->request->bearerToken();

        if ($token) {

            try {
                $checked = $this->jwt->checkAccess($token);
                $model = $this->provider->createModel();
                $model->id = $checked->sub;
                $model->setPermissions($checked->per);

                $this->user = $model;
            } catch (JWTValidationException $e) {
                abort($e->getCode(), $e->getMessage());
            }
        }

        return $this->user;
    }

    /**
     * Проверяем email и пароль пользователя
     * В случае успеха сохраняем полученную модель
     * как авторизованного пользователя
     *
     * @param  array  $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        $user = $this->provider->retrieveByCredentials($credentials);
        $validated = $user && $this->provider->validateCredentials($user, $credentials);

        if ($validated) {
            $this->user = $user;
            return true;
        }

        return false;
    }
}
