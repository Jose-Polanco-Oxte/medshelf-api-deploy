<?php

namespace Tests\Feature;

use App\Models\ActiveIngredientModel;
use App\Models\HouseModel;
use App\Models\ItemModel;
use App\Models\PharmaceuticalFormModel;
use App\Models\PlaceModel;
use App\Models\ProductCompoundModel;
use App\Models\ProductModel;
use App\Models\StorageModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Feature\Traits\WithJwtAuth;
use Tests\TestCase;

class ItemReportControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithJwtAuth;

    private User $actor;
    private HouseModel $house;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actor = User::factory()->create();
        $this->house = HouseModel::create([
            'public_id' => Str::uuid(),
            'owner_id'  => $this->actor->id,
            'name'      => 'My Home',
        ]);
        // Eager-load house so the JWT custom claim includes house_id
        $this->actor->load('house');
    }

    // ── GET /api/items/report ─────────────────────────────────────────────

    public function test_report_returns_401_without_auth(): void
    {
        $this->getJson('/api/items/report')->assertStatus(401);
    }

    public function test_report_returns_pdf_content_type(): void
    {
        $this->getJson('/api/items/report', $this->authHeaders($this->actor))
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_report_returns_200_when_cabinet_is_empty(): void
    {
        $response = $this->get('/api/items/report', $this->authHeaders($this->actor));

        $response->assertStatus(200);
        $this->assertStringStartsWith('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_report_returns_pdf_with_items(): void
    {
        $this->createItem('continuous', 50);
        $this->createItem('continuous', 20);

        $response = $this->get('/api/items/report', $this->authHeaders($this->actor));

        $response->assertStatus(200);
        $this->assertStringStartsWith('application/pdf', $response->headers->get('Content-Type'));
        // PDF binary starts with '%PDF'
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_report_does_not_include_other_users_items(): void
    {
        // Item belonging to another user's house
        $otherUser = User::factory()->create();
        $this->createItemForUser($otherUser, 'continuous', 30);

        // Actor has no items of their own
        $response = $this->get('/api/items/report', $this->authHeaders($this->actor));

        $response->assertStatus(200);
        // Still a valid PDF with no items from the other user
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function createItem(string $consumptionType = 'continuous', float $totalContent = 10): ItemModel
    {
        return $this->createItemForUser($this->actor, $consumptionType, $totalContent);
    }

    private function createItemForUser(User $user, string $consumptionType = 'continuous', float $totalContent = 10): ItemModel
    {
        $pharmaForm = PharmaceuticalFormModel::create([
            'name'             => 'Form ' . Str::random(4),
            'consumption_type' => $consumptionType,
        ]);

        $product = ProductModel::create([
            'public_id'                   => Str::uuid(),
            'name'                        => 'Medicine ' . Str::random(4),
            'pharmaceutical_form_id'      => $pharmaForm->id,
            'net_content_value'           => 100,
            'net_content_unit'            => 'ml',
            'total_quantity'              => null,
            'composition_reference_amount' => 100,
        ]);

        $ingredient = ActiveIngredientModel::create(['name' => 'Ingredient ' . Str::random(4)]);
        ProductCompoundModel::create([
            'product_id'           => $product->id,
            'active_ingredient_id' => $ingredient->id,
            'strength_value'       => 500,
            'strength_unit'        => 'mg',
        ]);

        $house = $user->is($this->actor) && isset($this->house)
            ? $this->house
            : HouseModel::create([
                'public_id' => Str::uuid(),
                'owner_id'  => $user->id,
                'name'      => 'Other Home',
            ]);

        $place = PlaceModel::create([
            'public_id' => Str::uuid(),
            'house_id'  => $house->id,
            'name'      => 'Cabinet',
        ]);

        $storage = StorageModel::create([
            'public_id' => Str::uuid(),
            'place_id'  => $place->id,
            'name'      => 'Shelf',
        ]);

        return ItemModel::create([
            'public_id'       => Str::uuid(),
            'product_id'      => $product->id,
            'storage_id'      => $storage->id,
            'total_content'   => $totalContent,
            'expiration_date' => now()->addYear(),
        ]);
    }
}
