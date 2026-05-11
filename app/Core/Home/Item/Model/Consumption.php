<?php

namespace App\Core\Home\Item\Model;

use App\Core\Home\Item\Model\Event\ItemConsumed;
use App\Core\Shared\Domain\DomainEvent;
use App\Core\Shared\Domain\Utils;
use Carbon\Carbon;

final class Consumption
{

    /* @var array<int, DomainEvent> */
    private array $events = [];

    private function __construct(
        protected string  $id,
        protected string  $itemId,
        protected float   $amount,
        protected Carbon  $consumedAt,
        protected ?string $treatmentId = null,
    )
    {
    }

    public static function create(string $itemId, float $amount, ?string $treatmentId = null): Consumption
    {
        $consumption = new self(
            Utils::generateUUIDV4(),
            $itemId,
            $amount,
            Carbon::now(),
            $treatmentId,
        );
        $consumption->addEvent(
            new ItemConsumed(
                itemId: $itemId,
                amount: $amount
            )
        );
        return $consumption;
    }

    public function addEvent(DomainEvent $event): void
    {
        $this->events[] = $event;
    }

    public static function load(string $id, string $itemId, float $amount, Carbon $consumedAt, ?string $treatmentId = null): Consumption
    {
        return new self(
            $id,
            $itemId,
            $amount,
            $consumedAt,
            $treatmentId,
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getItemId(): string
    {
        return $this->itemId;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getConsumedAt(): Carbon
    {
        return $this->consumedAt;
    }

    public function getTreatmentId(): ?string
    {
        return $this->treatmentId;
    }

    /**
     * @return array<int, DomainEvent>
     */
    public function pullEvents(): array
    {
        $events = $this->events;
        $this->events = [];
        return $events;
    }
}
