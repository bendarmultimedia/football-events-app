<?php

namespace App\Infrastructure\Queue;

use App\Infrastructure\RedisClient;
use InvalidArgumentException;
use Redis;

final class Producer
{
    private Redis $redis;

    public function __construct(RedisClient $redisClient)
    {
        $this->redis = $redisClient->getConnection();
    }

    public function push(string $queueName, array $data): int
    {
        $payload = json_encode($data);

        var_dump($payload);

        if ($payload === false) {
            throw new InvalidArgumentException("Data cannot be serialized to JSON format.");
        }
        return $this->redis->lPush($queueName, $payload);
    }
}
