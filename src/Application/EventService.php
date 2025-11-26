<?php

namespace App\Application;

use App\Application\DTO\EventDTO;
use App\Domain\Service\EventFactory;
use App\Storage\FileStorage;

final class EventService
{
    private FileStorage $storage;

    public function __construct(
        private StatisticsService $stats,
        string $storagePath,
    ) {

        $this->storage = new FileStorage($storagePath);
    }

    public function handle(array $data): EventDTO
    {

        $event = EventFactory::createFromArray($data);

        $this->storage->save($event);

        $this->stats->apply($event);

        return EventDTO::fromDomain($event);
    }
}
