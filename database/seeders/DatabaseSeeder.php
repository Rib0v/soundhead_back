<?php

namespace Database\Seeders;

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

class DatabaseSeeder extends Seeder
{
    private array $attributes;
    private array $values;
    private array $thumbnails;
    private array $photos;

    public function __construct()
    {
        require_once('attributes.php');

        $this->attributes = $attributes;
        $this->values = $values;
        $this->thumbnails = $thumbnails;
        $this->photos = $photos;
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

        User::factory()->create([
            'name' => 'Семён Семёныч',
            'email' => 'a@a.a',
            'address' => 'г. Кирово-черпецк, ул. Кукурузная, д. 35, кв 14',
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

        PermissionUser::factory()->createMany([
            ['user_id' => 1, 'permission_id' => 1],
            ['user_id' => 1, 'permission_id' => 2],
            ['user_id' => 1, 'permission_id' => 3],
        ]);

        Attribute::factory()->createMany($this->attributes);

        $this->createValues();

        $this->createProducts(100);
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
        for ($i = 1; $i <= $quantity; $i++) {
            $this->createProductWithAttributes($i);
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

    private function createProductWithPhotos(int $productId, int $brandId, string $productType): void
    {
        $name = $this->generateName($brandId);
        $slug = $this->nameToSlug($name);

        Product::factory()->create(['name' => $name, 'slug' => $slug, 'image' => $this->thumbnails[$productType]]);

        foreach ($this->photos[$productType] as $photo) {
            Photo::factory()->create([
                'url' => $photo,
                'product_id' => $productId,
            ]);
        }
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
            if ($wire === 24) { // если проводные
                $this->createProductWithPhotos($productId, $brand, 'inear_wired');
            } else { // если беспроводные
                $this->createProductWithPhotos($productId, $brand, 'inear_wireless');
            }
        }

        if ($form === 22) { // Накладные
            if ($wire === 24) { // если проводные
                $this->createProductWithPhotos($productId, $brand, 'overhead_wired');
            } else { // если беспроводные
                $this->createProductWithPhotos($productId, $brand, 'overhead_wireless');
            }
        }

        if ($form === 23) { // Полноразмерные
            if ($wire === 24) { // если проводные
                $this->createProductWithPhotos($productId, $brand, 'fullsize_wired');
            } else { // если беспроводные
                $this->createProductWithPhotos($productId, $brand, 'fullsize_wireless');
            }
        }

        if ($wire === 24) { // если проводные
            $this->createProductAttribute($productId, rand(31, 34)); // wireLength
        } else { // если беспроводные
            $this->createProductAttribute($productId, rand(35, 40)); // bluetooth
            $this->createProductAttribute($productId, rand(41, 44)); // connector
            $this->createProductAttribute($productId, rand(45, 46)); // fastCharge
        }
    }
}
