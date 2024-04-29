<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use App\Services\AttributeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AttributeController extends Controller
{
    /**
     * @OA\Get(
     *   tags={"Attribute"},
     *   path="/api/attributes",
     *   summary="INDEX - Список всех доступных характеристик товаров",
     *   @OA\Response(response=200, description="OK",
     *     @OA\JsonContent(
     *       type="array",
     *         @OA\Items(
     *           @OA\Property(property="id", type="integer", example=1),
     *           @OA\Property(property="name", type="string", example="Марка"),
     *           @OA\Property(property="slug", type="string", example="brand"),
     *           @OA\Property(property="vals", type="array", 
     *             @OA\Items(
     *               @OA\Property(property="id", type="integer", example=1),
     *               @OA\Property(property="name", type="string", example="Marshall"),
     *             )
     *           ),
     *         )
     *       ),
     *     )
     *   )
     * )
     */
    public function index(AttributeService $service)
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
