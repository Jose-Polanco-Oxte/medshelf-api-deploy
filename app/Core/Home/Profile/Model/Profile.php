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
        protected Carbon  $createdAt,
    )
    {
    }

    public static function create(string $userId, string $name, ?string $relationship): Profile
    {
        return new self(Utils::generateUUIDV4(), $userId, $name, $relationship, Carbon::now());
    }

    public static function load(
        string  $id,
        string  $userId,
        string  $name,
        ?string $relationship,
        Carbon  $createdAt
    ): Profile
    {
        return new self($id, $userId, $name, $relationship, $createdAt);
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

    public function getCreatedAt(): Carbon
    {
        return $this->createdAt;
    }
}
