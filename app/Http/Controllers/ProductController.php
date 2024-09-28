<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\UpdateRequest;
use App\Http\Resources\Product\CartResource;
use App\Http\Resources\Product\IndexResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class ProductController extends Controller
{
    /**
     * Массив товаров
     * 
     * @param Request $request
     * @param ProductService $service
     * 
     * @return Response
     */
    public function index(Request $request, ProductService $service): Response
    {
        if ($service->isRequestWithoutFilters($request)) {
            return response($service->getCachedPage($request));
        }

        $perPage = $request->query('perpage', config('app.products_per_page_default'));

        $products = Product::query()
            ->filterOptions($request)
            ->filterRanges($request)
            ->sort($request)
            ->paginate($perPage);

        $data = IndexResource::collection($products)->toArray($request);
        $meta = $service->getMeta($products);

        return response(compact('data', 'meta'));
    }

    /**
     * Информация о товаре
     * 
     * Если передано число - ищет по id,
     * если строка - по slug
     * 
     * @param int|string $identifier
     * @param ProductService $service
     * 
     * @return Response
     */
    public function show(int|string $identifier, ProductService $service): Response
    {
        try {
            $id = +$identifier;
            return response(['data' => $service->getById($id)]);
        } catch (\Throwable $th) {
            return response(['data' => $service->getBySlug($identifier)]);
        }
    }

    /**
     * Сравнение товаров
     * 
     * @param Request $request
     * @param ProductService $service
     * 
     * @return Response
     */
    public function compare(Request $request, ProductService $service): Response
    {
        if (!$request->has('product')) {
            return response(['message' => 'Products not found'], 404);
        }

        $products = $service->getProductsToCompare($request->query('product'));

        $resp = $service->getFormattedData($products, $request);
        return response($resp);
    }

    /**
     * Актуальные цены и ссылки на фото
     * для списка товаров из корзины пользователя
     * 
     * @param Request $request
     * @param ProductService $service
     * 
     * @return Response
     */
    public function cart(Request $request, ProductService $service): Response
    {
        if (!$request->has('product')) {
            return response(['message' => 'Products not found'], 404);
        }

        $products = $service->getProductsToCart($request->query('product'));

        return CartResource::collection($products);
    }

    /**
     * Создание товара
     * 
     * @param UpdateRequest $request
     * @param ProductService $service
     * 
     * @return Response
     */
    public function store(UpdateRequest $request, ProductService $service): Response
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
     * Изменение товара
     * 
     * @param UpdateRequest $request
     * @param Product $product
     * @param ProductService $service
     * 
     * @return Response
     */
    public function update(UpdateRequest $request, Product $product, ProductService $service): Response
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
     * Удаление товара
     * 
     * @param Product $product
     * @param Request $request
     * 
     * @return Response
     */
    public function destroy(Product $product, Request $request): Response
    {
        if (Gate::denies('content-manager', [$request->bearerToken()])) {
            abort(404, 'Not found');
        }

        $product->delete();

        return response(['message' => 'Товар удалён.', 'product' => $product]);
    }

    /**
     * Поиск товаров по ключевому слову
     * 
     * @param string $query
     * @param Request $request
     * 
     * @return Response
     */
    public function search(string $query, Request $request): Response
    {
        $paginate = $request->query('paginate', '20');

        // Для сохранения совместимости с SQLITE
        $caseInsensitiveOperator = config('database.default') === 'pgsql' ? 'ILIKE' : 'LIKE';

        $products = Product::where('name', $caseInsensitiveOperator, "%$query%")->paginate($paginate);

        return response(IndexResource::collection($products));
    }
}
