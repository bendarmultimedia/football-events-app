<?php

namespace App\Domain\ValueObject;

final class Player
{

    public function __construct(private string $name)
    {
    }

    public function value(): string
    {
        return $this->name;
    }
}
