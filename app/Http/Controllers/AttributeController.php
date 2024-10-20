<?php

namespace App\Http\Controllers;

use App\Http\Resources\AttributeResource;
use App\Models\Attribute;
use Illuminate\Http\Response;

class AttributeController extends Controller
{
    /**
     * Список всех доступных характеристик товаров
     * 
     * @return Response
     */
    public function index(): Response
    {
        $attributes = Attribute::with('values')->get();
        return response(AttributeResource::collection($attributes));
    }
}
