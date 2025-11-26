<?php

namespace App\Infrastructure;

use Exception;
use Redis;
use RuntimeException;

final class RedisClient
{
    private Redis $redis;

    private const array DEFAULT_OPTIONS = [
        'host' => 'redis',
        'port' => 6379,
        'timeout' => 1,
    ];

    private array $options = [];

    public function __construct(?array $options = [])
    {
        $this->options = array_merge($options, self::DEFAULT_OPTIONS);
        $host = getenv('REDIS_HOST') ?: $this->options['host'];
        $port = getenv('REDIS_PORT') ? (int)getenv('REDIS_PORT') : $this->options['port'];

        $this->redis = new Redis();

        try {
            $connected = $this->redis->connect($host, $port, $this->options['timeout']);
            if (!$connected) {
                throw new RuntimeException("Unable to connect to Redis on $host:$port");
            }
        } catch (Exception $e) {
            throw new RuntimeException('Redis connection error: ' . $e->getMessage());
        }
    }

    public function getConnection(): Redis
    {
        return $this->redis;
    }
}
