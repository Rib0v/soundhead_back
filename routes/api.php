<?php

use App\Http\Controllers\AttributeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Models\Order;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::get('/checkref', 'checkRefresh');
    Route::get('/checkacc', 'checkAccess');
    Route::get('/refresh', 'refresh');
    Route::get('/logout', 'logout');
    Route::get('/test', 'test')->middleware('auth');
});

Route::post('/users', [UserController::class, 'store']);
Route::prefix('users')->middleware('auth')->controller(UserController::class)->group(function () {
    Route::get('/', 'index')->middleware('permission:edit_users');
    Route::get('/{user}', 'show')->can('show', 'user');
    Route::patch('/{user}/password', 'changePassword')->can('changeProfile', 'user');
    Route::patch('/{user}/email', 'changeEmail')->can('changeProfile', 'user');
    Route::patch('/{user}/address', 'changeAddress')->can('changeProfile', 'user');
    Route::patch('/{user}/profile', 'changeProfile')->can('changeProfile', 'user');
    Route::delete('/{user}', 'destroy')->middleware('permission:edit_users');
});

Route::prefix('products')->controller(ProductController::class)->group(function () {
    Route::get('/', 'index');
    Route::post('/', 'store')->middleware('permission:edit_content');
    Route::patch('/{product}', 'update')->middleware('permission:edit_content');
    Route::delete('/{product}', 'destroy')->middleware('permission:edit_content');
    Route::get('/compare', 'compare');
    Route::get('/cart', 'cart');
    Route::get('/search/{query}', 'search');
    Route::get('/{identifier}', 'show'); // id либо slug
});

Route::post('/orders', [OrderController::class, 'store']);
Route::prefix('orders')->controller(OrderController::class)->group(function () {
    Route::get('/', 'index')->middleware('auth')->middleware('permission:edit_orders');
    Route::get('/{order}', 'show')->can('show', 'order');
    Route::delete('/{order}', 'destroy')->middleware('permission:edit_users'); // удалять заказы может только тот, кто удаляет пользователей
    Route::patch('/{order}/status', 'changeStatus')->middleware('permission:edit_orders');
});
Route::get('/users/{user}/orders', [OrderController::class, 'showByUserId'])->can('showByUserId', Order::class);

Route::get('/attributes', [AttributeController::class, 'index']);
