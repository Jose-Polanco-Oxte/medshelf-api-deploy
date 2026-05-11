<?php

namespace App\Providers\Core\Home\Treatment;

use App\Core\Home\Treatment\Model\Repository\TreatmentRepository;
use App\Providers\Core\Home\Treatment\Service\TreatmentRepositoryAdapter;
use App\Providers\CoreProvider;

class TreatmentServiceProvider extends CoreProvider
{
    protected function registerRepositories(): void
    {
        $this->app->singleton(TreatmentRepository::class, TreatmentRepositoryAdapter::class);
    }
}
