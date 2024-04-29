<?php

use App\Http\Controllers\AttributeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;


Route::prefix('auth')->controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::get('/checkref', 'checkRefresh');
    Route::get('/checkacc', 'checkAccess');
    Route::get('/refresh', 'refresh');
    Route::get('/logout', 'logout');
});

Route::prefix('users')->middleware('jwt-auth')->controller(UserController::class)->group(function () {
    Route::get('/', 'index');
    Route::get('/{user}', 'show');
    Route::patch('/{id}/password', 'changePassword');
    Route::patch('/{id}/email', 'changeEmail');
    Route::patch('/{id}/address', 'changeAddress');
    Route::patch('/{id}/profile', 'changeProfile');
    Route::delete('/{id}', 'destroy');
});

Route::post('/users', [UserController::class, 'store']);



Route::prefix('products')->controller(ProductController::class)->group(function () {
    Route::get('/', 'index');
    Route::post('/', 'store')->middleware('jwt-auth');
    Route::patch('/{product}', 'update');
    Route::delete('/{product}', 'destroy');
    Route::get('/compare', 'compare');
    Route::get('/cart', 'cart');
    Route::get('/search/{query}', 'search');

    Route::get('/{identifier}', 'show'); // id либо slug
});


Route::prefix('orders')->middleware('jwt-auth')->controller(OrderController::class)->group(function () {
    Route::get('/', 'index');
    Route::get('/{order}', 'show');
    Route::delete('/{order}', 'destroy');
    Route::patch('/{order}/status', 'changeStatus');
});

Route::post('/orders', [OrderController::class, 'store']);

Route::get('/users/{id}/orders', [OrderController::class, 'showByUserId'])->middleware('jwt-auth');

Route::get('/attributes', [AttributeController::class, 'index']);
