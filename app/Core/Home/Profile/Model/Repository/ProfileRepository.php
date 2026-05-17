<?php

namespace App\Core\Home\Profile\Model\Repository;

use App\Core\Home\Profile\Model\Profile;

interface ProfileRepository
{
    public function save(Profile $profile): void;

    public function findById(string $id): ?Profile;

    public function delete(Profile $profile): void;
}
