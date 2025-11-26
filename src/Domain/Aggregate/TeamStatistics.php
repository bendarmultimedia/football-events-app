<?php

namespace App\Domain\Aggregate;

use App\Domain\ValueObject\MatchId;
use App\Domain\ValueObject\TeamId;

final class TeamStatistics
{
    private int $fouls = 0;
    private int $goals = 0;

    private function __construct(
        private readonly MatchId $matchId,
        private readonly TeamId $teamId
    ) {}

    public static function forTeamInMatch(
        MatchId $matchId,
        TeamId $teamId
    ): self {
        return new self($matchId, $teamId);
    }
    public function registerFoul(): void
    {
        $this->fouls++;
    }

    public function registerGoal(): void
    {
        $this->goals++;
    }

    public function fouls(): int { return $this->fouls; }
    public function goals(): int { return $this->goals; }

    public function getMatchId(): MatchId
    {
        return $this->matchId;
    }

    public function getTeamId(): TeamId
    {
        return $this->teamId;
    }

    public function setGoals(int $goals): void { $this->goals = $goals; }
    public function setFouls(int $fouls): void { $this->fouls = $fouls; }
}
