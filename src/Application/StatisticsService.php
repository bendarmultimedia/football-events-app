<?php

namespace App\Application;

use App\Domain\Aggregate\TeamStatistics;
use App\Domain\Event\Event;
use App\Domain\Event\FoulEvent;
use App\Domain\Event\GoalEvent;
use App\Service\StatisticsManager;

final class StatisticsService
{

    private StatisticsManager $manager;

    public function __construct(
        string $storagePath,
    ) {
        $this->manager = new StatisticsManager($storagePath);
    }

    /**
     * Nakłada event na statystyki drużyny i zapisuje nowy stan
     */
    public function apply(Event $event): TeamStatistics
    {
        $teamId  = $event->teamId()->value();
        $matchId = $event->matchId()->value();

        $statsArray = $this->manager->getTeamStatistics($matchId, $teamId);

        $stats = TeamStatistics::forTeamInMatch($event->matchId(), $event->teamId());
        if ($statsArray) {
            $stats->setGoals($statsArray['goals'] ?? 0);
            $stats->setFouls($statsArray['fouls'] ?? 0);
        }

        if ($event instanceof GoalEvent) {
            $stats->registerGoal();
        }

        if ($event instanceof FoulEvent) {
            $stats->registerFoul();
        }


        $this->manager->updateTeamStatistics(
            $stats
        );

        return $stats;
    }
}
