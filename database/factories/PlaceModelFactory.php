<?php

namespace Database\Factories;

use App\Models\HouseModel;
use App\Models\PlaceModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlaceModel>
 */
class PlaceModelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'public_id' => $this->faker->unique()->uuid(),
            'house_id' => HouseModel::factory(),
            'name' => $this->faker->word(),
        ];
    }
}
