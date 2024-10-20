<?php

namespace App\Services;

use App\Http\Resources\Product\CompareResource;
use App\Http\Resources\Product\IndexResource;
use App\Http\Resources\Product\SingleResource;
use App\Models\Product;
use App\Services\Cache\CacheService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductService
{
    public function __construct(protected CacheService $cacheService) {}

    public function isRequestWithoutFilters(array $requestQuery): bool
    {
        if (empty($requestQuery)) return true;

        $filtersCount = count($requestQuery);
        $hasValidPage = (isset($requestQuery['page']) && is_numeric($requestQuery['page']));
        $hasDefaultPerPage = (isset($requestQuery['perpage']) && $requestQuery['perpage'] === config('app.products_per_page_default'));

        if ($filtersCount === 1 && $hasValidPage) return true;
        if ($filtersCount === 1 && $hasDefaultPerPage) return true;
        if ($filtersCount === 2 && $hasValidPage && $hasDefaultPerPage) return true;

        return false;
    }

    public function getCachedPage(int $page): array
    {
        return $this->cacheService->cacheAndGet("productlist_page:$page", fn() => $this->getProducts($page));
    }

    public function cacheProductListPages(int $firstPage, int $lastPage): int
    {
        $count = 0;

        for ($page = $firstPage; $page <= $lastPage; $page++) {
            $count += (int)$this->cacheService->cacheOnEveryCall("productlist_page:$page", fn() => $this->getProducts($page));
        }

        return $count;
    }

    protected function getProducts(int $page): array
    {
        $products = Product::with('values')->paginate(config('app.products_per_page_default'), ['*'], 'page', $page);

        return  [
            'data' => IndexResource::collection($products),
            'meta' => $this->getMeta($products),
        ];
    }

    public function getMeta(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'total' => $paginator->total(),
        ];
    }

    public function getById(int $productId): array
    {
        $product = $this->cacheService->cacheAndGet("product:$productId", fn() => $this->getProductById($productId));

        if (!empty($product['slug'])) {
            $this->cacheService->cacheOnce("product_id:{$product['slug']}", fn() => $productId);
        }

        return $product;
    }

    public function cacheSingleProductPages(int $firstId, int $lastId): int
    {
        $count = 0;

        for ($id = $firstId; $id <= $lastId; $id++) {
            $count += (int)$this->cacheById($id);
        }

        return $count;
    }

    public function cacheById(int $productId): bool
    {
        $product = $this->getProductById($productId);
        $productIsCached = $this->cacheService->cacheOnEveryCall("product:$productId", fn() => $product);
        $slugIsCached = $this->cacheService->cacheOnEveryCall("product_id:{$product['slug']}", fn() => $productId);

        return $productIsCached && $slugIsCached;
    }

    protected function getProductById($productId): array
    {
        $product = Product::with(['values.attribute'])->where('products.id', $productId)->firstOrFail();
        return (new SingleResource($product))->getArray();
    }

    public function getBySlug(string $slug): array
    {
        $productId = $this->cacheService->cacheAndGet("product_id:$slug", fn() => Product::where('slug', $slug)->valueOrFail('id'));

        return $this->getById($productId);
    }

    public function reCacheProduct(Product $product): void
    {
        $this->cacheService->cacheOnEveryCall("product:{$product->id}", fn() => new SingleResource($product));
        $this->cacheService->cacheOnEveryCall("product_id:{$product->slug}", fn() => $product->id);
    }

    public function clearProductCache(): int
    {
        return $this->cacheService->delCollections(['product', 'product_id', 'productlist_page']);
    }

    public function getProductsToCompare(string $query): Collection
    {
        $productsIDs = explode(',', $query);

        return Product::with(['values.attribute'])->whereIn('id', $productsIDs)->limit(10)->get();
    }

    public function getProductsToCart(string $query): Collection
    {
        $productsIDs = explode(',', $query);

        return Product::whereIn('id', $productsIDs)->limit(100)->get();
    }

    public function getFormattedData($products)
    {
        $productsCollection = CompareResource::collection($products)->jsonSerialize();
        $allAttributes = $this->getAllAttributes($productsCollection);
        $preparedProducts = $this->prepareProducts($productsCollection, $allAttributes);

        return [
            'data' => $preparedProducts,
            'attributes' => $allAttributes,
        ];
    }

    private function getAllAttributes(array $products): array
    {
        $allAttributes = [];

        foreach ($products as $product) {
            foreach ($product['attributes'] as $key => $value) {
                $allAttributes[$key] = $product['attributes'][$key]['name'];
            }
        }

        return $allAttributes;
    }

    private function prepareProducts(array $products, array $attributes): array
    {
        foreach ($products as &$product) {
            foreach ($attributes as $key => $value) {
                if (isset($product['attributes'][$key])) {
                    $product['attributes'][$key] = $product['attributes'][$key]['value'];
                }
            }
        }

        return $products;
    }
}
