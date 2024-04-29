<?php

namespace App\Services;

use App\Http\Resources\Product\CompareResource;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class ProductService
{
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
