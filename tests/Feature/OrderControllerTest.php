<?php

namespace Tests\Feature;

use App\Helpers\StatusHelper;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\FeatureTestCase;

class OrderControllerTest extends FeatureTestCase
{
    use DatabaseTransactions;

    // ============================== STORE ==============================

    public function test_store_method_saves_order_by_unregistered_user(): void
    {
        $orderData = [
            'name' => 'Test name',
            'phone' => '123456789',
            'email' => 'test@mail.com',
            'address' => 'Test Address, 1',
            'comment' => 'Test comment',
            'products' => [
                [
                    'product_id' => 1,
                    'count' => 4,
                ],
                [
                    'product_id' => 2,
                    'count' => 1,
                ],
                [
                    'product_id' => 3,
                    'count' => 2,
                ],
            ],
        ];

        $response = $this->postJson('/api/orders', $orderData);
        $createdOrder = Order::orderByDesc('id')->with('orderProducts')->first();

        $response->assertStatus(201);

        $this->assertNull($createdOrder->user_id);
        $this->assertEquals($createdOrder->status_id, StatusHelper::CREATED);
        $this->assertEquals($createdOrder->total, $this->calculateTotal($orderData));
        $this->assertEquals($createdOrder->name, $orderData['name']);
        $this->assertEquals($createdOrder->phone, $orderData['phone']);
        $this->assertEquals($createdOrder->email, $orderData['email']);
        $this->assertEquals($createdOrder->address, $orderData['address']);
        $this->assertEquals($createdOrder->comment, $orderData['comment']);

        $this->assertProductEquals(
            expectedProducts: $orderData['products'],
            createdOrderProducts: $createdOrder->orderProducts
        );
    }

    public function test_store_method_saves_order_by_registered_user(): void
    {
        $orderData = [
            'products' => [
                [
                    'product_id' => 1,
                    'count' => 4,
                ],
            ],
        ];

        $user = User::find(1);

        $response = $this->actingAs($user, 'jwt')->postJson('/api/orders', $orderData);
        $createdOrder = Order::orderByDesc('id')->first();

        $response->assertStatus(201);
        $this->assertEquals($createdOrder->user_id, $user->id);
        $this->assertEquals($createdOrder->name, $user->name);
        $this->assertEquals($createdOrder->phone, $user->phone);
        $this->assertEquals($createdOrder->email, $user->email);
        $this->assertEquals($createdOrder->address, $user->address);
    }

    // ============================== HELPERS ==============================

    protected function assertProductEquals(array $expectedProducts, Collection $createdOrderProducts): void
    {
        $expectedProductIds = array_column($expectedProducts, 'product_id');

        $productDataFromDb = Product::whereIn('id', $expectedProductIds)->get();

        foreach ($expectedProducts as $expectedProduct) {

            $expectedPrice = $productDataFromDb->find($expectedProduct['product_id'])->price;
            $createdOrderProduct = $createdOrderProducts->find($expectedProduct['product_id']);

            $this->assertEquals($expectedProduct['product_id'], $createdOrderProduct->product_id);
            $this->assertEquals($expectedProduct['count'], $createdOrderProduct->count);
            $this->assertEquals($expectedPrice, $createdOrderProduct->price);
        }
    }

    protected function calculateTotal(array $orderData): int
    {
        $total = 0;

        $expectedProductIds = array_column($orderData['products'], 'product_id');
        $productDataFromDb = Product::whereIn('id', $expectedProductIds)->get();

        foreach ($orderData['products'] as $product) {
            $total +=  $product['count'] * $productDataFromDb->find($product['product_id'])->price;
        }

        return $total;
    }
}
