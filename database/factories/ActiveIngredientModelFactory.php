<?php

namespace Database\Factories;

use App\Models\ActiveIngredientModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActiveIngredientModel>
 */
class ActiveIngredientModelFactory extends Factory
{

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
        ];
    }
}
