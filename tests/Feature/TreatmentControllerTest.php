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
use Tests\Feature\Traits\WithJwtAuth;
use Tests\TestCase;

class TreatmentControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithJwtAuth;

    private User $actor;

    public function test_store_creates_treatment_and_returns_201(): void
    {
        $profile = $this->createProfile();
        $item = $this->createItem('continuous', 10, $this->actor);
        $this->actor->load('house');

        $this->postJson("/api/profiles/$profile->public_id/treatments", [
            'productId' => $item->product->public_id,
            'dose' => 1.5,
            'frequencyHours' => 8,
            'startDate' => now()->toIso8601String(),
        ], $this->authHeaders($this->actor))
            ->assertStatus(201)
            ->assertJsonFragment(['status' => 'active']);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

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
    private function createItem(string $consumptionType = 'continuous', int $totalContent = 10, ?User $houseOwner = null): ItemModel
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

        $houseOwner ??= User::factory()->create();

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

    public function test_store_response_has_required_fields(): void
    {
        $profile = $this->createProfile();
        $item = $this->createItem('continuous', 10, $this->actor);
        $this->actor->load('house');

        $this->postJson("/api/profiles/{$profile->public_id}/treatments", [
            'productId' => $item->product->public_id,
            'dose' => 1.0,
            'frequencyHours' => 8,
            'startDate' => now()->toIso8601ZuluString('millisecond'),
        ], $this->authHeaders($this->actor))
            ->assertStatus(201)
            ->assertJsonStructure(['id', 'status', 'dose', 'frequencyHours', 'startDate', 'createdAt', 'product']);
    }

    public function test_store_returns_401_without_auth(): void
    {
        $this->postJson('/api/profiles/' . Str::uuid() . '/treatments', [])->assertStatus(401);
    }

    public function test_store_returns_404_when_profile_not_found(): void
    {
        $item = $this->createItem('continuous', 10, $this->actor);
        $this->actor->load('house');

        $this->postJson('/api/profiles/' . Str::uuid() . '/treatments', [
            'productId' => $item->product->public_id,
            'dose' => 1.0,
            'frequencyHours' => 8,
            'startDate' => now()->toIso8601ZuluString('millisecond'),
        ], $this->authHeaders($this->actor))
            ->assertStatus(404);
    }

    // ── POST /api/profiles/{profileId}/treatments ─────────────────────────

    public function test_store_returns_422_on_validation_failure(): void
    {
        $this->createItem('continuous', 10, $this->actor);
        $this->actor->load('house');

        $this->postJson('/api/profiles/' . Str::uuid() . '/treatments', [
            'productId' => 'not-a-uuid',
        ], $this->authHeaders($this->actor))
            ->assertStatus(422);
    }

    public function test_index_returns_treatments_for_profile(): void
    {
        $treatment = $this->createTreatment();

        $this->getJson("/api/profiles/{$treatment->profile->public_id}/treatments", $this->authHeaders($this->actor))
            ->assertStatus(200)
            ->assertJsonFragment(['id' => $treatment->public_id]);
    }

    private function createTreatment(string $status = 'active'): TreatmentModel
    {
        $profile = $this->createProfile();
        $item = $this->createItem();

        return TreatmentModel::create([
            'public_id' => Str::uuid(),
            'profile_id' => $profile->id,
            'product_id' => $item->product_id,
            'status' => $status,
            'dose' => 1.0,
            'frequency_hours' => 8,
            'start_date' => now()->toIso8601ZuluString('millisecond'),
            'days' => null,
        ]);
    }

    // ── GET /api/treatments ────────────────────────────────────────────────

    public function test_show_returns_treatment_detail(): void
    {
        $treatment = $this->createTreatment();

        $this->getJson("/api/treatments/{$treatment->public_id}", $this->authHeaders($this->actor))
            ->assertStatus(200)
            ->assertJsonFragment(['id' => $treatment->public_id]);
    }

    // ── GET /api/treatments/{id} ───────────────────────────────────────────

    public function test_show_returns_404_when_not_found(): void
    {
        $this->getJson('/api/treatments/non-existent-id', $this->authHeaders($this->actor))
            ->assertStatus(404);
    }

    public function test_update_returns_updated_treatment(): void
    {
        $treatment = $this->createTreatment();

        $this->patchJson("/api/treatments/{$treatment->public_id}", [
            'dose' => 2.0,
            'frequencyHours' => 12,
            'status' => 'paused',
        ], $this->authHeaders($this->actor))
            ->assertStatus(200)
            ->assertJsonFragment(['dose' => 2.0]);
    }

    // ── PATCH /api/treatments/{id} (update fields + status transitions) ─────

    public function test_patch_paused_transitions_active_treatment(): void
    {
        $treatment = $this->createTreatment('active');

        $this->patchJson("/api/treatments/{$treatment->public_id}", ['status' => 'paused'], $this->authHeaders($this->actor))
            ->assertStatus(200)
            ->assertJsonFragment(['status' => 'paused']);
    }

    // ── POST /api/treatments/{id}/pause ───────────────────────────────────

    // ── PATCH /api/treatments/{id} (status transitions) ──────────────────────

    public function test_patch_paused_returns_400_when_already_paused(): void
    {
        $treatment = $this->createTreatment('paused');

        $this->patchJson("/api/treatments/{$treatment->public_id}", ['status' => 'paused'], $this->authHeaders($this->actor))
            ->assertStatus(400);
    }

    public function test_patch_active_resumes_paused_treatment(): void
    {
        $treatment = $this->createTreatment('paused');

        $this->patchJson("/api/treatments/{$treatment->public_id}", ['status' => 'active'], $this->authHeaders($this->actor))
            ->assertStatus(200)
            ->assertJsonFragment(['status' => 'active']);
    }

    public function test_patch_cancelled_cancels_active_treatment(): void
    {
        $treatment = $this->createTreatment('active');

        $this->patchJson("/api/treatments/{$treatment->public_id}", ['status' => 'cancelled'], $this->authHeaders($this->actor))
            ->assertStatus(200)
            ->assertJsonFragment(['status' => 'cancelled']);
    }

    public function test_patch_completed_completes_active_treatment(): void
    {
        $treatment = $this->createTreatment('active');

        $this->patchJson("/api/treatments/{$treatment->public_id}", ['status' => 'completed'], $this->authHeaders($this->actor))
            ->assertStatus(200)
            ->assertJsonFragment(['status' => 'completed']);
    }

    public function test_store_dose_registers_dose_and_returns_201(): void
    {
        // The actor must own the house so their JWT token has the correct house_id claim
        $profile = $this->createProfile();
        $item = $this->createItem('continuous', 10, $this->actor);

        $this->actor->load('house');

        $treatment = TreatmentModel::create([
            'public_id' => Str::uuid(),
            'profile_id' => $profile->id,
            'product_id' => $item->product_id,
            'status' => 'active',
            'dose' => 1.0,
            'frequency_hours' => 8,
            'start_date' => now()->toIso8601ZuluString('millisecond'),
            'days' => null,
        ]);

        // Re-issue token so it includes the house_id claim set above
        $this->postJson("/api/treatments/{$treatment->public_id}/consumptions", [
            'itemId' => $item->public_id,
            'amount' => 1.5,
        ], $this->authHeaders($this->actor))
            ->assertStatus(201);
    }

    // ── POST /api/treatments/{id}/consumptions ────────────────────────────

    public function test_store_dose_returns_400_when_treatment_not_active(): void
    {
        $profile = $this->createProfile();
        $item = $this->createItem('continuous', 10, $this->actor);

        $this->actor->load('house');

        $treatment = TreatmentModel::create([
            'public_id' => Str::uuid(),
            'profile_id' => $profile->id,
            'product_id' => $item->product_id,
            'status' => 'paused',
            'dose' => 1.0,
            'frequency_hours' => 8,
            'start_date' => now()->toIso8601ZuluString('millisecond'),
            'days' => null,
        ]);

        $this->postJson("/api/treatments/{$treatment->public_id}/consumptions", [
            'itemId' => $item->public_id,
            'amount' => 1.5,
        ], $this->authHeaders($this->actor))
            ->assertStatus(400);
    }

    public function test_store_dose_returns_400_when_item_does_not_belong_to_product(): void
    {
        $profile = $this->createProfile();
        $item = $this->createItem('continuous', 10, $this->actor);

        $this->actor->load('house');

        $treatment = TreatmentModel::create([
            'public_id' => Str::uuid(),
            'profile_id' => $profile->id,
            'product_id' => $item->product_id,
            'status' => 'active',
            'dose' => 1.0,
            'frequency_hours' => 8,
            'start_date' => now()->toIso8601ZuluString('millisecond'),
            'days' => null,
        ]);

        // Create a different product and an item in the SAME storage (same house)
        // so findByIdAndHouseId succeeds but the product IDs differ → 400
        $anotherPharmaForm = PharmaceuticalFormModel::create([
            'name' => 'OtherForm ' . Str::random(4),
            'consumption_type' => 'continuous',
        ]);
        $anotherProduct = ProductModel::create([
            'public_id' => Str::uuid(),
            'name' => 'OtherMed ' . Str::random(4),
            'pharmaceutical_form_id' => $anotherPharmaForm->id,
            'net_content_value' => 50,
            'net_content_unit' => 'ml',
            'total_quantity' => null,
            'composition_reference_amount' => 50,
        ]);
        $otherItem = ItemModel::create([
            'public_id' => Str::uuid(),
            'product_id' => $anotherProduct->id,
            'storage_id' => $item->storage_id,
            'total_content' => 10,
            'expiration_date' => now()->addYear(),
        ]);

        $this->postJson("/api/treatments/{$treatment->public_id}/consumptions", [
            'itemId' => $otherItem->public_id,
            'amount' => 1.5,
        ], $this->authHeaders($this->actor))
            ->assertStatus(400);
    }

    public function test_index_doses_returns_200(): void
    {
        $treatment = $this->createTreatment();

        $this->getJson("/api/treatments/{$treatment->public_id}/consumptions", $this->authHeaders($this->actor))
            ->assertStatus(200);
    }

    // ── GET /api/treatments/{id}/consumptions ─────────────────────────────

    public function test_qr_returns_png_image_for_existing_treatment(): void
    {
        $treatment = $this->createTreatment();

        $response = $this->getJson(
            "/api/treatments/$treatment->public_id/qr",
            $this->authHeaders($this->actor)
        );

        // The endpoint returns image/png — Laravel's getJson still fires the request,
        // but the content-type will be image/png, not application/json.
        $response->assertStatus(200);
        $this->assertStringContainsString('image/svg+xml', $response->headers->get('Content-Type'));
    }

    // ── GET /api/treatments/{id}/qr ───────────────────────────────────────

    public function test_qr_returns_404_for_missing_treatment(): void
    {
        $this->getJson(
            '/api/treatments/non-existent-uuid/qr',
            $this->authHeaders($this->actor)
        )->assertStatus(404);
    }

    public function test_qr_returns_401_without_auth(): void
    {
        $this->getJson('/api/treatments/some-uuid/qr')
            ->assertStatus(401);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->actor = User::factory()->create();
    }
}
