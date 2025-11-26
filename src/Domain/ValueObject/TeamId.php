<?php

namespace App\Domain\ValueObject;

final class TeamId
{
    public function __construct(private string $id) {}

    public function value(): string
    {
        return $this->id;
    }
}
