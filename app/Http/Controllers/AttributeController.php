<?php

namespace App\Http\Controllers;

use App\Services\AttributeService;
use Illuminate\Http\Response;

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
}
