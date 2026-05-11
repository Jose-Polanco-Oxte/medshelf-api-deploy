<?php

namespace Database\Factories;

use App\Models\PharmaceuticalFormModel;
use App\Models\ProductCompoundModel;
use App\Models\ProductModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductModel>
 */
class ProductModelFactory extends Factory
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
            'name' => fake()->word(),
            'net_content_value' => fake()->randomFloat(2, 1, 100),
            'net_content_unit' => fake()->randomElement(['mg', 'ml', 'g']),
            'total_quantity' => fake()->numberBetween(10, 100),
            'pharmaceutical_form_id' => PharmaceuticalFormModel::factory(),
            'composition_reference_amount' => fake()->randomFloat(2, 1, 100),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (ProductModel $product) {
            ProductCompoundModel::factory()->count(2)->create([
                'product_id' => $product->id,
            ]);
        });
    }
}
