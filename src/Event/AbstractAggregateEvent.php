<?php

/*
 * event-sourcing (https://github.com/phpgears/event-sourcing).
 * Event Sourcing base.
 *
 * @license MIT
 * @link https://github.com/phpgears/event-sourcing
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

declare(strict_types=1);

namespace Gears\EventSourcing\Event;

use Gears\Event\Time\SystemTimeProvider;
use Gears\Event\Time\TimeProvider;
use Gears\EventSourcing\Aggregate\AggregateVersion;
use Gears\Identity\Identity;

/**
 * Abstract immutable aggregate event.
 */
abstract class AbstractAggregateEvent implements AggregateEvent
{
    use AggregateEventBehaviour;

    /**
     * Prevent aggregate event direct instantiation.
     *
     * @param Identity             $aggregateId
     * @param array<string, mixed> $payload
     * @param \DateTimeImmutable   $createdAt
     */
    final protected function __construct(Identity $aggregateId, array $payload, \DateTimeImmutable $createdAt)
    {
        $this->assertImmutable();

        $this->identity = $aggregateId;
        $this->version = new AggregateVersion(0);
        $this->setPayload($payload);
        $this->createdAt = $createdAt->setTimezone(new \DateTimeZone('UTC'));
    }

    /**
     * Instantiate new aggregate event.
     *
     * @param Identity             $aggregateId
     * @param array<string, mixed> $payload
     * @param TimeProvider|null    $timeProvider
     *
     * @return mixed|self
     */
    final protected static function occurred(Identity $aggregateId, array $payload, ?TimeProvider $timeProvider = null)
    {
        $timeProvider = $timeProvider ?? new SystemTimeProvider();

        return new static($aggregateId, $payload, $timeProvider->getCurrentTime());
    }

    /**
     * {@inheritdoc}
     *
     * @return mixed|self
     */
    final public static function reconstitute(array $payload, \DateTimeImmutable $createdAt, array $attributes = [])
    {
        $event = new static($attributes['aggregateId'], $payload, $createdAt);
        $event->version = $attributes['aggregateVersion'];

        if (isset($attributes['metadata'])) {
            $event->addMetadata($attributes['metadata']);
        }

        return $event;
    }

    /**
     * {@inheritdoc}
     *
     * @return string[]
     */
    final protected function getAllowedInterfaces(): array
    {
        return [AggregateEvent::class];
    }
}
