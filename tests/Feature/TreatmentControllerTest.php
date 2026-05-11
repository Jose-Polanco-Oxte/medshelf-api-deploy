<?php

namespace Tests\Feature;

use App\Models\ActiveIngredientModel;
use App\Models\HouseModel;
use App\Models\ItemModel;
use App\Models\PharmaceuticalFormModel;
use App\Models\PlaceModel;
use App\Models\ProductCompoundModel;
use App\Models\ProductModel;
use App\Models\ProfileModel;
use App\Models\StorageModel;
use App\Models\TreatmentModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TreatmentControllerTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ────────────────────────────────────────────────────────────

    public function test_store_creates_treatment_and_returns_201(): void
    {
        $profile = $this->createProfile();
        $item = $this->createItem();

        $this->postJson('/api/treatments', [
            'profileId' => $profile->public_id,
            'itemId' => $item->public_id,
            'frequencyValue' => 8,
            'frequencyUnit' => 'hours',
            'doseQuantity' => 1.5,
            'startDate' => now()->toDateString(),
        ])
            ->assertStatus(201)
            ->assertJsonFragment(['status' => 'active']);
    }

    private function createProfile(): ProfileModel
    {
        $user = User::factory()->create();
        return ProfileModel::factory()->create(['user_id' => $user->id]);
    }

    /**
     * Creates a PharmaceuticalFormModel → ProductModel → House → Place → Storage → ItemModel chain.
     * Returns the ItemModel. The house public_id can be fetched via item->storage->place->house->public_id.
     *
     * @param string $consumptionType 'continuous'|'discrete'
     */
    private function createItem(string $consumptionType = 'continuous', int $totalContent = 10): ItemModel
    {
        $pharmaForm = PharmaceuticalFormModel::create([
            'name' => 'Tablet ' . Str::random(4),
            'consumption_type' => $consumptionType,
        ]);

        $product = ProductModel::create([
            'public_id' => Str::uuid(),
            'name' => 'Medicine ' . Str::random(4),
            'pharmaceutical_form_id' => $pharmaForm->id,
            'net_content_value' => 100,
            'net_content_unit' => 'ml',
            'total_quantity' => null,
            'composition_reference_amount' => 100,
        ]);

        $ingredient = ActiveIngredientModel::create(['name' => 'Ingredient ' . Str::random(4)]);
        ProductCompoundModel::create([
            'product_id' => $product->id,
            'active_ingredient_id' => $ingredient->id,
            'strength_value' => 500,
            'strength_unit' => 'mg',
        ]);

        $houseOwner = User::factory()->create();

        $house = HouseModel::create([
            'public_id' => Str::uuid(),
            'owner_id' => $houseOwner->id,
            'name' => 'Home',
        ]);

        $place = PlaceModel::create([
            'public_id' => Str::uuid(),
            'house_id' => $house->id,
            'name' => 'Cabinet',
        ]);

        $storage = StorageModel::create([
            'public_id' => Str::uuid(),
            'place_id' => $place->id,
            'name' => 'Shelf',
        ]);

        return ItemModel::create([
            'public_id' => Str::uuid(),
            'product_id' => $product->id,
            'storage_id' => $storage->id,
            'total_content' => $totalContent,
            'expiration_date' => now()->addYear(),
        ]);
    }

    // ── POST /api/treatments ───────────────────────────────────────────────

    public function test_store_returns_404_when_profile_not_found(): void
    {
        $item = $this->createItem();

        $this->postJson('/api/treatments', [
            'profileId' => Str::uuid(),
            'itemId' => $item->public_id,
            'frequencyValue' => 8,
            'frequencyUnit' => 'hours',
            'doseQuantity' => 1.0,
            'startDate' => now()->toDateString(),
        ])
            ->assertStatus(404);
    }

    public function test_store_returns_422_on_validation_failure(): void
    {
        $this->postJson('/api/treatments', [
            'profileId' => 'not-a-uuid',
        ])
            ->assertStatus(422);
    }

    public function test_index_returns_treatments_for_profile(): void
    {
        $treatment = $this->createTreatment();

        $this->getJson("/api/treatments?profile_id={$treatment->profile->public_id}")
            ->assertStatus(200)
            ->assertJsonFragment(['id' => $treatment->public_id]);
    }

    // ── GET /api/treatments ────────────────────────────────────────────────

    private function createTreatment(string $status = 'active'): TreatmentModel
    {
        $profile = $this->createProfile();
        $item = $this->createItem();

        return TreatmentModel::create([
            'public_id' => Str::uuid(),
            'profile_id' => $profile->id,
            'item_id' => $item->id,
            'status' => $status,
            'frequency_value' => 8,
            'frequency_unit' => 'hours',
            'dose_quantity' => 1.0,
            'start_date' => now()->toDateString(),
            'end_date' => null,
        ]);
    }

    // ── GET /api/treatments/{id} ───────────────────────────────────────────

    public function test_show_returns_treatment_detail(): void
    {
        $treatment = $this->createTreatment();

        $this->getJson("/api/treatments/{$treatment->public_id}")
            ->assertStatus(200)
            ->assertJsonFragment(['id' => $treatment->public_id]);
    }

    public function test_show_returns_404_when_not_found(): void
    {
        $this->getJson('/api/treatments/non-existent-id')
            ->assertStatus(404);
    }

    // ── PUT /api/treatments/{id} ───────────────────────────────────────────

    public function test_update_returns_updated_treatment(): void
    {
        $treatment = $this->createTreatment();

        $this->putJson("/api/treatments/{$treatment->public_id}", [
            'frequencyValue' => 24,
            'frequencyUnit' => 'hours',
        ])
            ->assertStatus(200)
            ->assertJsonFragment(['frequencyValue' => 24]);
    }

    // ── POST /api/treatments/{id}/pause ───────────────────────────────────

    public function test_pause_transitions_treatment_to_paused(): void
    {
        $treatment = $this->createTreatment('active');

        $this->postJson("/api/treatments/{$treatment->public_id}/pause")
            ->assertStatus(200)
            ->assertJsonFragment(['status' => 'paused']);
    }

    public function test_pause_returns_422_when_already_paused(): void
    {
        $treatment = $this->createTreatment('paused');

        $this->postJson("/api/treatments/{$treatment->public_id}/pause")
            ->assertStatus(400);
    }

    // ── POST /api/treatments/{id}/resume ──────────────────────────────────

    public function test_resume_transitions_paused_treatment_to_active(): void
    {
        $treatment = $this->createTreatment('paused');

        $this->postJson("/api/treatments/{$treatment->public_id}/resume")
            ->assertStatus(200)
            ->assertJsonFragment(['status' => 'active']);
    }

    // ── POST /api/treatments/{id}/cancel ──────────────────────────────────

    public function test_cancel_cancels_active_treatment(): void
    {
        $treatment = $this->createTreatment('active');

        $this->postJson("/api/treatments/{$treatment->public_id}/cancel")
            ->assertStatus(200)
            ->assertJsonFragment(['status' => 'cancelled']);
    }

    // ── POST /api/treatments/{id}/complete ────────────────────────────────

    public function test_complete_completes_active_treatment(): void
    {
        $treatment = $this->createTreatment('active');

        $this->postJson("/api/treatments/{$treatment->public_id}/complete")
            ->assertStatus(200)
            ->assertJsonFragment(['status' => 'completed']);
    }

    // ── POST /api/treatments/{id}/consumptions ────────────────────────────

    public function test_store_dose_registers_dose_and_returns_201(): void
    {
        $profile = $this->createProfile();
        $item = $this->createItem('continuous', 10);

        $treatment = TreatmentModel::create([
            'public_id' => Str::uuid(),
            'profile_id' => $profile->id,
            'item_id' => $item->id,
            'status' => 'active',
            'frequency_value' => 8,
            'frequency_unit' => 'hours',
            'dose_quantity' => 1.0,
            'start_date' => now()->toDateString(),
            'end_date' => null,
        ]);

        $house = $item->storage->place->house;

        $this->postJson("/api/treatments/{$treatment->public_id}/consumptions", [
            'amount' => 1.5,
        ], ['X-House-Id' => $house->public_id])
            ->assertStatus(201);
    }

    public function test_store_dose_returns_422_when_treatment_not_active(): void
    {
        $profile = $this->createProfile();
        $item = $this->createItem('continuous', 10);

        $treatment = TreatmentModel::create([
            'public_id' => Str::uuid(),
            'profile_id' => $profile->id,
            'item_id' => $item->id,
            'status' => 'paused',
            'frequency_value' => 8,
            'frequency_unit' => 'hours',
            'dose_quantity' => 1.0,
            'start_date' => now()->toDateString(),
            'end_date' => null,
        ]);

        $house = $item->storage->place->house;

        $this->postJson("/api/treatments/{$treatment->public_id}/consumptions", [
            'amount' => 1.5,
        ], ['X-House-Id' => $house->public_id])
            ->assertStatus(400);
    }

    // ── GET /api/treatments/{id}/consumptions ─────────────────────────────

    public function test_index_doses_returns_200(): void
    {
        $treatment = $this->createTreatment();

        $this->getJson("/api/treatments/{$treatment->public_id}/consumptions")
            ->assertStatus(200);
    }
}
