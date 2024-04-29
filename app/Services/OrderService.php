<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Status;
use Illuminate\Http\Request;

class OrderService
{
    public function __construct(private JWTAuthService $jwt)
    {
    }

    public function checkStatusId(int $statusId): bool
    {
        $maxStatusId = Status::orderByDesc('id')->first()->id;
        return $statusId <= $maxStatusId;
    }

    public function addActualPricesFromDB(array $products): array
    {
        $productsIDs = array_column($products, 'product_id');

        $productsPrices = Product::select('id', 'price')->whereIn('id', $productsIDs)->get();

        $priceAssoc = [];

        foreach ($productsPrices as $product) {
            $priceAssoc[$product['id']] = $product['price'];
        }

        foreach ($products as $key => $product) {
            $products[$key]['price'] = $priceAssoc[$product['product_id']];
        }

        return $products;
    }

    public function prepareTheOrder(array $validated, Request $request): array
    {
        $totalPrice = $this->calculateTotalPrice($validated['products']);
        $userId = $this->getUserIdFromToken($request);
        $formedOrder = $this->formatTheOrder($validated, $totalPrice, $userId);

        return $formedOrder;
    }

    private function calculateTotalPrice(array $products): int
    {
        return array_reduce($products, fn ($accum, $product) => $accum += $product['count'] * $product['price']);
    }

    private function getUserIdFromToken(Request $request): int
    {
        $userId = 0;
        $access = $request->bearerToken();
        $refresh = $request->cookie('refresh');

        try {
            $checked = $this->jwt->checkAccess($access);
            $userId = $checked->sub;
        } catch (\Throwable $th) {
            try {
                $checked = $this->jwt->checkRefresh($refresh);
                $userId = $checked->sub;
            } catch (\Throwable $th) {
            }
        }

        return $userId;
    }

    private function formatTheOrder(array $validated, int $totalPrice, int $userId): array
    {
        $order = [
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'address' => $validated['address'],
            'comment' => $validated['comment'],
            'total' => $totalPrice,
        ];

        if ($userId > 0) $order['user_id'] = $userId;

        return $order;
    }
}
