<?php

namespace App\Core\Home\Treatment\Model\Exception;

use App\Core\Shared\Domain\DomainException;

class TreatmentException extends DomainException
{
    public static function cannotRegisterDose(string $treatmentId, string $status): self
    {
        return new self(sprintf(
            'Cannot register a dose for treatment %s with status "%s". Treatment must be active.',
            $treatmentId,
            $status
        ));
    }

    public static function cannotPause(string $treatmentId): self
    {
        return new self(sprintf(
            'Cannot pause treatment %s. Only active treatments can be paused.',
            $treatmentId
        ));
    }

    public static function cannotResume(string $treatmentId): self
    {
        return new self(sprintf(
            'Cannot resume treatment %s. Only paused treatments can be resumed.',
            $treatmentId
        ));
    }

    public static function cannotComplete(string $treatmentId): self
    {
        return new self(sprintf(
            'Cannot complete treatment %s. Only active treatments can be completed.',
            $treatmentId
        ));
    }

    public static function cannotCancel(string $treatmentId): self
    {
        return new self(sprintf(
            'Cannot cancel treatment %s. Only active or paused treatments can be cancelled.',
            $treatmentId
        ));
    }
}
