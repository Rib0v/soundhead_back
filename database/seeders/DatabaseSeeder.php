<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\Permission;
use App\Models\PermissionUser;
use App\Models\Photo;
use App\Models\Product;
use App\Models\ProductValue;
use App\Models\User;
use App\Services\Cache\CacheService;
use App\Services\PermissionService;
use App\Services\ProductService;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Database\Seeders\ValueSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    private array $photos;
    private array $typesCounter;
    private CacheService $cache;
    private ProductService $productService;

    public function __construct()
    {
        require_once('attributes.php');

        $this->photos = $photos;
        $this->typesCounter = $typesCounter;

        $this->cache = app(CacheService::class);
        $this->productService = app(ProductService::class);
    }

    /**
     * Почему тут так всё заморочено:
     * 
     * Есть 6 подтипов наушников:
     * полноразмерные/накладные/вставные,
     * они делятся на проводные/беспроводные
     * 
     * У проводных есть одни атрибуты (длина шнура)
     * У беспроводных - другие (bluetooth, etc)
     * 
     * И у каждого типа наушников свой набор фоток, 
     * чтобы сразу было видно, что все фильтры работают.
     * Ну и в целом, чтобы лучше воспринималось визуально
     */
    public function run(): void
    {
        $this->productService->clearProductCache();

        DB::beginTransaction();

        User::factory()->create([
            'name' => 'Семён Семёныч',
            'email' => 'a@a.a',
            'address' => 'г. Кирово-Чепецк, ул. Кукурузная, д. 35, кв 14',
            'phone' => '+79999999999',
            'password' => '123',
        ]);

        // User::factory(10)->create();

        $this->call(OrderStatusSeeder::class);

        Permission::factory()->createMany([
            ['name' => 'edit_orders', 'description' => 'Может обрабатывать заказы и смотреть информацию о пользователях.'],
            ['name' => 'edit_content', 'description' => 'Может редактировать товары и список доступных характеристик товаров.'],
            ['name' => 'edit_users', 'description' => 'Может редактировать пользователей и выдавать им права на различные действия.'],
        ]);

        PermissionUser::factory()->createMany([
            ['user_id' => 1, 'permission_id' => 1],
            ['user_id' => 1, 'permission_id' => 2],
            ['user_id' => 1, 'permission_id' => 3],
        ]);

        $this->call(AttributeSeeder::class);
        $this->call(ValueSeeder::class);

        $this->createProducts(102);

        DB::commit();

        $this->cacheItems();
    }

    private function createProducts(int $quantity): void
    {
        for ($id = 1; $id <= $quantity; $id++) {
            $this->createProductWithAttributes($id);
            $this->productService->cacheById($id);
        }
    }

    private function createProductWithAttributes(int $productId): void
    {
        $brand = rand(1, 20);
        $form = rand(21, 23);
        $wire = rand(24, 25);

        if ($form === 21) { // Вставные
            if ($wire === 24) { // Проводные
                $this->createProductWithPhotos($productId, $brand, 'inear', 'wired');
            } else { // Беспроводные
                $this->createProductWithPhotos($productId, $brand, 'inear', 'wireless');
            }
        }

        if ($form === 22) { // Накладные
            if ($wire === 24) { // Проводные
                $this->createProductWithPhotos($productId, $brand, 'overhead', 'wired');
            } else { // Беспроводные
                $this->createProductWithPhotos($productId, $brand, 'overhead', 'wireless');
            }
        }

        if ($form === 23) { // Полноразмерные
            if ($wire === 24) { // Проводные
                $this->createProductWithPhotos($productId, $brand, 'fullsize', 'wired');
            } else { // Беспроводные
                $this->createProductWithPhotos($productId, $brand, 'fullsize', 'wireless');
            }
        }

        if ($wire === 24) { // Проводные
            $this->createProductAttribute($productId, rand(31, 34)); // wireLength
        } else { // Беспроводные
            $this->createProductAttribute($productId, rand(35, 40)); // bluetooth
            $this->createProductAttribute($productId, rand(41, 44)); // connector
            $this->createProductAttribute($productId, rand(45, 46)); // fastCharge
        }

        $this->createProductAttribute($productId, $brand); // brand
        $this->createProductAttribute($productId, $form); // form
        $this->createProductAttribute($productId, $wire); // wireTypes
        $this->createProductAttribute($productId, rand(26, 28)); // acousticTypes
        $this->createProductAttribute($productId, rand(29, 30)); // deNoise
    }

    private function createProductAttribute(int $productId, int $valueId): void
    {
        ProductValue::factory()->create([
            'product_id' => $productId,
            'value_id' => $valueId,
        ]);
    }

    private function createProductWithPhotos(int $productId, int $brandId, string $form, string $wire): void
    {
        $name = $this->generateName($brandId);
        $count = $this->typesCounter[$wire][$form];
        $lastPhotoNumber = $this->photos[$wire][$form][$count - 1];

        Product::factory()->create([
            'name' => $name,
            'slug' => toSlug($name),
            'image' => "/$wire/$form/$count/0.webp"
        ]);

        for ($i = 1; $i <= $lastPhotoNumber; $i++) {
            Photo::factory()->create([
                'url' => "/$wire/$form/$count/$i.webp",
                'product_id' => $productId,
            ]);
        }

        $this->typesCounter[$wire][$form] = $count < 5 ? ++$count : 1;
    }

    private function generateName(int $brandId): string
    {
        $brand = ValueSeeder::getBrandById($brandId);
        $name = fake()->word();
        $code = (fake()->text(5))[0] . rand(30, 200);

        return "$brand $name $code";
    }

    private function cacheItems(): void
    {
        PermissionService::cachePermissionsIds();

        $this->cache->cacheOnEveryCall('product_attributes', fn() => Attribute::pluck('slug'));

        $this->productService->cacheProductListPages(
            firstPage: 1,
            lastPage: Product::paginate(config('app.products_per_page_default'))->lastPage(),
        );
    }
}
