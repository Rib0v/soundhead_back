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
        if (config('cache.enabled') && $service->isRequestWithoutFilters($request)) {
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
     * Информация о товаре.
     * Если передано число - ищет по id,
     * если строка - по slug
     * 
     * @param int|string $identifier
     * @param ProductService $service
     * @return Response
     */
    public function show(int|string $identifier, ProductService $service): Response
    {
        $product = is_numeric($identifier)
            ? $service->getById($identifier)
            : $service->getBySlug($identifier);

        return response(['data' => $product]);
    }

    /**
     * Сравнение товаров
     * 
     * @param Request $request
     * @param ProductService $service
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
     * @return Response
     */
    public function cart(Request $request, ProductService $service): Response
    {
        if (!$request->has('product')) {
            return response(['message' => 'Products not found'], 404);
        }

        $products = $service->getProductsToCart($request->query('product'));

        return response(CartResource::collection($products));
    }

    /**
     * Создание товара
     * 
     * @param UpdateRequest $request
     * @param ProductService $service
     * @return Response
     */
    public function store(UpdateRequest $request, ProductService $service): Response
    {
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
     * @return Response
     */
    public function update(UpdateRequest $request, Product $product, ProductService $service): Response
    {
        $validated = $request->validated();

        Product::where('id', $product->id)->update($validated);
        $product->refresh();

        $service->cacheProduct($product->id);

        return response(['message' => 'Товар обновлён.', 'product' => $product]);
    }

    /**
     * Удаление товара
     * 
     * @param Product $product
     * @return Response
     */
    public function destroy(Product $product): Response
    {
        $product->delete();

        return response(['message' => 'Товар удалён.', 'product' => $product]);
    }

    /**
     * Поиск товаров по ключевому слову
     * 
     * @param string $query
     * @param Request $request
     * @return Response
     */
    public function search(string $query, Request $request): Response
    {
        $paginate = $request->query('paginate', '20');

        $like = $this->getCaseInsensitiveLikeOperator();

        $products = Product::where('name', $like, "%$query%")->paginate($paginate);

        return response(IndexResource::collection($products));
    }


    /**
     * Возвращает подходящий регистронезависимый
     * оператор like в зависимости от выбранной БД.
     * Сделано для возможности быстро переключаться
     * между PostgreSQL и SQLite
     * 
     * @return string
     */
    private function getCaseInsensitiveLikeOperator(): string
    {
        return (config('database.default') === 'pgsql') ? 'ILIKE' : 'LIKE';
    }
}
