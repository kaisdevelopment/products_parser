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
        return [
            'code' => (string) $this->faker->unique()->numberBetween(100000, 999999),
            'status' => 'published',
            'imported_t' => now(),
            'url' => $this->faker->url,
            'product_name' => $this->faker->words(3, true),
            'quantity' => '100g',
            'brands' => $this->faker->company,
            'categories' => 'Snacks',
            'labels' => 'Gluten-free',
            'image_url' => $this->faker->imageUrl,
        ];
    }
}
