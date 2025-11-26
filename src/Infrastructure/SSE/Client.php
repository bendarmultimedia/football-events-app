<?php

namespace App\Infrastructure\SSE;

final class Client
{

    public function __construct(private readonly mixed $socket, private int $lastHeartBeat, private readonly int $id)
    {
    }

    public function getSocket(): mixed
    {
        return $this->socket;
    }

    public function getLastHeartBeat(): int
    {
        return $this->lastHeartBeat;
    }

    public function setLastHeartBeat(int $lastHeartBeat): void
    {
        $this->lastHeartBeat = $lastHeartBeat;
    }

    public function isConnected(): bool
    {
        $chunk = @fread($this->socket, 1);
        return !($chunk === '' && feof($this->socket));
    }
    public function disconnect(): bool
    {
        return fclose($this->socket);
    }

    public function getId(): int
    {
        return $this->id;
    }
}
