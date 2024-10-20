<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Permission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $hasPermission = Auth::user()?->hasPermission($permission);

        /**
         * Почему 404
         * в целях безопасности
         * пользователю не стоит сообщать
         * о существовании маршрутов,
         * для доступа к которым у него нет прав
         */
        abort_if(!$hasPermission, 404);

        return $next($request);
    }
}
