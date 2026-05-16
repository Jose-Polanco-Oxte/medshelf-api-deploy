<?php

namespace App\Providers\Core\Home\Treatment\Service;

use App\Core\Home\Treatment\Model\Repository\TreatmentRepository;
use App\Core\Home\Treatment\Model\Treatment;
use App\Core\Home\Treatment\Model\TreatmentStatus;
use App\Models\ProductModel;
use App\Models\ProfileModel;
use App\Models\TreatmentModel;
use App\Providers\Core\InfrastructureException;

class TreatmentRepositoryAdapter implements TreatmentRepository
{
    public function save(Treatment $treatment): void
    {
        $profileInternalId = ProfileModel::where('public_id', $treatment->getProfileId())->value('id')
            ?? throw new InfrastructureException(sprintf('Profile with id %s not found', $treatment->getProfileId()));

        $productInternalId = ProductModel::where('public_id', $treatment->getProductId())->value('id')
            ?? throw new InfrastructureException(sprintf('Product with id %s not found', $treatment->getProductId()));

        TreatmentModel::updateOrCreate(
            ['public_id' => $treatment->getId()],
            [
                'profile_id' => $profileInternalId,
                'product_id' => $productInternalId,
                'status' => $treatment->getStatus()->value,
                'dose' => $treatment->getDose(),
                'frequency_hours' => $treatment->getFrequencyHours(),
                'start_date' => $treatment->getStartDate(),
                'days' => $treatment->getDays(),
            ]
        );
    }

    public function findById(string $id): ?Treatment
    {
        $record = TreatmentModel::with([
            'profile' => fn($q) => $q->select('id', 'public_id'),
            'product' => fn($q) => $q->select('id', 'public_id'),
        ])
            ->where('public_id', $id)
            ->first();

        if (!$record) return null;

        return $this->toDomain($record);
    }

    private function toDomain(TreatmentModel $record): Treatment
    {
        return Treatment::load(
            id: $record->public_id,
            profileId: $record->profile->public_id,
            productId: $record->product->public_id,
            status: TreatmentStatus::from($record->status),
            dose: $record->dose,
            frequencyHours: $record->frequency_hours,
            startDate: $record->start_date,
            days: $record->days,
            createdAt: $record->created_at,
        );
    }

    public function remove(Treatment $treatment): void
    {
        TreatmentModel::where('public_id', $treatment->getId())->delete();
    }
}
