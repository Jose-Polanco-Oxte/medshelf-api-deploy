<?php

namespace App\Core\Home\Treatment\Model\Repository;

use App\Core\Home\Treatment\Model\Treatment;

interface TreatmentRepository
{
    public function save(Treatment $treatment): void;

    public function findById(string $id): ?Treatment;

    public function remove(Treatment $treatment): void;
}
