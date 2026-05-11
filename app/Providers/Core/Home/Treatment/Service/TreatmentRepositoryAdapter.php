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
                'profile_id'      => $profileInternalId,
                'item_id'         => $itemInternalId,
                'status'          => $treatment->getStatus()->value,
                'frequency_value' => $treatment->getFrequencyValue(),
                'frequency_unit'  => $treatment->getFrequencyUnit(),
                'dose_quantity'   => $treatment->getDoseQuantity(),
                'start_date'      => $treatment->getStartDate(),
                'end_date'        => $treatment->getEndDate(),
            ]
        );
    }

    public function findById(string $id): ?Treatment
    {
        $record = TreatmentModel::with([
            'profile' => fn($q) => $q->select('id', 'public_id'),
            'item'    => fn($q) => $q->select('id', 'public_id'),
        ])
            ->where('public_id', $id)
            ->first();

        if (!$record) return null;

        return $this->toDomain($record);
    }

    public function remove(Treatment $treatment): void
    {
        TreatmentModel::where('public_id', $treatment->getId())->delete();
    }

    private function toDomain(TreatmentModel $record): Treatment
    {
        return Treatment::load(
            id: $record->public_id,
            profileId: $record->profile->public_id,
            itemId: $record->item->public_id,
            status: TreatmentStatus::from($record->status),
            frequencyValue: $record->frequency_value,
            frequencyUnit: $record->frequency_unit,
            doseQuantity: $record->dose_quantity,
            startDate: $record->start_date,
            endDate: $record->end_date,
            createdAt: $record->created_at,
        );
    }
}
