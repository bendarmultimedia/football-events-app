<?php

namespace App\Infrastructure\Worker;

use App\Configuration\MainConfiguration;
use App\Infrastructure\Queue\Consumer;
use App\Infrastructure\RedisClient;
use App\Service\EventHandler;
use Redis;
use Throwable;

final class EventWorker
{

    private Consumer $consumer;
    private EventHandler $eventHandler;
    private Redis $publisher;

    public function __construct(public RedisClient $redisClient)
    {
        try {

        $this->consumer = new Consumer($this->redisClient);
        $this->eventHandler = new EventHandler(
            __DIR__ . '/../../../' . MainConfiguration::EVENTS_STORAGE_PATH
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
                $result = $this->eventHandler->handleEvent($eventData);
                if (isset($result['event'])) {
                    $message = json_encode($result['event']);
                    $this->publisher->rPush(MainConfiguration::REALTIME_CHANNEL, $message);
                }

                echo json_encode($result);
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
