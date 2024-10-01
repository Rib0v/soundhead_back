<?php

namespace App\Providers;

use App\Services\Auth\CustomGate;
use App\Services\Auth\JWTTokenGuard;
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
        $this->app->singleton(GateContract::class, function ($app) {
            return new CustomGate($app, fn() => $app['auth']->user());
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Auth::extend('jwt', function (Application $app, string $name, array $config) {
            return new JWTTokenGuard(Auth::createUserProvider($config['provider']), $app['request']);
        });
    }
}
