<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\FeatureTestCase;

class ProductControllerTest extends FeatureTestCase
{
    use DatabaseTransactions;

    // ============================== INDEX ==============================

    public function test_index_method_returns_status_200(): void
    {
        $response = $this->get('/api/products');

        $response->assertStatus(200);
    }

    public function test_index_method_returns_valid_structure(): void
    {
        $response = $this->get('/api/products');

        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'slug', 'price', 'image', 'description', 'brand', 'form'],
            ],
            'meta',
        ]);
    }

    public function test_index_method_returns_valid_product(): void
    {
        $product = Product::first();

        $expected = [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'price' => $product->price,
            'description' => $product->description,
        ];

        $response = $this->get('/api/products');

        $response->assertJson([
            'data' => [
                $expected
            ],
        ]);
    }

    // ============================== SHOW ==============================

    public function test_show_method_returns_valid_structure(): void
    {
        $response = $this->get('/api/products/1');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'slug',
                    'price',
                    'image',
                    'description',
                    'attributes' => [
                        '*' => ['attribute_id', 'attribute_name', 'attribute_slug', 'value_id', 'value_name']
                    ],
                    'photos',
                ],
            ]);
    }

    public function test_show_method_returns_valid_product(): void
    {
        $product = Product::first();

        $expected = [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'price' => $product->price,
            'description' => $product->description,
        ];

        $response = $this->get('/api/products/1');

        $response->assertJson([
            'data' => [
                ...$expected
            ],
        ]);
    }

    public function test_show_method_returns_valid_product_when_find_by_slug(): void
    {
        $product = Product::first();

        $response = $this->get("/api/products/{$product->slug}");

        $this->assertNotEmpty($response->json('data'));
        $this->assertEquals($product->slug, $response->json('data.slug'));
    }

    // ============================== STORE ==============================

    public function test_store_method_creates_product(): void
    {
        $countBefore = Product::count();

        $expectedProduct = [
            'name' => 'test name',
            'slug' => 'test-name',
            'price' => 100500,
            'description' => 'test description',
            'image' => 'test.jpg',

        ];

        $response = $this->actingAs($this->getAdminUser(), 'jwt')->postJson('/api/products', $expectedProduct);

        $response->assertStatus(200);

        $countAfter = Product::count();
        $this->assertEquals($countBefore + 1, $countAfter);

        $newProduct = Product::query()->orderByDesc('id')->first();
        $this->assertEquals(
            $expectedProduct,
            [
                'name' => $newProduct->name,
                'slug' => $newProduct->slug,
                'price' => $newProduct->price,
                'description' => $newProduct->description,
                'image' => $newProduct->image,

            ]
        );
    }

    // ============================== UPDATE ==============================

    public function test_update_method_updates_product(): void
    {
        $newName = 'test';

        $this->assertNotEquals($newName, Product::first()->name);

        $response = $this
            ->actingAs($this->getAdminUser(), 'jwt')
            ->withHeaders($this->getHeaders())
            ->patchJson('/api/products/1', ['name' => $newName]);

        $response->assertStatus(200);

        $this->assertEquals($newName, Product::first()->name);
    }

    // ============================== DESTROY ==============================

    public function test_destroy_method_removes_product(): void
    {
        $countBefore = Product::count();

        $response = $this
            ->actingAs($this->getAdminUser(), 'jwt')
            ->withHeaders($this->getHeaders())
            ->delete('/api/products/1');

        $response->assertStatus(200);

        $countAfter = Product::count();
        $this->assertEquals($countBefore - 1, $countAfter);
    }

    // ============================== SEARCH ==============================

    public function test_search_method_searches_product(): void
    {
        $firstProductName = Product::first()->name;
        $uppercasedTrimmedName = mb_strtoupper(substr($firstProductName, 1, -1));

        $response = $this
            ->actingAs($this->getAdminUser(), 'jwt')
            ->withHeaders($this->getHeaders())
            ->get("/api/products/search/$uppercasedTrimmedName");

        $response
            ->assertStatus(200)
            ->assertJson([
                ['name' => $firstProductName]
            ]);
    }
}
