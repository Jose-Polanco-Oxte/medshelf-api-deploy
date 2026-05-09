<?php

namespace Database\Factories;

use App\Models\ProfileModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProfileModel>
 */
class ProfileModelFactory extends Factory
{
    public function definition(): array
    {
        return [
            'public_id' => fake()->unique()->uuid(),
            'user_id' => User::factory(),
            'name' => fake()->firstName(),
            'relationship' => fake()->randomElement(['parent', 'child', 'sibling', null]),
        ];
    }
}
