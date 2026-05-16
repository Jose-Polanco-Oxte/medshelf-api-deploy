<?php

namespace Tests\Unit\Treatment;

use App\Core\Home\Item\Model\Product;
use App\Core\Home\Item\Model\Repository\ProductRepository;
use App\Core\Home\Item\Model\ValueObject\ConsumptionType;
use App\Core\Home\Profile\Application\Exception\ProfileNotFound;
use App\Core\Home\Profile\Model\Profile;
use App\Core\Home\Profile\Model\Repository\ProfileRepository;
use App\Core\Home\Treatment\Application\Dto\Request\CreateTreatmentRequest;
use App\Core\Home\Treatment\Application\UseCase\CreateTreatment;
use App\Core\Home\Treatment\Model\Repository\TreatmentRepository;
use App\Core\Home\Treatment\Model\Treatment;
use App\Core\Home\Treatment\Model\TreatmentStatus;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class AddTreatmentTest extends TestCase
{
    private ProfileRepository $profileRepository;
    private TreatmentRepository $treatmentRepository;
    private ProductRepository $productRepository;
    private CreateTreatment $useCase;

    public function test_execute_creates_active_treatment(): void
    {
        $this->setupRepositories();

        $this->treatmentRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(fn(Treatment $t) => $t->getStatus() === TreatmentStatus::ACTIVE));

        $response = $this->useCase->execute($this->makeRequest());

        $this->assertEquals('active', $response->status);
    }

    private function setupRepositories(): void
    {
        $this->profileRepository->method('findById')->willReturn($this->makeProfile());
        $this->productRepository->method('findById')->willReturn($this->makeProduct());
    }

    private function makeProfile(): Profile
    {
        return Profile::load(
            id: 'profile-uuid',
            userId: 'user-uuid',
            name: 'Maria',
            relationship: null,
            birthDate: Carbon::parse('1990-01-01'),
            allergies: [],
            createdAt: Carbon::now(),
        );
    }

    private function makeProduct(): Product
    {
        return new Product(
            id: 'product-uuid',
            contentValue: null,
            totalQuantity: null,
            consumptionType: ConsumptionType::CONTINUOUS,
        );
    }

    private function makeRequest(array $overrides = []): CreateTreatmentRequest
    {
        return new CreateTreatmentRequest(
            profileId: $overrides['profileId'] ?? 'profile-uuid',
            productId: $overrides['productId'] ?? 'product-uuid',
            dose: $overrides['dose'] ?? 1.0,
            frequencyHours: $overrides['frequencyHours'] ?? 8,
            startDate: $overrides['startDate'] ?? '2026-01-01',
            days: $overrides['days'] ?? 5,
        );
    }

    public function test_execute_returns_response_with_correct_frequency_hours(): void
    {
        $this->setupRepositories();
        $this->treatmentRepository->expects($this->once())->method('save');

        $response = $this->useCase->execute($this->makeRequest(['frequencyHours' => 12]));

        $this->assertEquals(12, $response->frequencyHours);
    }

    public function test_execute_throws_profile_not_found_when_profile_missing(): void
    {
        $this->expectException(ProfileNotFound::class);

        $this->profileRepository->method('findById')->willReturn(null);
        $this->treatmentRepository->expects($this->never())->method('save');

        $this->useCase->execute($this->makeRequest());
    }

    public function test_execute_passes_correct_product_id_to_treatment(): void
    {
        $capturedTreatment = null;

        $this->profileRepository->method('findById')->willReturn($this->makeProfile());
        $this->productRepository->method('findById')->willReturn($this->makeProduct());
        $this->treatmentRepository
            ->expects($this->once())
            ->method('save')
            ->willReturnCallback(function (Treatment $t) use (&$capturedTreatment) {
                $capturedTreatment = $t;
            });

        $this->useCase->execute($this->makeRequest(['productId' => 'product-uuid']));

        $this->assertNotNull($capturedTreatment);
        $this->assertEquals('product-uuid', $capturedTreatment->getProductId());
    }

    protected function setUp(): void
    {
        $this->profileRepository = $this->createMock(ProfileRepository::class);
        $this->treatmentRepository = $this->createMock(TreatmentRepository::class);
        $this->productRepository = $this->createMock(ProductRepository::class);
        $this->useCase = new CreateTreatment(
            $this->profileRepository,
            $this->treatmentRepository,
            $this->productRepository,
        );
    }
}

