<?php

namespace App\Providers\Core\Home\Profile;

use App\Core\Home\Profile\Model\Repository\ProfileRepository;
use App\Providers\Core\Home\Profile\Service\ProfileRepositoryAdapter;
use App\Providers\CoreProvider;

class ProfileServiceProvider extends CoreProvider
{
    protected function registerRepositories(): void
    {
        $this->app->singleton(ProfileRepository::class, ProfileRepositoryAdapter::class);
    }
}
