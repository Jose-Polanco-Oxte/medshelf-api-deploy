<?php

namespace App\Core\Home\Treatment\Model;

enum TreatmentStatus: string
{
    case ACTIVE    = 'active';
    case PAUSED    = 'paused';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
}
