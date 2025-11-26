<?php

namespace App\Domain\Service;

use App\Domain\Event\FoulEvent;
use App\Domain\Event\GoalEvent;
use App\Domain\ValueObject\EventTimestamp;
use App\Domain\ValueObject\MatchId;
use App\Domain\ValueObject\Player;
use App\Domain\ValueObject\TeamId;

final class EventFactory
{
    public static function createFromArray(array $data): FoulEvent|GoalEvent
    {
        $matchId = new MatchId($data['match_id']);
        $teamId  = new TeamId($data['team_id']);
        $time = new EventTimestamp($data['minute'], $data['second']);
        $now = new \DateTimeImmutable();

        return match ($data['type']) {
            'goal' => new GoalEvent(
                $matchId,
                $teamId,
                new Player($data['scorer']),
                isset($data['assist']) ? new Player($data['assist']) : null,
                $time,
                $now
            ),
            'foul' => new FoulEvent(
                $matchId,
                $teamId,
                new Player($data['player']),
                isset($data['victim']) ? new Player($data['victim']) : null,
                $time,
                $now
            ),
            default => throw new \InvalidArgumentException("Unknown event type")
        };
    }
}
