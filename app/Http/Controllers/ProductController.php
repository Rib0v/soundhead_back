<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\UpdateRequest;
use App\Http\Resources\Product\CartResource;
use App\Http\Resources\Product\IndexResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProductController extends Controller
{
    /**
     * Это документация для swagger, если что :)
     * 
     * @OA\Get(
     *   tags={"Product"},
     *   path="/api/products",
     *   summary="INDEX - Массив товаров",
     *   description="Список фильтров динамический, берётся из БД. Приведены только некоторые параметры.",
     *   @OA\Parameter(name="brand", in="query", description="Пример: '1,2,3'",
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(name="minprice", in="query", description="Пример: 1000",
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Parameter(name="maxprice", in="query", description="Пример: 10000",
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Parameter(name="sort", in="query", description="Значения: lowprice/hiprice/older/newer",
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(response=200, description="OK",
     *     @OA\JsonContent(
     *       @OA\Property(property="data", type="array",
     *         @OA\Items(
     *           @OA\Property(property="id", type="integer", example="1"),
     *           @OA\Property(property="name", type="string", example="Focal molestiae"),
     *           @OA\Property(property="slug", type="string", example="focal-molestiae"),
     *           @OA\Property(property="price", type="integer", example="22900"),
     *           @OA\Property(property="image", type="string", example="/overhead/wired/0.jpg"),
     *           @OA\Property(property="description", type="string", example="Expedita eos earum eaque culpa iure quae."),
     *         ),
     *       ),
     *       @OA\Property(property="meta", type="object",
     *         @OA\Property(property="current_page", type="integer", example="1"),
     *         @OA\Property(property="last_page", type="integer", example="5"),
     *         @OA\Property(property="total", type="integer", example="100"),
     *       )
     *     )
     *   )
     * )
     */
    public function index(Request $request, ProductService $service)
    {
        if ($service->isRequestWithoutFilters($request)) {
            return $service->getCachedPage($request);
        }

        $perPage = $request->query('perpage', config('app.products_per_page_default'));

        $products = Product::query()
            ->filterOptions($request)
            ->filterRanges($request)
            ->sort($request)
            ->paginate($perPage);

        $data = IndexResource::collection($products)->toArray($request);
        $meta = $service->getMeta($products);

        return compact('data', 'meta');
    }

    /**
     * @OA\Get(
     *   tags={"Product"},
     *   path="/api/products/{identifier}",
     *   summary="SHOW - Информация о товаре",
     *   @OA\Parameter(name="identifier", in="path", required=true, description="Можно искать как по id, так и по slug",
     *     @OA\Schema(
     *       anyOf={
     *         @OA\Schema(type="integer"),
     *         @OA\Schema(type="string"),
     *       }
     *     )
     *   ),
     *   @OA\Response(response=200, description="OK",
     *     @OA\JsonContent(
     *       @OA\Property(property="data", type="object",
     *          @OA\Property(property="id", type="integer", example="1"),
     *          @OA\Property(property="name", type="string", example="Focal molestiae"),
     *          @OA\Property(property="slug", type="string", example="focal-molestiae"),
     *          @OA\Property(property="price", type="integer", example="22900"),
     *          @OA\Property(property="image", type="string", example="/overhead/wired/0.jpg"),
     *          @OA\Property(property="description", type="string", example="Expedita eos earum eaque culpa iure quae."),
     *          @OA\Property(property="attributes", type="arr", example="[{...}, {...}, ...]"),
     *          @OA\Property(property="photos", type="arr", example="['url', 'url', ...]"),
     *       )
     *     )
     *   ),
     *   @OA\Response(response=404, description="Not Found")
     * )
     */
    public function show(int|string $identifier, ProductService $service)
    {
        /**
         * Если передано число - ищет по id,
         * если строка - по slug
         */
        try {
            $id = +$identifier;
            return response(['data' => $service->getById($id)]);
        } catch (\Throwable $th) {
            return response(['data' => $service->getBySlug($identifier)]);
        }
    }

    /**
     * @OA\Get(
     *   tags={"Product"},
     *   path="/api/products/compare",
     *   summary="compare - Сравнение товаров",
     *   @OA\Parameter(name="product", in="query", required=true,
     *     @OA\Schema(type="string", example="1,2,3")
     *   ),
     *   @OA\Response(response=200, description="OK",
     *     @OA\JsonContent(
     *       @OA\Property(property="data", type="array",
     *         @OA\Items(
     *           @OA\Property(property="id", type="integer", example="1"),
     *           @OA\Property(property="name", type="string", example="Focal molestiae"),
     *           @OA\Property(property="slug", type="string", example="focal-molestiae"),
     *           @OA\Property(property="price", type="integer", example="22900"),
     *           @OA\Property(property="image", type="string", example="/overhead/wired/0.jpg"),
     *           @OA\Property(property="description", type="string", example="Expedita eos earum eaque culpa iure quae."),
     *           @OA\Property(property="attributes", type="obj", example="{...}"),
     *         )
     *       ),
     *       @OA\Property(property="attributes", type="obj", example="{...}"),    
     *     )
     *   ),
     *   @OA\Response(response=404, description="Not Found")
     * )
     */
    public function compare(Request $request, ProductService $service)
    {
        if (!$request->has('product')) {
            return response(['message' => 'Products not found'], 404);
        }

        $products = $service->getProductsToCompare($request->query('product'));

        $resp = $service->getFormattedData($products, $request);
        return response($resp);
    }

    /**
     * @OA\Get(
     *   tags={"Product"},
     *   path="/api/products/cart",
     *   summary="cart - Актуальные цены и ссылки на фото для списка товаров из корзины пользователя",
     *   @OA\Parameter(name="product", in="query", required=true,
     *     @OA\Schema(type="string", example="1,2,3")
     *   ),
     *   @OA\Response(response=200, description="OK",
     *     @OA\JsonContent(
     *       @OA\Property(property="data", type="array",
     *         @OA\Items(
     *           @OA\Property(property="id", type="integer", example="1"),
     *           @OA\Property(property="name", type="string", example="Focal molestiae"),
     *           @OA\Property(property="slug", type="string", example="focal-molestiae"),
     *           @OA\Property(property="price", type="integer", example="22900"),
     *           @OA\Property(property="image", type="string", example="/overhead/wired/0.jpg"),
     *         )
     *       )
     *     )
     *   ),
     *   @OA\Response(response=404, description="Not Found")
     * )
     */
    public function cart(Request $request, ProductService $service)
    {
        if (!$request->has('product')) {
            return response(['message' => 'Products not found'], 404);
        }

        $products = $service->getProductsToCart($request->query('product'));

        return CartResource::collection($products);
    }

    /**
     * @OA\Post(
     *   tags={"Product"},
     *   path="/api/products",
     *   summary="STORE - Создание товара",
     *   security={{ "jwt": {} }},
     *   @OA\RequestBody(required=true,
     *     @OA\JsonContent(type="object",
     *       @OA\Property(property="name", type="string", example="Тестовый товар"),
     *       @OA\Property(property="slug", type="string", example="testoviy-tovar"),
     *       @OA\Property(property="price", type="integer", example=100500),
     *       @OA\Property(property="description", type="string", example="Тестовое описание"),
     *       @OA\Property(property="min_frequency", type="integer", example=50),
     *       @OA\Property(property="max_frequency", type="integer", example=19),
     *       @OA\Property(property="sensitivity", type="integer", example=30),
     *       @OA\Property(property="image", type="string", example="test.jpg"),
     *     )
     *   ),
     *   @OA\Response(response=201, description="OK",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Товар создан."),
     *       @OA\Property(property="product", type="obj", example="{ id, всё что мы отправили }"),
     *     )
     *   ),
     *   @OA\Response(response=404, description="Нет прав = не знаешь о существовании страницы"),
     *   @OA\Response(response=422, description="Валидация не пройдена"),
     * )
     */
    public function store(UpdateRequest $request, ProductService $service)
    {
        if (Gate::denies('content-manager', [$request->bearerToken()])) {
            abort(404, 'Not found');
        }

        $validated = $request->validated();
        $createdProduct = Product::create($validated);

        $service->cacheProduct($createdProduct->id);

        return response(['message' => 'Товар создан.', 'product' => $createdProduct]);
    }

    /**
     * @OA\Patch(
     *   tags={"Product"},
     *   path="/api/products/{id}",
     *   summary="UPDATE - Изменение товара",
     *   description="Это Patch, а не Put, так что можно отправлять на обновление отдельные свойства.",
     *   security={{ "jwt": {} }},
     *   @OA\Parameter(name="id", in="path", required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="name", type="string", example="Тестовый товар ред"),
     *       @OA\Property(property="slug", type="string", example="testoviy-tovar-red"),
     *       @OA\Property(property="price", type="integer", example=100600),
     *       @OA\Property(property="description", type="string", example="Тестовое описание ред"),
     *       @OA\Property(property="min_frequency", type="integer", example=60),
     *       @OA\Property(property="max_frequency", type="integer", example=29),
     *       @OA\Property(property="sensitivity", type="integer", example=40),
     *       @OA\Property(property="image", type="string", example="test-edited.jpg"),
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Товар обновлён."),
     *       @OA\Property(property="product", type="obj", example="{ всё что мы отправили }"),
     *     )
     *   ),
     *   @OA\Response(response=400, description="Отправлен запрос без параметров"),
     *   @OA\Response(response=404, description="Нет прав = не знаешь о существовании страницы"),
     *   @OA\Response(response=422, description="Валидация не пройдена"),
     * )
     */
    public function update(UpdateRequest $request, Product $product, ProductService $service)
    {
        if (Gate::denies('content-manager', [$request->bearerToken()])) {
            abort(404, 'Not found');
        }

        $validated = $request->validated();

        if (empty($validated)) {
            return response(['message' => 'Не предоставлено данных для обновления'], 400);
        }

        Product::where('id', $product->id)->update($validated);
        $product->refresh();

        $service->cacheProduct($product->id);

        return response(['message' => 'Товар обновлён.', 'product' => $product]);
    }

    /**
     * @OA\Delete(
     *   tags={"Product"},
     *   path="/api/products/{product}",
     *   summary="DESTROY - Удаление товара",
     *   security={{ "jwt": {} }},
     *   @OA\Parameter(name="product", in="path", required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(response=200, description="OK",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Товар удалён."),
     *       @OA\Property(property="product", type="obj", example="{ товар }"),
     *     )
     *   ),
     *   @OA\Response(response=404, description="Нет прав, либо попытка удалить несуществующий товар")
     * )
     */
    public function destroy(Product $product, Request $request)
    {
        if (Gate::denies('content-manager', [$request->bearerToken()])) {
            abort(404, 'Not found');
        }

        $product->delete();

        return response(['message' => 'Товар удалён.', 'product' => $product]);
    }

    /**
     * @OA\Get(
     *   tags={"Product"},
     *   path="/api/products/search/{query}",
     *   summary="search - Поиск товаров по ключевому слову",
     *   @OA\Parameter(name="query", in="path", required=true,
     *     @OA\Schema(type="string", example="sony")
     *   ),
     *   @OA\Parameter(name="paginate", in="query", required=false,
     *     @OA\Schema(type="integer", example="5")
     *   ),
     *   @OA\Response(response=200, description="OK",
     *     @OA\JsonContent(
     *       @OA\Property(property="data", type="array",
     *         @OA\Items(
     *           @OA\Property(property="id", type="integer", example="1"),
     *           @OA\Property(property="name", type="string", example="Focal molestiae"),
     *           @OA\Property(property="slug", type="string", example="focal-molestiae"),
     *           @OA\Property(property="price", type="integer", example="22900"),
     *           @OA\Property(property="image", type="string", example="/overhead/wired/0.jpg"),
     *           @OA\Property(property="description", type="string", example="Expedita eos earum eaque culpa iure quae."),
     *         )
     *       ),
     *       @OA\Property(property="links", type="obj", example="{...}"),
     *       @OA\Property(property="meta", type="obj", example="{...}")
     *     )
     *   ),
     *   @OA\Response(response=404, description="Not Found")
     * )
     */
    public function search(string $query, Request $request)
    {
        $paginate = $request->query('paginate', '20');
        $products = Product::where('name', 'LIKE', "%$query%")->paginate($paginate);

        return IndexResource::collection($products);
    }
}
