<?php

namespace App\Application\DTO;

use App\Domain\Event\FoulEvent;
use App\Domain\Event\GoalEvent;
use App\Domain\Event\Event;

final class EventDTO
{
    public function __construct(
        public readonly string $type,
        public readonly string $matchId,
        public readonly string $teamId,
        public readonly int $minute,
        public readonly int $second,
        public readonly array $payload,
        public readonly string $occurredAt
    ) {}

    public static function fromDomain(Event $event): self
    {
        $basePayload = [
            'type'       => $event->type(),
            'match_id'   => $event->matchId()->value(),
            'team_id'    => $event->teamId()->value(),
            'minute'    => $event->timestamp()->minute(),
            'second'    => $event->timestamp()->second(),
            'occurred_at' => $event->occurredAt()->format('Y-m-d H:i:s'),
        ];

        if ($event instanceof GoalEvent) {
            $payload = array_merge($basePayload, [
                'scorer' => $event->scorer()->value(),
                'assist' => $event->assist()?->value(),
            ]);
        } elseif ($event instanceof FoulEvent) {
            $payload = array_merge($basePayload, [
                'player_at_fault' => $event->playerAtFault()->value(),
                'victim'          => $event->victim()?->value(),
            ]);
        } else {
            throw new \LogicException('Unknown Event type: ' . get_class($event));
        }

        return new self(
            type: $event->type(),
            matchId: $event->matchId()->value(),
            teamId: $event->teamId()->value(),
            minute: $event->timestamp()->minute(),
            second: $event->timestamp()->second(),
            payload: $payload,
            occurredAt: $event->occurredAt()->format(DATE_ATOM)
        );
    }

    public function toArray(): array
    {
        return [
            'type'         => $this->type,
            'match_id'     => $this->matchId,
            'team_id'      => $this->teamId,
            'minute'       => $this->minute,
            'second'       => $this->second,
            'occurred_at'  => $this->occurredAt,
            'payload'      => $this->payload,
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE);
    }
}
