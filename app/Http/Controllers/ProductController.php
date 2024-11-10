<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\CartRequest;
use App\Http\Requests\Product\CompareRequest;
use App\Http\Requests\Product\UpdateRequest;
use App\Http\Resources\Product\CartResource;
use App\Http\Resources\Product\IndexResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;

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
        if ($service->isRequestWithoutFilters($request->query())) {
            $page = (int)$request->query('page', 1);
            return response($service->getCachedPage($page));
        }

        $perPage = $request->query('perpage', config('app.products_per_page_default'));

        $products = Product::query()
            ->with('values')
            ->filterOptions($request)
            ->filterRanges($request)
            ->sort($request)
            ->paginate($perPage);

        return response([
            'data' => IndexResource::collection($products),
            'meta' => $service->getMeta($products),
        ]);
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
     * @param CompareRequest $request
     * @param ProductService $service
     * @return Response
     */
    public function compare(CompareRequest $request, ProductService $service): Response
    {
        $products = $service->getProductsToCompare($request->product);

        return response($service->getFormattedData($products));
    }

    /**
     * Актуальные цены и ссылки на фото
     * для списка товаров из корзины пользователя
     * 
     * @param CartRequest $request
     * @param ProductService $service
     * @return ResourceCollection
     */
    public function cart(CartRequest $request, ProductService $service): ResourceCollection
    {
        $products = $service->getProductsToCart($request->product);

        return CartResource::collection($products);
    }

    /**
     * Создание товара
     * 
     * @param UpdateRequest $request
     * @return Response
     */
    public function store(UpdateRequest $request): Response
    {
        return response([
            'message' => 'Товар создан.',
            'product' => Product::create($request->validated()),
        ]);
    }

    /**
     * Изменение товара
     * 
     * @param UpdateRequest $request
     * @param Product $product
     * @return Response
     */
    public function update(UpdateRequest $request, Product $product): Response
    {
        $product->update($request->validated());

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
     * @return ResourceCollection
     */
    public function search(string $query, Request $request): ResourceCollection
    {
        $paginate = $request->query('paginate', '20');
        $like = $this->getCaseInsensitiveLikeOperator();
        $products = Product::where('name', $like, "%$query%")->paginate($paginate);

        return IndexResource::collection($products);
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
