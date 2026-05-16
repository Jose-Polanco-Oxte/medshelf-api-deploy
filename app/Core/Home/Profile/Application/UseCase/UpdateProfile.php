<?php

namespace App\Core\Home\Profile\Application\UseCase;

use App\Core\Home\Profile\Application\Dto\Request\UpdateProfileRequest;
use App\Core\Home\Profile\Application\Dto\Response\ProfileResponse;
use App\Core\Home\Profile\Application\Exception\ProfileNotFound;
use App\Core\Home\Profile\Application\Mapping\ProfileMapper;
use App\Core\Home\Profile\Model\Repository\ProfileRepository;

final readonly class UpdateProfile
{
    public function __construct(
        private ProfileRepository $profileRepository,
    )
    {
    }

    public function execute(UpdateProfileRequest $request): ProfileResponse
    {
        $profile = $this->profileRepository->findById($request->profileId)
            ?? throw new ProfileNotFound("Profile with id {$request->profileId} not found");

        $profile->update($request->name, $request->relationship);

        $this->profileRepository->save($profile);

        return ProfileMapper::toResponse($profile);
    }
}
