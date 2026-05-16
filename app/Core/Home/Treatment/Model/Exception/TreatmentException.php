<?php

namespace App\Core\Home\Treatment\Model\Exception;

use App\Core\Home\Item\Model\Exception\ConsumptionException;
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

    public static function discreteDoseMustBeInteger(float $dose): self
    {
        return new self(sprintf(
            'Dose %s is invalid for a discrete product. Dose must be an integer.',
            $dose
        ));
    }

    public static function cannotConsumeDose(ConsumptionException $e): self
    {
        return new self(sprintf(
            'Cannot register dose for treatment due to consumption error: %s',
            $e->getMessage()
        ));
    }

    public static function itemDoesNotBelongToProduct(string $itemId, string $productId): self
    {
        return new self(sprintf(
            'Item %s does not belong to the product %s linked to this treatment.',
            $itemId,
            $productId
        ));
    }
}
