<?php

namespace App\Domain\ValueObject;

final class MatchId
{
    public function __construct(private string $id) {}

    public function value(): string
    {
        return $this->id;
    }
}
