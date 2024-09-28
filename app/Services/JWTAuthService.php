<?php

namespace App\Services;

use App\Models\Token;
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
    private string $issuer;
    private string $key;
    private int $access_ttl;
    private int $refresh_ttl;
    private int $leeway;

    public function __construct()
    {
        $this->issuer = config('jwt.issuer');
        $this->key = config('jwt.key');
        $this->access_ttl = config('jwt.access_ttl');
        $this->refresh_ttl = config('jwt.refresh_ttl');
        $this->leeway = config('jwt.leeway');
    }

    /**
     * Генерация новой пары токенов
     * 
     * @param int $userId
     * @param array $permissions
     * 
     * @return array ['access', 'refresh', 'access_exp', 'refresh_minutes']
     */
    public function create(int $userId, array $permissions = []): array
    {
        $time = time();
        $expiredAccess = $time + 60 * $this->access_ttl;
        $expiredRefresh = $time + 60 * $this->refresh_ttl;

        $payload = [
            'iss' => $this->issuer,
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

        $jwtAccess = JWT::encode($payloadAccess, $this->key, 'HS256');
        $jwtRefresh = JWT::encode($payloadRefresh, $this->key, 'HS256');

        Token::updateOrCreate(
            ['user_id' => $userId],
            ['token' => $jwtRefresh, 'expired_at' => date('Y-m-d H:i:s', $expiredRefresh)],
        );

        return [
            'access' => $jwtAccess,
            'refresh' => $jwtRefresh,
            'access_exp' => $expiredAccess,
            'refresh_minutes' => $this->refresh_ttl, // для cookie, там exp указывается в минутах, а не timestamp
        ];
    }


    /**
     * Проверка токена
     * 
     * @param string|null $token
     * 
     * @return stdClass decoded payload
     * 
     * @throws \Exception 403 = просрочен, 401 = валидация провалена
     */
    public function check(?string $token): stdClass
    {
        if (!$token) {
            throw new \Exception("Токен не найден.", 401);
        }

        JWT::$leeway = $this->leeway;

        try {
            $decoded = JWT::decode($token, new Key($this->key, 'HS256'));
            $permissions = explode(',', $decoded->per);
            $decoded->per = array_map(fn($permission) => (int)$permission, $permissions);
        } catch (InvalidArgumentException $e) {
            throw new \Exception("Ключ отсутствует, или имеет неверный формат.", 401);
        } catch (DomainException $e) {
            throw new \Exception("Алгоритм не поддерживается, или ключ недействителен.", 401);
        } catch (SignatureInvalidException $e) {
            throw new \Exception("Проверка подписи не удалась", 401);
        } catch (BeforeValidException $e) {
            throw new \Exception("Токен ещё не начал действовать.", 401);
        } catch (ExpiredException $e) {
            throw new \Exception("Токен просрочен.", 403);
        } catch (UnexpectedValueException $e) {
            throw new \Exception("Неверный формат или несоответствующий алгоритм.", 401);
        } catch (\Throwable $th) {
            throw new \Exception("Неизвестная ошибка.", 401);
        }

        return $decoded;
    }

    /**
     * Проверка access токена
     * 
     * @param string|null $token
     * 
     * @return stdClass    decoded jwt-payload
     * 
     * @throws \Exception    401 = не access токен
     */
    public function checkAccess(?string $token): stdClass
    {
        $checked = $this->check($token);

        if ($checked->typ !== 'AT') {
            throw new \Exception("Токен не является типом access.", 401);
        }

        return $checked;
    }

    /**
     * Проверка refresh токена
     * 
     * @param string|null $token
     * 
     * @return stdClass    decoded jwt-payload
     * 
     * @throws \Exception    403 = не refresh, либо не найден в белом списке БД
     */
    public function checkRefresh(?string $token): stdClass
    {
        $checked = $this->check($token);

        if ($checked->typ !== 'RT') {
            throw new \Exception("Токен не является типом refresh.", 401);
        }

        if (Token::where('token', $token)->doesntExist()) {
            throw new \Exception("Refresh-токен недействителен.", 403);
        }

        return $checked;
    }

    /**
     * Обновление пары токенов
     * 
     * @param string|null $token
     * 
     * @return array ['decoded', 'tokens']
     * 
     * @throws \Exception    в вызываемых методах
     */
    public function refresh(?string $token): array
    {
        $checked = $this->checkRefresh($token);

        return ['decoded' => $checked, 'tokens' => $this->create($checked->sub, $checked->per)];
    }

    /**
     * Удаление refresh токена из БД
     * 
     * @param string|null $token
     * 
     * @return void
     * 
     * @throws \Exception    403 = не refresh, либо не найден в белом списке БД
     */
    public function destroy(?string $token): void
    {
        $checked = $this->check($token);

        if ($checked->typ !== 'RT') {
            throw new \Exception("Токен не является типом refresh.", 403);
        }

        $deleted = Token::where('token', $token)->delete();

        if (!$deleted) {
            throw new \Exception("Токен не найден в базе.", 403);
        }
    }
}
