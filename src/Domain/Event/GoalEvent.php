<?php

namespace App\Domain\Event;

use App\Domain\ValueObject\EventTimestamp;
use App\Domain\ValueObject\MatchId;
use App\Domain\ValueObject\Player;
use App\Domain\ValueObject\TeamId;

class GoalEvent extends Event
{
    public function __construct(
        MatchId                  $matchId,
        TeamId                   $teamId,
        private readonly Player  $scorer,
        private readonly ?Player $assist,
        EventTimestamp           $timestamp,
        \DateTimeImmutable       $occurredAt
    ) {
        parent::__construct($matchId, $teamId, $timestamp, $occurredAt);
    }

    public function type(): string { return 'goal'; }

    public function scorer(): Player { return $this->scorer; }
    public function assist(): ?Player { return $this->assist; }
}
