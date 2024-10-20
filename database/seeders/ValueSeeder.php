<?php

namespace Database\Seeders;

use App\Models\Value;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ValueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::getValues() as $key => $item) {
            foreach ($item as $value) {
                Value::factory()->create([
                    'name' => $value,
                    'attribute_id' => $key + 1,
                ]);
            }
        }
    }

    public static function getBrandById(int $brandId): string
    {
        return self::getValues()[0][$brandId - 1];
    }

    protected static function getValues(): array
    {
        //1-20
        $brands = [
            'Marshall',
            'Beyerdynamic',
            'Sony',
            'Sennheiser',
            'Audio-Technica',
            'AKG',
            'Apple',
            'Beats',
            'Behringer',
            'Corsair',
            'Edifier',
            'Focal',
            'JBL',
            'Koss',
            'Philips',
            'Pioneer',
            'Ritmix',
            'Ultrasone',
            'Westone',
            'ZMW',
        ];

        //21-23
        $forms = [
            'Вставные',
            'Накладные',
            'Полноразмерные',
        ];

        //24-25
        $wireTypes = [
            'Проводные',
            'Бесроводные',
        ];

        //26-28
        $acousticTypes = [
            'Закрытые',
            'Открытые',
            'Полуоткрытые',
        ];

        //29-30
        $deNoise = ['Да', 'Нет'];

        //31-34
        // для проводных
        $wireLength = [1.5, 2, 2.5, 3];

        //35-40
        // для беспроводных
        $bluetooth = [4.2, 5.0, 5.1, 5.2, 5.3, 5.4];

        //41-44
        $connector = [
            'usb-c',
            'usb-b',
            'usb-micro',
            'lighting',
        ];

        //45-46
        $fastCharge = ['Да', 'Нет'];

        return [$brands, $forms, $wireTypes, $acousticTypes, $deNoise, $wireLength, $bluetooth, $connector, $fastCharge];
    }
}
