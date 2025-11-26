<?php

namespace App\Configuration;

use ReflectionClass;

final class MainConfiguration
{
    public const string EVENTS_STORAGE_PATH = 'storage/events.txt';
    public const string STATISTICS_STORAGE_PATH = 'storage/statistics.txt';

    public const int EMPTY_QUEUE_TIMEOUT = 5;
    public const string QUEUE_NAME = 'events_queue';

    public const string REALTIME_CHANNEL = 'live_events';

    public static function getSettings(): array
    {
        $reflection = new ReflectionClass(__CLASS__);
        $publicConstants = [];
        foreach ($reflection->getReflectionConstants() as $constant) {
            if ($constant->isPublic()) {
                $publicConstants[$constant->getName()] = $constant->getValue();
            }
        }
        return $publicConstants;
    }
}
