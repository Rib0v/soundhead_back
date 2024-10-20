<?php

namespace Database\Seeders;

use App\Helpers\StatusHelper;
use App\Models\Status;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Status::factory()->createMany([
            ['id' => StatusHelper::CREATED, 'name' => 'Создан, ожидает подтверждения.'],
            ['id' => StatusHelper::CONFIRMED, 'name' => 'Подтверждён, ожидает оплаты.'],
            ['id' => StatusHelper::PAID, 'name' => 'Оплачен, ожидает отправки.'],
            ['id' => StatusHelper::SHIPPED, 'name' => 'Отправлен.'],
            ['id' => StatusHelper::COMPLETED, 'name' => 'Завершён.'],
        ]);
    }
}
