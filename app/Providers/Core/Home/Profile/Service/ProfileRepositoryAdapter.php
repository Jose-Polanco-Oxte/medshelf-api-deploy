<?php

namespace App\Providers\Core\Home\Profile\Service;

use App\Core\Home\Profile\Model\Profile;
use App\Core\Home\Profile\Model\Repository\ProfileRepository;
use App\Models\ProfileModel;
use App\Models\User;
use App\Providers\Core\InfrastructureException;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProfileRepositoryAdapter implements ProfileRepository
{
    public function save(Profile $profile): void
    {
        $userInternalId = User::where('public_id', $profile->getUserId())->value('id')
            ?? throw new InfrastructureException(sprintf('User with id %s not found', $profile->getUserId()));

        try {
            DB::transaction(function () use ($profile, $userInternalId) {
                $record = ProfileModel::updateOrCreate(
                    ['public_id' => $profile->getId()],
                    [
                        'user_id' => $userInternalId,
                        'name' => $profile->getName(),
                        'relationship' => $profile->getRelationship(),
                        'birthdate' => $profile->getBirthDate()->toIso8601ZuluString('millisecond'),
                    ]
                );

                $record->allergies()->delete();

                $allergies = array_map(
                    fn(string $name) => ['name' => $name],
                    $profile->getAllergies()
                );

                if (count($allergies) > 0) {
                    $record->allergies()->createMany($allergies);
                }
            });
        } catch (Throwable $e) {
            throw new InfrastructureException('Failed to save profile', previous: $e);
        }
    }

    public function findById(string $id): ?Profile
    {
        $record = ProfileModel::with([
            'user' => fn($q) => $q->select('id', 'public_id'),
            'allergies' => fn($q) => $q->select('id', 'profile_id', 'name'),
        ])
            ->where('public_id', $id)
            ->first();

        if (!$record) return null;

        return $this->toDomain($record);
    }

    private function toDomain(ProfileModel $record): Profile
    {
        return Profile::load(
            id: $record->public_id,
            userId: $record->user->public_id,
            name: $record->name,
            relationship: $record->relationship,
            birthDate: $record->birthdate,
            allergies: $record->allergies->pluck('name')->toArray(),
            createdAt: $record->created_at,
        );
    }

    public function remove(Profile $profile): void
    {
        ProfileModel::where('public_id', $profile->getId())->delete();
    }
}
