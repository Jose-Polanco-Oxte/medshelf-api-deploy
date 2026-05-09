<?php

namespace Database\Factories;

use App\Models\ItemModel;
use App\Models\ProfileModel;
use App\Models\TreatmentModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TreatmentModel>
 */
class TreatmentModelFactory extends Factory
{
    public function definition(): array
    {
        return [
            'public_id' => fake()->unique()->uuid(),
            'profile_id' => ProfileModel::factory(),
            'item_id' => ItemModel::factory(),
            'status' => 'active',
            'frequency_value' => 8,
            'frequency_unit' => 'hours',
            'dose_quantity' => 1.0,
            'start_date' => now()->toDateString(),
            'end_date' => null,
        ];
    }
}
