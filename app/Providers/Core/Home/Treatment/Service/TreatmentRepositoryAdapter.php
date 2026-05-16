<?php

namespace App\Providers\Core\Home\Treatment\Service;

use App\Core\Home\Treatment\Model\Repository\TreatmentRepository;
use App\Core\Home\Treatment\Model\Treatment;
use App\Core\Home\Treatment\Model\TreatmentStatus;
use App\Models\ItemModel;
use App\Models\ProfileModel;
use App\Models\TreatmentModel;
use App\Providers\Core\InfrastructureException;

class TreatmentRepositoryAdapter implements TreatmentRepository
{
    public function save(Treatment $treatment): void
    {
        $profileInternalId = ProfileModel::where('public_id', $treatment->getProfileId())->value('id')
            ?? throw new InfrastructureException(sprintf('Profile with id %s not found', $treatment->getProfileId()));

        $itemInternalId = ItemModel::where('public_id', $treatment->getItemId())->value('id')
            ?? throw new InfrastructureException(sprintf('Item with id %s not found', $treatment->getItemId()));

        TreatmentModel::updateOrCreate(
            ['public_id' => $treatment->getId()],
            [
                'profile_id' => $profileInternalId,
                'item_id' => $itemInternalId,
                'status' => $treatment->getStatus()->value,
                'dose' => $treatment->getDose(),
                'frequency_unit' => $treatment->getFrequencyUnit(),
                'start_date' => $treatment->getStartDate(),
                'end_date' => $treatment->getEndDate(),
            ]
        );
    }

    public function findById(string $id): ?Treatment
    {
        $record = TreatmentModel::with([
            'profile' => fn($q) => $q->select('id', 'public_id'),
            'item' => fn($q) => $q->select('id', 'public_id'),
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
            itemId: $record->item->public_id,
            status: TreatmentStatus::from($record->status),
            dose: $record->dose,
            frequencyUnit: $record->frequency_unit,
            startDate: $record->start_date,
            endDate: $record->end_date,
            createdAt: $record->created_at,
        );
    }

    public function remove(Treatment $treatment): void
    {
        TreatmentModel::where('public_id', $treatment->getId())->delete();
    }
}
