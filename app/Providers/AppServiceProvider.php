<?php

namespace App\Providers;

use App\Services\PermissionService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        // $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        // ServiceProvider::addProviderToBootstrapFile(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        // ServiceProvider::addProviderToBootstrapFile(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        Gate::define('content-manager', function (null $user, ?string $token): bool {

            return PermissionService::checkEditContentPermission($token);
        });

        Gate::define('order-manager', function (null $user, ?string $token): bool {

            return PermissionService::checkEditOrdersPermission($token);
        });

        Gate::define('admin', function (null $user, ?string $token): bool {

            return PermissionService::checkEditUsersPermission($token);
        });
    }
}
