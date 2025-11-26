<?php

namespace App\Domain\ValueObject;

final class EventTimestamp
{
    public function __construct(
        private int $minute,
        private int $second
    ) {}

    public function minute(): int { return $this->minute; }
    public function second(): int { return $this->second; }
}
