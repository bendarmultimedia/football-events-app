<?php

namespace App\Infrastructure\SSE;

use AllowDynamicProperties;
use App\Configuration\MainConfiguration;
use App\Infrastructure\RedisClient;
use Redis;

final class Server
{

    private Redis $redis;

    public const string SERVER_URL = 'tcp://0.0.0.0:8001';
    public const int SLEEP_TIMEOUT = 500000;
    public const int HEARTBEAT_INTERVAL = 15;

    public const string EVENT_NAME = 'new_football_event';

    /**
     * @var false|resource
     */
    private mixed $streamSocketServer;

    /**
     * @var Client[]
     */
    private array $clients = [];
    /**
     * @var mixed|string
     */
    private mixed $origin;

    public function __construct(private RedisClient $redisClient)
    {
        $this->origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
        $this->redis = $this->redisClient->getConnection();
    }

    public function start(): void
    {
        $this->streamSocketServer = stream_socket_server(self::SERVER_URL, $errno, $errstr);

        if (!$this->streamSocketServer) {
            die("Cannot start SSE server: $errstr\n");
        }
        stream_set_blocking($this->streamSocketServer, false);
        echo "⏳ Waiting for client...\n";
    }

    public function loop(): void
    {
        while (true) {
            $newClient = @stream_socket_accept($this->streamSocketServer, 0);
            if ($newClient) {
                stream_set_blocking($newClient, false);
                $this->sendHeaders($newClient);
                $lastKey = array_key_last($this->clients) ?: 0;
                $this->clients[] = new Client($newClient, time(), $lastKey);
                echo "🟢 Client connected\n";
            }

            $this->broadcast();
            usleep(self::SLEEP_TIMEOUT);
        }
    }

    private function sendHeaders($newClient): void
    {
        fwrite($newClient, "HTTP/1.1 200 OK\r\n");
        fwrite($newClient, "Access-Control-Allow-Origin: $this->origin\r\n");
        fwrite($newClient, "Access-Control-Allow-Credentials: true\r\n");
        fwrite($newClient, "Content-Type: text/event-stream\r\n");
        fwrite($newClient, "Cache-Control: no-cache\r\n");
        fwrite($newClient, "X-Accel-Buffering: no\r\n");
        fwrite($newClient, "Connection: keep-alive\r\n\r\n");
    }

    private function broadcast(): void
    {
        foreach ($this->clients as $key => $client) {

            if (!$client->isConnected()) {
                $this->closeConnection($key);
                continue;
            }
            $this->heartBeat($client);

            $redisSentResult = $this->sendRedisEvent($client, MainConfiguration::REALTIME_CHANNEL);
            $timeMessageResult = $this->sendEvent($client, 'time_message', date("H:i:s"));
            if (false === $redisSentResult || false === $timeMessageResult) {
                $this->closeConnection($key, 'send failed');
            }
        }
    }

    private function closeConnection(int|string $key, ?string $additionalInfo = ''): void
    {
        if(isset($this->clients[$key])) {
            $this->clients[$key]->disconnect();
            unset($this->clients[$key]);
            if ($additionalInfo === '') {

                echo "🔴 Client disconnected\n";
            } else {
                echo "🔴 Client disconnected ($additionalInfo)\n";
            }
        }
    }

    private function heartBeat(Client $client): void
    {
        if (time() - $client->getLastHeartBeat() >= self::HEARTBEAT_INTERVAL) {
            @fwrite($client->getSocket(), ": heartbeat\n\n");
            @fflush($client->getSocket());
            $client->setLastHeartBeat(time());
        }
    }
    private function sendEvent(Client $client, string $eventName, $data): bool
    {
        $data = [
            'id'    => time(),
            'type'    => $eventName,
            'data' => $data,

        ];
        $formatted = json_encode($data, JSON_UNESCAPED_UNICODE);
        if (
            @fwrite($client->getSocket(), "id: {$data['id']}\n") === false ||
            @fwrite($client->getSocket(), "event: $eventName\n") === false ||
            @fwrite($client->getSocket(), "data: {$formatted}\n\n") === false ||
            @fflush($client->getSocket()) === false
        ) {
            return false;
        }

        return true;
    }

    private function sendRedisEvent(Client $client, $channel): ?bool
    {
        $result = $this->redis->blPop([$channel], .5);

        if (is_array($result) && isset($result[1])) {
            $payload = $result[1];
            return $this->sendEvent($client, self::EVENT_NAME, $payload);
        }
        return null;
    }
}
