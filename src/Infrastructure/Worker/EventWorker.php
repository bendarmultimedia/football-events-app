<?php

namespace App\Infrastructure\Worker;

use App\Application\EventService;
use App\Application\StatisticsService;
use App\Configuration\MainConfiguration;
use App\Infrastructure\Queue\Consumer;
use App\Infrastructure\RedisClient;
use App\Service\EventHandler;
use Redis;
use Throwable;

final class EventWorker
{

    private Consumer $consumer;
    private Redis $publisher;
    private EventService $eventService;

    public function __construct(public RedisClient $redisClient)
    {
        try {
        $pathPrefix = __DIR__ . '/../../../';
        $this->consumer = new Consumer($this->redisClient);
        $this->eventService = new EventService(
            (new StatisticsService($pathPrefix . MainConfiguration::STATISTICS_STORAGE_PATH)),
            $pathPrefix . MainConfiguration::EVENTS_STORAGE_PATH,
        );
        $this->publisher = $redisClient->getConnection();
        } catch (\Throwable $e) {
            die("CRITICAL ERROR: " . $e->getMessage() . "\n");
        }
    }

    public function run(): void
    {
        while (true) {
            try {
                $eventData = $this->getEvent();
                if (null === $eventData) {
                    echo "⚽\n";
                    continue;
                }
                echo "[" . date('H:i:s') . "] Processing event: " . ($eventData['type'] ?? 'unknown') . "\n";
                $event = $this->eventService->handle($eventData);
                $message = $event->toJson();
                $this->publisher->rPush(MainConfiguration::REALTIME_CHANNEL, $message);

                echo $event->toJson() . "\n";
                echo "[" . date('H:i:s') . "] Done.\n";
            } catch (Throwable $e) {
                echo "[" . date('H:i:s') . "] ERROR: " . $e->getMessage() . "\n";
            }
            gc_collect_cycles();
        }
    }

    private function getEvent(): ?array
    {
        return $this->consumer->pop(
            MainConfiguration::QUEUE_NAME,
            MainConfiguration::EMPTY_QUEUE_TIMEOUT
        );
    }
}
