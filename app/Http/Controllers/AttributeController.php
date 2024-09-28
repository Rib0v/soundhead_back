<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use App\Services\AttributeService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class AttributeController extends Controller
{
    /**
     * Список всех доступных характеристик товаров
     * 
     * @param AttributeService $service
     * 
     * @return Response
     */
    public function index(AttributeService $service): Response
    {
        $filters = $service->getFilters();

        $formattedFilters = $service->formatFilters($filters);

        return response($formattedFilters);
    }

    /**
     * Эти методы понядобятся в админке
     * для редактирования списка характеристик,
     * доступных для фильтрации в каталоге
     */
    public function store(Request $request)
    {
        if (Gate::denies('content-manager', [$request->bearerToken()])) {
            abort(404, 'Not found');
        }
        //
    }

    public function update(Request $request, Attribute $attribute)
    {
        if (Gate::denies('content-manager', [$request->bearerToken()])) {
            abort(404, 'Not found');
        }
        //
    }

    public function destroy(Request $request, Attribute $attribute)
    {
        if (Gate::denies('content-manager', [$request->bearerToken()])) {
            abort(404, 'Not found');
        }
        //
    }
}
