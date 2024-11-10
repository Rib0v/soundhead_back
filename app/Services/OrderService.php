<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;

class OrderService
{
    public function getPreparedOrderAndProducts(array $order, ?User $user): array
    {
        $productsWithPrices = $this->addActualPricesFromDB($order['products']);
        $preparedOrder = $this->prepareTheOrder($order, $productsWithPrices, $user);

        return [$preparedOrder, $productsWithPrices];
    }

    protected function addActualPricesFromDB(array $products): array
    {
        $productsIDs = array_column($products, 'product_id');

        $productsWithPrices = Product::select('id', 'price')->whereIn('id', $productsIDs)->get();

        foreach ($products as &$product) {
            $product['price'] = $productsWithPrices->find($product['product_id'])->price;
        }

        return $products;
    }

    protected function prepareTheOrder(array $order, array $products, ?User $user): array
    {
        unset($order['products']);

        $order['total'] = $this->calculateTotalPrice($products);

        if (! is_null($user)) {
            $order['user_id'] = $user->id;
        }

        return $order;
    }

    protected function calculateTotalPrice(array $products): int
    {
        return array_reduce($products, fn($sum, $product) => $sum += $product['count'] * $product['price']);
    }
}
