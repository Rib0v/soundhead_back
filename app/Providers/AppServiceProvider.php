<?php

namespace App\Providers;

use App\Services\Auth\CustomGate;
use App\Services\Auth\JWTAuthDTO;
use App\Services\Auth\JWTAuthService;
use App\Services\Auth\JWTTokenGuard;
use App\Services\Auth\EloquentTokenRepository;
use App\Services\Cache\CacheService;
use App\Services\Cache\RedisRepository;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(GateContract::class, function (Application $app) {
            return new CustomGate($app, fn() => $app['auth']->user());
        });

        $this->app->singleton(CacheService::class, function (Application $app) {
            return new CacheService(
                cache: new RedisRepository(
                    databasePrefix: config('database.redis.options.prefix')
                ),
                enableCache: config('cache.enabled')
            );
        });

        $this->app->singleton(JWTAuthService::class, function () {
            $config = (object)config('jwt');
            $dto = new JWTAuthDTO(
                issuer: $config->issuer,
                key: $config->key,
                access_ttl: $config->access_ttl,
                refresh_ttl: $config->refresh_ttl,
                leeway: $config->leeway,
            );
            return new JWTAuthService($dto, new EloquentTokenRepository);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('database.log')) {
            $this->logDatabaseQueries();
        }

        Auth::extend('jwt', function (Application $app, string $name, array $config) {
            return new JWTTokenGuard(Auth::createUserProvider($config['provider']), $app['request'], $app->make(JWTAuthService::class));
        });
    }

    protected function logDatabaseQueries(): void
    {
        logger('============================== NEW BOOT ==============================');

        DB::listen(function (QueryExecuted $query) {
            $sqlWithBindings = vsprintf(str_replace('?', "'%s'", $query->sql), $query->bindings);
            logger('SQL', ['time' => $query->time, 'query' => $sqlWithBindings]);
        });
    }
}
