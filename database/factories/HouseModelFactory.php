<?php

namespace Database\Factories;

use App\Models\HouseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HouseModel>
 */
class HouseModelFactory extends Factory
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
            'owner_id' => User::factory(),
            'name' => $this->faker->word(),
        ];
    }
}
