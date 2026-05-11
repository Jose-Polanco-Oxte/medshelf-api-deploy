<?php

namespace Database\Factories;

use App\Models\ActiveIngredientModel;
use App\Models\ProductCompoundModel;
use App\Models\ProductModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductCompoundModel>
 */
class ProductCompoundModelFactory extends Factory
{
    public function definition(): array
    {
        return [
            'active_ingredient_id' => ActiveIngredientModel::factory(),
            'product_id' => ProductModel::factory(),
            'strength_value' => fake()->randomFloat(2, 0.1, 100),
            'strength_unit' => fake()->randomElement(['mg', 'ml', 'g']),
        ];
    }
}
