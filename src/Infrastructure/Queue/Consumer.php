<?php

namespace App\Infrastructure\Queue;

use App\Infrastructure\RedisClient;
use Redis;

final class Consumer
{
    private Redis $redis;

    public function __construct(RedisClient $redisClient)
    {
        $this->redis = $redisClient->getConnection();
    }

    public function pop(string $queueName, int $timeout = 10): ?array
    {
        $result = $this->redis->brPop([$queueName], $timeout);

        if (empty($result)) {
            return null;
        }

        $payload = $result[1];
        $data = json_decode($payload, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Error decoding JSON from the queue: " . json_last_error_msg());
            return null;
        }

        return $data;
    }
}
