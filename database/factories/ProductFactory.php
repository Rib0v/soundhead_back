<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->word();
        return [
            'name' => $name,
            'price' => (fake()->numberBetween(10, 400)) * 100,
            'description' => fake()->paragraph(14),
            'min_frequency' => fake()->numberBetween(30, 100),
            'max_frequency' => fake()->numberBetween(18, 30),
            'sensitivity' => fake()->numberBetween(38, 127),
            'image' => fake()->imageUrl(200, 200, $name),
        ];
    }
}
