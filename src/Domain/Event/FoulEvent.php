<?php

namespace App\Domain\Event;

use App\Domain\ValueObject\EventTimestamp;
use App\Domain\ValueObject\MatchId;
use App\Domain\ValueObject\Player;
use App\Domain\ValueObject\TeamId;

class FoulEvent extends Event
{
    public function __construct(
        MatchId                 $matchId,
        TeamId                  $teamId,
        private readonly Player $playerAtFault,
        private readonly ?Player $playerVictim,
        EventTimestamp          $timestamp,
        \DateTimeImmutable      $occurredAt
    ) {
        parent::__construct($matchId, $teamId, $timestamp, $occurredAt);
    }

    public function type(): string { return 'foul'; }

    public function playerAtFault(): Player { return $this->playerAtFault; }
    public function victim(): ?Player { return $this->playerVictim; }
}
