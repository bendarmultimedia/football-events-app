<?php

namespace App\Service;

use App\Domain\Aggregate\TeamStatistics;
use App\Storage\FileStorage;

class StatisticsManager
{
    private FileStorage $storage;
    private string $statsFile;
    
    public function __construct(string $statsFile = '../storage/statistics.txt')
    {
        $this->storage = new FileStorage($statsFile);
        $this->statsFile = $statsFile;
        
        $directory = dirname($statsFile);
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
    }
    
    public function updateTeamStatistics(TeamStatistics $newStats): void
    {
        $stats = $this->getStatistics();
        $matchId = $newStats->getMatchId()->value();
        $teamId = $newStats->getTeamId()->value();
        if (!isset($stats[$matchId])) {
            $stats[$matchId] = [];
        }
        
        if (!isset($stats[$matchId][$teamId])) {
            $stats[$matchId][$teamId] = [];
        }
        
        $stats[$matchId][$teamId]['fouls'] = $newStats->fouls();
        $stats[$matchId][$teamId]['goals'] = $newStats->goals();

        $this->saveStatistics($stats);
    }
    
    public function getTeamStatistics(string $matchId, string $teamId): array
    {
        $stats = $this->getStatistics();

        return $stats[$matchId][$teamId] ?? [];
    }
    
    public function getMatchStatistics(string $matchId): array
    {
        $stats = $this->getStatistics();
        return $stats[$matchId] ?? [];
    }
    
    private function getStatistics(): array
    {
        if (!file_exists($this->statsFile)) {
            return [];
        }
        
        $content = file_get_contents($this->statsFile);
        return json_decode($content, true) ?? [];
    }
    
    private function saveStatistics(array $stats): void
    {
        file_put_contents($this->statsFile, json_encode($stats, JSON_PRETTY_PRINT), LOCK_EX);
    }
}
