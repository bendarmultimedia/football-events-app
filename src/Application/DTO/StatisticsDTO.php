<?php

namespace App\Application\DTO;

use App\Domain\Aggregate\TeamStatistics;

final class StatisticsDTO
{
    public function __construct(
        public readonly string $matchId,
        public readonly string $teamId,
        public readonly int $goals,
        public readonly int $fouls
    ) {}

    public static function fromDomain(TeamStatistics $statistics): self
    {
        return new self(
            matchId: $statistics->getMatchId()->value(),
            teamId:  $statistics->getTeamId()->value(),
            goals:   $statistics->goals(),
            fouls:   $statistics->fouls()
        );
    }

    public function toArray(): array
    {
        return [
            'match_id' => $this->matchId,
            'team_id'  => $this->teamId,
            'goals'    => $this->goals,
            'fouls'    => $this->fouls,
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE);
    }
}
