<?php

namespace Database\Seeders\Product;

use App\Models\ActiveIngredientModel;
use Illuminate\Database\Seeder;

class ActiveIngredientsSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/active_ingredients.json');

        if (!file_exists($path)) {
            $this->command->error("Archivo no encontrado: $path");
            return;
        }

        $json = file_get_contents($path);
        $decoded = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->command->error('JSON inválido: ' . json_last_error_msg());
            return;
        }

        // El JSON tiene { "ingredients": [...] }, no es un array directo
        $data = $decoded['ingredients'] ?? $decoded;

        $this->command->info('Insertando ' . count($data) . ' ingredientes...');

        ActiveIngredientModel::factory()->createMany($data);
    }
}
