<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Configuration\MainConfiguration;
use App\Infrastructure\Queue\Producer;
use App\Infrastructure\RedisClient;
use App\Service\EventHandler;
use App\Service\StatisticsManager;

header('Content-Type: application/json');

// Simple routing
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($method === 'POST' && $path === '/event') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    $redis = new RedisClient();
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON']);
        exit;
    }

    // Temporary disabled handler
    // $handler = new EventHandler(__DIR__ . '/../storage/events.txt');
    
    try {

        http_response_code(201);
        $producer = new Producer($redis);
        $producer->push(MainConfiguration::QUEUE_NAME, $data);
        // $result = $handler->handleEvent($data);
        // echo json_encode($result);
        echo "\nok\n";
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
} elseif ($method === 'GET' && $path === '/statistics') {
    $statsManager = new StatisticsManager(__DIR__ . '/../storage/statistics.txt');
    
    $matchId = $_GET['match_id'] ?? null;
    $teamId = $_GET['team_id'] ?? null;
    
    try {
        if ($matchId && $teamId) {
            // Get team statistics for specific match
            $stats = $statsManager->getTeamStatistics($matchId, $teamId);
            echo json_encode([
                'match_id' => $matchId,
                'team_id' => $teamId,
                'statistics' => $stats
            ]);
        } elseif ($matchId) {
            // Get all team statistics for specific match
            $stats = $statsManager->getMatchStatistics($matchId);
            echo json_encode([
                'match_id' => $matchId,
                'statistics' => $stats
            ]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'match_id is required']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Not found']);
}
