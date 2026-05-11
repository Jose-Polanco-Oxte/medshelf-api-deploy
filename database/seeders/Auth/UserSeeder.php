<?php

namespace Database\Seeders\Auth;

use App\Core\Shared\Domain\Utils;
use App\Models\HouseModel;
use App\Models\PlaceModel;
use App\Models\StorageModel;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {

        $user = User::factory()->create([
            'public_id' => Utils::generateUUIDV4(),
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);
        $house = HouseModel::factory()->create([
            'owner_id' => $user->id,
            'public_id' => Utils::generateUUIDV4(),
            'name' => 'Admin House',
        ]);
        $place = PlaceModel::factory()->create([
            'house_id' => $house->id,
            'public_id' => Utils::generateUUIDV4(),
            'name' => 'Default Place',
        ]);
        StorageModel::factory()->create([
            'place_id' => $place->id,
            'public_id' => Utils::generateUUIDV4(),
            'name' => 'Default Storage',
        ]);

        User::factory()
            ->count(3)
            ->state(fn() => ['public_id' => Utils::generateUUIDV4()])
            ->create();
    }
}

