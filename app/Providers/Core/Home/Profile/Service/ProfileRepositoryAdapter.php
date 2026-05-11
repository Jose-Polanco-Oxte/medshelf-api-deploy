<?php

namespace App\Providers\Core\Home\Profile\Service;

use App\Core\Home\Profile\Model\Profile;
use App\Core\Home\Profile\Model\Repository\ProfileRepository;
use App\Models\ProfileModel;
use App\Models\User;
use App\Providers\Core\InfrastructureException;

class ProfileRepositoryAdapter implements ProfileRepository
{
    public function save(Profile $profile): void
    {
        $userInternalId = User::where('public_id', $profile->getUserId())->value('id')
            ?? throw new InfrastructureException(sprintf('User with id %s not found', $profile->getUserId()));

        ProfileModel::updateOrCreate(
            ['public_id' => $profile->getId()],
            [
                'user_id'      => $userInternalId,
                'name'         => $profile->getName(),
                'relationship' => $profile->getRelationship(),
            ]
        );
    }

    public function findById(string $id): ?Profile
    {
        $record = ProfileModel::with(['user' => fn($q) => $q->select('id', 'public_id')])
            ->where('public_id', $id)
            ->first();

        if (!$record) return null;

        return $this->toDomain($record);
    }

    public function remove(Profile $profile): void
    {
        ProfileModel::where('public_id', $profile->getId())->delete();
    }

    private function toDomain(ProfileModel $record): Profile
    {
        return Profile::load(
            id: $record->public_id,
            userId: $record->user->public_id,
            name: $record->name,
            relationship: $record->relationship,
            createdAt: $record->created_at,
        );
    }
}
