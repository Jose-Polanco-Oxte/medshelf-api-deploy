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
            userId: $profile->getUserId(),
            name: $profile->getName(),
            relationship: $profile->getRelationship(),
            createdAt: $profile->getCreatedAt(),
        );
    }
}
