<?php

namespace App\Services;

use App\Http\Resources\Product\CompareResource;
use App\Http\Resources\Product\IndexResource;
use App\Http\Resources\Product\SingleResource;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Redis;

class ProductService
{
    public function isRequestWithoutFilters(Request $request): bool
    {
        if (!count($request->query())) return true;

        if (count($request->query()) === 1 && (int)$request->query('page')) return true;

        if (
            count($request->query()) === 1
            && $request->query('perpage') === config('app.products_per_page_default')
        ) return true;

        if (
            count($request->query()) === 2
            && (int)$request->query('page')
            && $request->query('perpage') === config('app.products_per_page_default')
        ) return true;

        return false;
    }

    public function getCachedPage(Request $request): array
    {
        $page = (int)$request->query('page', 1);

        if (!Redis::exists("productlist_page:$page")) {
            $products = Product::paginate(config('app.products_per_page_default'), ['*'], 'page', $page);
            $data = IndexResource::collection($products);
            $meta = $this->getMeta($products);
            Redis::set("productlist_page:$page", compact('data', 'meta'));
        }

        return Redis::get("productlist_page:$page");
    }

    public function getFirstPage(): array
    {
        if (!Redis::exists('products_first_page')) {
            $products = Product::paginate(config('app.products_per_page_default'));
            $data = IndexResource::collection($products);
            $meta = $this->getMeta($products);
            Redis::set('products_first_page', compact('data', 'meta'));
        }

        return Redis::get('products_first_page');
    }

    public function getMeta(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'total' => $paginator->total(),
        ];
    }

    public function getById(int $id): array
    {
        if (!Redis::exists("product:$id")) {
            $this->cacheProduct($id);
        }

        return Redis::get("product:$id");
    }

    public function cacheProduct(int $productId): void
    {
        $product = new SingleResource(Product::findOrFail($productId));
        Redis::set("product:$productId", $product);
        Redis::set("product_id:{$product->slug}", $productId);
    }

    public function getBySlug(string $slug): array
    {
        if (!Redis::exists("product_id:$slug")) {
            $product = new SingleResource(Product::where('slug', $slug)->firstOrFail());
            Redis::set("product:{$product->id}", $product);
            Redis::set("product_id:$slug", $product->id);
        }

        $productId = Redis::get("product_id:$slug");
        return Redis::get("product:$productId");
    }

    public function getProductsToCompare(string $query): Collection
    {
        $productsIDs = explode(',', $query);

        return Product::whereIn('id', $productsIDs)->limit(10)->get();
    }

    public function getProductsToCart(string $query): Collection
    {
        $productsIDs = explode(',', $query);

        return Product::whereIn('id', $productsIDs)->limit(100)->get();
    }

    public function getFormattedData($products, $request)
    {

        $productsCollection = CompareResource::collection($products)->toArray($request);
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
