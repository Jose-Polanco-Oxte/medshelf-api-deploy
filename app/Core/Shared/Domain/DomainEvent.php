<?php

namespace App\Core\Shared\Domain;

use Carbon\Carbon;

readonly abstract class DomainEvent
{
    private Carbon $occurredAt;

    public function __construct()
    {
        $this->occurredAt = Carbon::now();
    }

    public function getOccurredAt(): Carbon
    {
        return $this->occurredAt;
    }
}