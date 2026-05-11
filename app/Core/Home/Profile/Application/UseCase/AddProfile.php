<?php

namespace App\Core\Home\Profile\Application\UseCase;

use App\Core\Home\Profile\Application\Dto\Request\AddProfileRequest;
use App\Core\Home\Profile\Application\Dto\Response\ProfileResponse;
use App\Core\Home\Profile\Application\Mapping\ProfileMapper;
use App\Core\Home\Profile\Model\Profile;
use App\Core\Home\Profile\Model\Repository\ProfileRepository;

final readonly class AddProfile
{
    public function __construct(
        private ProfileRepository $profileRepository,
    )
    {
    }

    public function execute(AddProfileRequest $request): ProfileResponse
    {
        $profile = Profile::create(
            userId: $request->userId,
            name: $request->name,
            relationship: $request->relationship,
        );

        $this->profileRepository->save($profile);

        return ProfileMapper::toResponse($profile);
    }
}
