<?php

namespace Tests\Unit\Services\Auth;

use App\Contracts\TokenRepository;
use App\Exceptions\JWTValidationException;
use App\Services\Auth\JWTAuthService;
use Mockery;
use Tests\UnitTestCase;

class JWTAuthServiceTest extends UnitTestCase
{
    protected static JWTAuthService $jwt;
    protected static JWTAuthService $jwtWrong;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$jwt = new JWTAuthService(self::getConfig(), self::getMockedTokenRepository());
        self::$jwtWrong = new JWTAuthService(self::getConfig(), self::getMockedTokenRepository(wrong: true));
    }

    // ============================== CREATE ==============================

    public function test_create_method_returns_array(): void
    {
        $created = self::$jwt->create(userId: 1, permissions: [1, 2, 3]);
        $this->assertIsArray($created);
    }

    public function test_create_method_returns_valid_data(): void
    {
        $time = 1727984319;
        $config = (object)self::getConfig();

        $expected = [
            'access' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vZXhhbXBsZS5jb20iLCJzdWIiOjEsInBlciI6IjEsMiwzIiwiZXhwIjoxNzI3OTg3OTE5LCJ0eXAiOiJBVCJ9.EKJZkWVQirj1c4ErEvMkPuc-meLZE-Bj6PKkkE7k0x8',
            'refresh' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vZXhhbXBsZS5jb20iLCJzdWIiOjEsInBlciI6IjEsMiwzIiwiZXhwIjoxNzMwNTc2MzE5LCJ0eXAiOiJSVCJ9.Rmr1ahEtRlCzgIOHaUsmvIRmnYS6wX-Qy9jkaEXfJq4',
            'access_exp' => $time + 60 * $config->access_ttl,
            'refresh_minutes' => $config->refresh_ttl,
        ];

        $actual = self::$jwt->create(userId: 1, permissions: [1, 2, 3], time: $time);

        $this->assertSame($expected, $actual);
    }

    // ============================== DECODE ==============================

    public function test_decode_method_returns_valid_access_token(): void
    {
        $userId = 3;
        $permissions = [2, 4, 8];
        $time = time() + 1000;

        $encodedToken = self::$jwt->create($userId, $permissions, $time);

        $decodedToken = self::$jwt->decode($encodedToken['access']);

        $config = (object)self::getConfig();

        $expected = [
            'iss' =>  $config->issuer,
            'sub' => $userId,
            'per' => $permissions,
            'exp' => $time + 60 * $config->access_ttl,
            'typ' => 'AT',
        ];

        $this->assertSame($expected, (array)$decodedToken);
    }

    public function test_decode_method_returns_valid_refresh_token(): void
    {
        $userId = 3;
        $permissions = [2, 4, 8];
        $time = time() + 1000;

        $encodedToken = self::$jwt->create($userId, $permissions, $time);

        $decodedToken = self::$jwt->decode($encodedToken['refresh']);

        $config = (object)self::getConfig();

        $expected = [
            'iss' =>  $config->issuer,
            'sub' => $userId,
            'per' => $permissions,
            'exp' => $time + 60 * $config->refresh_ttl,
            'typ' => 'RT',
        ];

        $this->assertSame($expected, (array)$decodedToken);
    }

    public function test_decode_method_throw_exception_when_token_expired(): void
    {
        $config = (object)self::getConfig();
        $accessTtlInSeconds = $config->access_ttl * 60;
        $timeInPast = time() - $accessTtlInSeconds - $config->leeway - 1;

        $encodedToken = self::$jwt->create(userId: 1, time: $timeInPast);

        $this->expectException(JWTValidationException::class);
        $this->expectExceptionCode(403);
        $this->expectExceptionMessage('Токен просрочен.');

        self::$jwt->decode($encodedToken['access']);
    }

    public function test_decode_method_throw_exception_when_token_is_invalid(): void
    {
        $this->expectException(JWTValidationException::class);
        $this->expectExceptionCode(401);
        $this->expectExceptionMessage('Неверный формат или несоответствующий алгоритм.');

        self::$jwt->decode('abrakadabra');
    }

    public function test_decode_method_throw_exception_when_token_is_empty(): void
    {
        $this->expectException(JWTValidationException::class);
        $this->expectExceptionCode(401);
        $this->expectExceptionMessage('Токен не найден.');

        self::$jwt->decode('');
    }

    public function test_decode_method_throw_exception_when_token_is_null(): void
    {
        $this->expectException(JWTValidationException::class);
        $this->expectExceptionCode(401);
        $this->expectExceptionMessage('Токен не найден.');

        self::$jwt->decode(null);
    }

    // ============================== CHECK_ACCESS ==============================

    public function test_checkAccess_method_returns_decoded_access_token(): void
    {
        $encodedToken = self::$jwt->create(1);
        $decodedToken = self::$jwt->decode($encodedToken['access']);
        $checkedReturn = self::$jwt->checkAccess($encodedToken['access']);

        $this->assertSame((array)$decodedToken, (array)$checkedReturn);
    }

    public function test_checkAccess_method_throw_exception_when_refresh_token_provided(): void
    {
        $this->expectException(JWTValidationException::class);
        $this->expectExceptionCode(401);
        $this->expectExceptionMessage('Токен не является типом access.');

        $encodedToken = self::$jwt->create(1);
        self::$jwt->checkAccess($encodedToken['refresh']);
    }

    // ============================== CHECK_REFRESH ==============================

    public function test_checkRefresh_method_returns_decoded_refresh_token(): void
    {
        $encodedToken = self::$jwt->create(1);
        $decodedToken = self::$jwt->decode($encodedToken['refresh']);
        $checkedReturn = self::$jwt->checkRefresh($encodedToken['refresh']);

        $this->assertSame((array)$decodedToken, (array)$checkedReturn);
    }

    public function test_checkRefresh_method_throw_exception_when_access_token_provided(): void
    {
        $this->expectException(JWTValidationException::class);
        $this->expectExceptionCode(401);
        $this->expectExceptionMessage('Токен не является типом refresh.');

        $encodedToken = self::$jwt->create(1);
        self::$jwt->checkRefresh($encodedToken['access']);
    }

    public function test_checkRefresh_method_throw_exception_when_refresh_token_not_found_in_repository(): void
    {
        $this->expectException(JWTValidationException::class);
        $this->expectExceptionCode(403);
        $this->expectExceptionMessage('Refresh-токен недействителен.');

        $encodedToken = self::$jwt->create(1);
        self::$jwtWrong->checkRefresh($encodedToken['refresh']);
    }

    // ============================== REFRESH =============================

    public function test_refresh_method_returns_valid_data(): void
    {
        $userId = 1;
        $permissions = [];
        $time = time();

        $encodedToken = self::$jwt->create($userId, $permissions, $time);
        $refreshData = self::$jwt->refresh($encodedToken['refresh']);

        $this->assertArrayHasKey('decoded', $refreshData);
        $this->assertArrayHasKey('tokens', $refreshData);

        $config = (object) $this->getConfig();

        $expectedOldRefreshToken = [
            'iss' => $config->issuer,
            'sub' => $userId,
            'per' => $permissions,
            'exp' => $time + 60 * $config->refresh_ttl,
            'typ' => 'RT',
        ];

        $decodedOldRefreshToken = (array) $refreshData['decoded'];
        $this->assertSame($expectedOldRefreshToken, $decodedOldRefreshToken);

        $decodedNewAccessToken = self::$jwt->decode($refreshData['tokens']['access']);
        $this->assertEquals('AT', $decodedNewAccessToken->typ);

        $decodedNewRefreshToken = self::$jwt->decode($refreshData['tokens']['refresh']);
        $this->assertEquals('RT', $decodedNewRefreshToken->typ);

        $this->assertEquals($config->refresh_ttl, $refreshData['tokens']['refresh_minutes']);
    }

    // ============================== DESTROY ==============================

    public function test_destroy_method_is_ok_when_refresh_token_provided(): void
    {
        $encodedToken = self::$jwt->create(1);
        self::$jwt->destroy($encodedToken['refresh']);
        $this->assertTrue(true);
    }

    public function test_destroy_method_throw_exception_when_access_token_provided(): void
    {
        $this->expectException(JWTValidationException::class);
        $this->expectExceptionCode(403);
        $this->expectExceptionMessage('Токен не является типом refresh.');

        $encodedToken = self::$jwt->create(1);
        self::$jwt->destroy($encodedToken['access']);
    }

    public function test_destroy_method_throw_exception_when_deleting_token_is_fail(): void
    {
        $this->expectException(JWTValidationException::class);
        $this->expectExceptionCode(403);
        $this->expectExceptionMessage('Токен не найден в базе.');

        $encodedToken = self::$jwt->create(1);
        self::$jwtWrong->destroy($encodedToken['refresh']);
    }

    // ============================== HELPERS ==============================

    protected static function getConfig(): array
    {
        return [

            'issuer' => 'http://example.com',
            'key' => 'example_key',
            'access_ttl' => 60, // in minutes
            'refresh_ttl' => 43200, // in minutes
            'leeway' => 60, // in seconds

        ];
    }

    protected static function getMockedTokenRepository($wrong = false)
    {
        /** @var TokenRepository|Mockery\MockInterface */
        $tokenRepositoryMock = Mockery::mock(TokenRepository::class);

        $tokenRepositoryMock->shouldReceive('saveRefreshToken')->andReturn(!$wrong);

        $tokenRepositoryMock->shouldReceive('isRefreshTokenExists')->andReturn(!$wrong);
        $tokenRepositoryMock->shouldReceive('removeRefreshToken')->andReturn(!$wrong);

        return $tokenRepositoryMock;
    }
}
