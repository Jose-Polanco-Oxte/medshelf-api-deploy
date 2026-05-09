<?php

namespace Database\Factories;

use App\Models\ItemModel;
use App\Models\ProductModel;
use App\Models\StorageModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ItemModel>
 */
class ItemModelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'public_id' => fake()->unique()->uuid(),
            'product_id' => ProductModel::factory(),
            'storage_id' => StorageModel::factory(),
            'total_content' => fake()->numberBetween(10, 100),
            'expiration_date' => fake()->dateTimeBetween('now', '+1 year'),
        ];
    }
}
