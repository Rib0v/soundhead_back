<?php

namespace Database\Seeders;

use App\Models\Attribute;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $attributes = [
            ['name' => 'Марка', 'slug' => 'brand'],
            ['name' => 'Форма', 'slug' => 'form'],
            ['name' => 'Подключение', 'slug' => 'connect'],
            ['name' => 'Акустическое оформление', 'slug' => 'actype'],
            ['name' => 'Активное шумоподавление', 'slug' => 'denoise'],
            ['name' => 'Длина провода', 'slug' => 'wirelen'],
            ['name' => 'Версия bluetooth', 'slug' => 'bluetooth'],
            ['name' => 'Разъем для зарядки', 'slug' => 'connector'],
            ['name' => 'Быстрая зарядка', 'slug' => 'fcharge'],
        ];

        Attribute::factory()->createMany($attributes);
    }
}
