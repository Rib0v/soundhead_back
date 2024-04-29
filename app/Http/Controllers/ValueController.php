<?php

namespace App\Http\Controllers;

use App\Models\Value;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ValueController extends Controller
{
    /**
     * Контроллер понадобится в админке
     * для управления характеристиками товаров
     */

    public function index()
    {
        //
    }

    public function store(Request $request)
    {
        if (Gate::denies('content-manager', [$request->bearerToken()])) {
            abort(404, 'Not found');
        }
        //
    }

    public function update(Request $request, Value $value)
    {
        if (Gate::denies('content-manager', [$request->bearerToken()])) {
            abort(404, 'Not found');
        }
        //
    }

    public function destroy(Request $request, Value $value)
    {
        if (Gate::denies('content-manager', [$request->bearerToken()])) {
            abort(404, 'Not found');
        }
        //
    }
}
