<?php

namespace Database\Seeders;

use App\Http\Resources\Product\SingleResource;
use App\Models\Attribute;
use App\Models\Permission;
use App\Models\PermissionUser;
use App\Models\Photo;
use App\Models\Product;
use App\Models\ProductValue;
use App\Models\Status;
use App\Models\User;
use App\Models\Value;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Redis;

class DatabaseSeeder extends Seeder
{
    private array $attributes;
    private array $values;
    private array $photos;
    private array $typesCounter;

    public function __construct()
    {
        require_once('attributes.php');

        $this->attributes = $attributes;
        $this->values = $values;
        $this->photos = $photos;
        $this->typesCounter = $typesCounter;
    }

    /**
     * Почему тут так всё замороченно:
     * 
     * Есть 6 подтипов наушников:
     * полноразмерные/накладные/вставные,
     * они дялятся на проводные/беспроводные
     * 
     * У проводных есть одни атрибуты (длина шнура)
     * У беспроводных - другие (bluetooth, etc)
     * 
     * И у каждого типа наушников свой набор фоток, 
     * чтобы сразу было видно, что все фильтры работают.
     * Ну и просто, чтобы было чуть разнообразнее и симпотичнее :)
     */
    public function run(): void
    {
        $this->clearProductsCache();

        User::factory()->create([
            'name' => 'Семён Семёныч',
            'email' => 'a@a.a',
            'address' => 'г. Кирово-Чепецк, ул. Кукурузная, д. 35, кв 14',
            'phone' => '+79999999999',
            'password' => '123',
        ]);

        // User::factory(10)->create();

        Status::factory()->createMany([
            ['name' => 'Создан, ожидает подтверждения.'],
            ['name' => 'Подтверждён, ожидает оплаты.'],
            ['name' => 'Оплачен, ожидает отправки.'],
            ['name' => 'Отправлен.'],
            ['name' => 'Завершён.'],
        ]);

        Permission::factory()->createMany([
            ['name' => 'edit_orders', 'description' => 'Может обрабатывать заказы и смотреть информацию о пользователях.'],
            ['name' => 'edit_content', 'description' => 'Может редактировать товары и список доступных характеристик товаров.'],
            ['name' => 'edit_users', 'description' => 'Может редактировать пользователей и выдавать им права на различные действия.'],
        ]);

        $this->cachePermissionsIds();

        PermissionUser::factory()->createMany([
            ['user_id' => 1, 'permission_id' => 1],
            ['user_id' => 1, 'permission_id' => 2],
            ['user_id' => 1, 'permission_id' => 3],
        ]);

        Attribute::factory()->createMany($this->attributes);
        Redis::set('product_attributes', Attribute::pluck('slug'));

        $this->createValues();

        $this->createProducts(100);
    }

    private function clearProductsCache(): void
    {
        $prefix = config('database.redis.options.prefix');
        foreach (Redis::keys('product:*') as $key) {
            $key = ltrim($key, $prefix);
            Redis::del($key);
        }
        foreach (Redis::keys('product_id:*') as $key) {
            $key = ltrim($key, $prefix);
            Redis::del($key);
        }

        Redis::del('products_first_page');
    }

    private function cachePermissionsIds(): void
    {
        $permissionList = [];

        foreach (Permission::all() as $permission) {
            $permissionList[$permission['name']] = $permission['id'];
        }

        Redis::set('user_permissions', $permissionList);
    }

    private function createValues(): void
    {
        foreach ($this->values as $key => $item) {
            foreach ($item as $value) {
                Value::factory()->create([
                    'name' => $value,
                    'attribute_id' => $key + 1,
                ]);
            }
        }
    }

    private function createProducts(int $quantity): void
    {
        for ($id = 1; $id <= $quantity; $id++) {
            $this->createProductWithAttributes($id);
            $this->cacheProduct($id);
        }
    }

    private function createProductAttribute(int $productId, int $valueId): void
    {
        ProductValue::factory()->create([
            'product_id' => $productId,
            'value_id' => $valueId,
        ]);
    }

    private function generateName(int $brandId): string
    {
        $brand = $this->values[0][$brandId - 1];
        $name = fake()->word();
        $code = (fake()->text(5))[0] . rand(30, 200);

        return "$brand $name $code";
    }

    private function nameToSlug(string $name): string
    {
        return strtolower(str_replace(' ', '-', $name));
    }

    private function cacheProduct(int $id): void
    {
        $product = new SingleResource(Product::findOrFail($id));
        Redis::set("product:$id", $product);
        Redis::set("product_id:{$product->slug}", $id);
    }

    private function createProductWithPhotos(int $productId, int $brandId, string $form, string $wire): void
    {
        $name = $this->generateName($brandId);
        $slug = $this->nameToSlug($name);
        $count = $this->typesCounter[$wire][$form];
        $lastPhoto = $this->photos[$wire][$form][$count - 1];

        Product::factory()->create([
            'name' => $name,
            'slug' => $slug,
            'image' => "/$wire/$form/$count/0.webp"
        ]);

        for ($i = 1; $i <= $lastPhoto; $i++) {
            Photo::factory()->create([
                'url' => "/$wire/$form/$count/$i.webp",
                'product_id' => $productId,
            ]);
        }

        $this->typesCounter[$wire][$form] = $count < 5 ? ++$count : 1;
    }

    private function createProductWithAttributes(int $productId): void
    {

        $brand = rand(1, 20);
        $this->createProductAttribute($productId, $brand); // brand

        $form = rand(21, 23);
        $this->createProductAttribute($productId, $form); // form

        $wire = rand(24, 25);
        $this->createProductAttribute($productId, $wire); // wireTypes

        $this->createProductAttribute($productId, rand(26, 28)); // acousticTypes
        $this->createProductAttribute($productId, rand(29, 30)); // deNoise

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
    }
}
