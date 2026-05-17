<?php

namespace App\Core\Home\Profile\Application\UseCase;

use App\Core\Home\Profile\Application\Exception\ProfileNotFound;
use App\Core\Home\Profile\Model\Repository\ProfileRepository;

final readonly class DeleteProfile
{
    public function __construct(
        private ProfileRepository $profileRepository,
    )
    {
    }

    public function execute(string $profileId): void
    {
        $profile = $this->profileRepository->findById($profileId)
            ?? throw new ProfileNotFound($profileId);
        $this->profileRepository->delete($profile);
    }
}