<?php

namespace App\Services\Auth;

use App\Contracts\TokenRepository;
use App\Exceptions\JWTValidationException;
use DomainException;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use InvalidArgumentException;
use stdClass;
use UnexpectedValueException;

class JWTAuthService
{
    public function __construct(
        protected JWTAuthDTO $config,
        protected TokenRepository $tokenRepository
    ) {}

    /**
     * Генерация новой пары токенов
     * 
     * @param int $userId
     * @param array $permissions
     * @return array ['access', 'refresh', 'access_exp', 'refresh_minutes']
     */
    public function create(int $userId, array $permissions = [], ?int $time = null): array
    {
        $time ??= time();
        $expiredAccess = $time + 60 * $this->config->access_ttl;
        $expiredRefresh = $time + 60 * $this->config->refresh_ttl;

        $payload = [
            'iss' => $this->config->issuer,
            'sub' => $userId,
            'per' => join(',', $permissions),
        ];

        $payloadAccess = [
            ...$payload,
            'exp' => $expiredAccess,
            'typ' => 'AT',
        ];

        $payloadRefresh = [
            ...$payload,
            'exp' => $expiredRefresh,
            'typ' => 'RT',
        ];

        $jwtAccess = JWT::encode($payloadAccess, $this->config->key, 'HS256');
        $jwtRefresh = JWT::encode($payloadRefresh, $this->config->key, 'HS256');

        $this->tokenRepository->saveRefreshToken(
            userId: $userId,
            token: $jwtRefresh,
            expiredTimestamp: $expiredRefresh
        );

        return [
            'access' => $jwtAccess,
            'refresh' => $jwtRefresh,
            'access_exp' => $expiredAccess,
            'refresh_minutes' => $this->config->refresh_ttl, // для cookie, там exp указывается в минутах, а не timestamp
        ];
    }


    /**
     * Проверка токена
     * 
     * @param string|null $token
     * @return stdClass decoded payload
     * @throws JWTValidationException 403 = просрочен, 401 = валидация провалена
     */
    public function decode(?string $token): stdClass
    {
        if (!$token) {
            throw new JWTValidationException("Токен не найден.", 401);
        }

        JWT::$leeway = $this->config->leeway;

        try {
            $decoded = JWT::decode($token, new Key($this->config->key, 'HS256'));
            $decoded->per = $this->permissionsFromTokenToArray($decoded->per);
        } catch (InvalidArgumentException $e) {
            throw new JWTValidationException("Ключ отсутствует, или имеет неверный формат.", 401);
        } catch (DomainException $e) {
            throw new JWTValidationException("Алгоритм не поддерживается, или ключ недействителен.", 401);
        } catch (SignatureInvalidException $e) {
            throw new JWTValidationException("Проверка подписи не удалась", 401);
        } catch (BeforeValidException $e) {
            throw new JWTValidationException("Токен ещё не начал действовать.", 401);
        } catch (ExpiredException $e) {
            throw new JWTValidationException("Токен просрочен.", 403);
        } catch (UnexpectedValueException $e) {
            throw new JWTValidationException("Неверный формат или несоответствующий алгоритм.", 401);
        }

        return $decoded;
    }

    /**
     * Преобразование строковых данных
     * из токена в целочисленный массив
     * 
     * @param string $permissions
     * @return int[]
     */
    protected function permissionsFromTokenToArray(string $permissions): array
    {
        if ($permissions === '') {
            return [];
        }

        $permissions = explode(',', $permissions);
        return array_map(fn($permission) => (int)$permission, $permissions);
    }

    /**
     * Проверка access токена
     * 
     * @param string|null $token
     * @return stdClass  decoded jwt-payload
     * @throws JWTValidationException  401 = не access токен
     */
    public function checkAccess(?string $token): stdClass
    {
        $decoded = $this->decode($token);
        $this->checkTokenIsAccessType($decoded);

        return $decoded;
    }

    /**
     * Проверка refresh токена
     * 
     * @param string|null $token
     * @return stdClass  decoded jwt-payload
     * @throws JWTValidationException  403 = не refresh, либо не найден в белом списке БД
     */
    public function checkRefresh(?string $token): stdClass
    {
        $decoded = $this->decode($token);
        $this->checkTokenIsRefreshType($decoded);

        if (! $this->tokenRepository->isRefreshTokenExists($token)) {
            throw new JWTValidationException("Refresh-токен недействителен.", 403);
        }

        return $decoded;
    }

    /**
     * Обновление пары токенов
     * 
     * @param string|null $token
     * @return array  ['decoded', 'tokens']
     * @throws JWTValidationException  в вызываемых методах
     */
    public function refresh(?string $token): array
    {
        try {
            $decoded = $this->checkRefresh($token);
        } catch (JWTValidationException $e) {
            throw new JWTValidationException(message: $e->getMessage(), code: $e->getCode(), withoutRefreshCookie: true);
        }

        return ['decoded' => $decoded, 'tokens' => $this->create($decoded->sub, $decoded->per)];
    }

    /**
     * Удаление refresh токена из БД
     * 
     * @param string|null $token
     * @return void
     * @throws JWTValidationException  403 = не refresh, либо не найден в белом списке БД
     */
    public function destroy(?string $token): void
    {
        $decoded = $this->decode($token);
        $this->checkTokenIsRefreshType($decoded);

        $deleted = $this->tokenRepository->removeRefreshToken($token);

        if (!$deleted) {
            throw new JWTValidationException("Токен не найден в базе.", 403);
        }
    }
    /**
     * Проверка, что тип токена access
     * 
     * @param stdClass $token
     * @return void
     * @throws JWTValidationException  401 = не access токен
     */
    protected function checkTokenIsAccessType(stdClass $token): void
    {
        if ($token->typ !== 'AT') {
            throw new JWTValidationException("Токен не является типом access.", 401);
        }
    }

    /**
     * Проверка, что тип токена refresh
     * @param stdClass $token
     * @return void
     * @throws JWTValidationException  403 = не refresh токен
     */
    protected function checkTokenIsRefreshType(stdClass $token): void
    {
        if ($token->typ !== 'RT') {
            throw new JWTValidationException("Токен не является типом refresh.", 403);
        }
    }
}
