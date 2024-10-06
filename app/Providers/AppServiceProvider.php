<?php

namespace App\Providers;

use App\Services\Auth\CustomGate;
use App\Services\Auth\JWTAuthService;
use App\Services\Auth\JWTTokenGuard;
use App\Services\Auth\TokenRepositoryService;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
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

        $this->app->singleton(JWTAuthService::class, function () {
            return new JWTAuthService(config('jwt'), new TokenRepositoryService);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Auth::extend('jwt', function (Application $app, string $name, array $config) {
            return new JWTTokenGuard(Auth::createUserProvider($config['provider']), $app['request'], $app->make(JWTAuthService::class));
        });
    }
}
