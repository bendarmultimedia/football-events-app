<?php

namespace App\Domain\Event;

use App\Domain\ValueObject\EventTimestamp;
use App\Domain\ValueObject\MatchId;
use App\Domain\ValueObject\TeamId;
use DateTimeImmutable;

abstract class Event
{
    public function __construct(
        protected MatchId $matchId,
        protected TeamId $teamId,
        protected EventTimestamp $timestamp,
        protected DateTimeImmutable $occurredAt
    ) {}

    abstract public function type(): string;

    public function matchId(): MatchId { return $this->matchId; }
    public function teamId(): TeamId { return $this->teamId; }
    public function timestamp(): EventTimestamp { return $this->timestamp; }
    public function occurredAt(): DateTimeImmutable { return $this->occurredAt; }
}
