<?php

namespace App\Core\Home\Profile\Model;

use App\Core\Shared\Domain\Utils;
use Carbon\Carbon;

final class Profile
{
    private function __construct(
        protected string  $id,
        protected string  $userId,
        protected string  $name,
        protected ?string $relationship,
        protected Carbon  $birthDate,
        /** @var string[] */
        protected array   $allergies,
        protected Carbon  $createdAt,
    )
    {
    }

    /** @param string[] $allergies */
    public static function create(string $userId, string $name, ?string $relationship, Carbon $birthDate, array $allergies): Profile
    {
        return new self(Utils::generateUUIDV4(), $userId, $name, $relationship, $birthDate, $allergies, Carbon::now());
    }

    public static function load(
        string  $id,
        string  $userId,
        string  $name,
        ?string $relationship,
        Carbon  $birthDate,
        array   $allergies,
        Carbon  $createdAt
    ): Profile
    {
        return new self($id, $userId, $name, $relationship, $birthDate, $allergies, $createdAt);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRelationship(): ?string
    {
        return $this->relationship;
    }

    public function getBirthDate(): Carbon
    {
        return $this->birthDate;
    }

    /** @return string[] */
    public function getAllergies(): array
    {
        return $this->allergies;
    }

    public function getCreatedAt(): Carbon
    {
        return $this->createdAt;
    }

    /**
     * Partially update the profile — only fields that are non-null are changed.
     */
    public function update(?string $name, ?string $relationship): void
    {
        if ($name !== null) {
            $this->name = $name;
        }

        if ($relationship !== null) {
            $this->relationship = $relationship;
        }
    }
}
