<?php

namespace Database\Seeders\Product;

use App\Core\Shared\Domain\Utils;
use App\Models\ActiveIngredientModel;
use App\Models\PharmaceuticalFormModel;
use App\Models\ProductCompoundModel;
use App\Models\ProductModel;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = json_decode(
            file_get_contents(database_path('seeders/data/products.json')),
            associative: true
        );

        foreach ($products as $data) {
            // 1. Buscar o crear la forma farmacéutica
            $form = PharmaceuticalFormModel::firstOrCreate(
                ['name' => $data['pharmaceuticalForm']['name']],
                ['consumption_type' => $data['pharmaceuticalForm']['consumptionType']]
            );

            // 2. Crear el producto
            $product = ProductModel::create([
                'public_id' => Utils::generateUUIDV4(),
                'name' => $data['name'],
                'net_content_value' => $data['netContent']['value'] ?? null,
                'net_content_unit' => $data['netContent']['unit'] ?? null,
                'total_quantity' => $data['totalQuantity'],
                'pharmaceutical_form_id' => $form->id,
                'composition_reference_amount' => $data['composition']['referenceAmount'],
            ]);

            // 3. Crear los ingredientes activos y sus compuestos
            foreach ($data['composition']['activeIngredients'] as $ingredient) {
                $activeIngredient = ActiveIngredientModel::firstOrCreate(
                    ['name' => $ingredient['name']],
                );

                ProductCompoundModel::create([
                    'product_id' => $product->id,
                    'active_ingredient_id' => $activeIngredient->id,
                    'strength_value' => $ingredient['strength']['value'],
                    'strength_unit' => $ingredient['strength']['unit'],
                ]);
            }

            $this->command->line("✓ $product->name");
        }

        $this->command->info('Seeding completado: ' . count($products) . ' productos insertados.');
    }
}

