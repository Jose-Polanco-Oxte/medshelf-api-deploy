<?php

namespace App\Core\Home\Profile\Application\Mapping;

use App\Core\Home\Profile\Application\Dto\Response\ProfileResponse;
use App\Core\Home\Profile\Model\Profile;

final class ProfileMapper
{
    private function __construct()
    {
    }

    public static function toResponse(Profile $profile): ProfileResponse
    {
        return new ProfileResponse(
            id: $profile->getId(),
            name: $profile->getName(),
            relationship: $profile->getRelationship(),
            birthDate: $profile->getBirthDate(),
            allergies: $profile->getAllergies(),
            createdAt: $profile->getCreatedAt(),
        );
    }
}
