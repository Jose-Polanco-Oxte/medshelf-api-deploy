<?php

namespace App\Providers\Core\Home\Treatment\Service;

use App\Models\TreatmentModel;

final readonly class CheckCompletedTreatments
{
    public function __construct()
    {
    }

    public function execute(): void
    {
        $completedTreatments = TreatmentModel::where('status', 'active')
            ->where('end_date', '<=', now())
            ->get();
        foreach ($completedTreatments as $completedTreatment) {
            $completedTreatment->update(['status' => 'completed']);
        }
    }
}