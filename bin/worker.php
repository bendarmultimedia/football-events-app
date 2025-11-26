<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Infrastructure\RedisClient;
use App\Infrastructure\Worker\EventWorker;

set_time_limit(0);
ini_set('display_errors', '1');
error_reporting(E_ALL);

echo "Starting EventWorker...\n";
try {
    $worker = new EventWorker(new RedisClient());
    echo "Event worker started!\n";
} catch (Exception $e) {
    die("CRITICAL ERROR: " . $e->getMessage() . "\n");
}

$worker->run();
