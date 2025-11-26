<?php
require_once __DIR__ . '/../vendor/autoload.php';

ini_set('display_errors', '1');
use App\Infrastructure\RedisClient;
use App\Infrastructure\SSE\Server;

$server = new Server(new RedisClient());

$server->start();
$server->loop();
