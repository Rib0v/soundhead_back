<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get(config('swagger.uri'), function () {
    return view('swagger');
});
