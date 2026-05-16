<?php

namespace App\Providers\Core\Home\House\Service;

use App\Models\HouseModel;
use App\Providers\Core\Home\House\Detail\HouseDetail;
use App\Providers\Core\Home\House\Resume\OwnerResume;

class HouseFinder
{

    public function findById(string $id): ?HouseDetail
    {
        $record = HouseModel::with(['owner' => fn($q) => $q->select('id', 'public_id', 'name')])
            ->where('public_id', $id)
            ->first();
        if (!$record) return null;
        return $this->toDetail($record);
    }

    private function toDetail(HouseModel $record): HouseDetail
    {
        $owner = $record->owner;
        return new HouseDetail(
            id: $record->public_id,
            owner: new OwnerResume(
                id: $owner->public_id,
                name: $owner->name,
            ),
            name: $record->name,
            createdAt: $record->created_at->toIso8601ZuluString('millisecond'),
        );
    }
}
