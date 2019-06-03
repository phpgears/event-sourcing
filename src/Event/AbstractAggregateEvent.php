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

use Gears\DTO\ScalarPayloadBehaviour;
use Gears\Event\Time\SystemTimeProvider;
use Gears\Event\Time\TimeProvider;
use Gears\EventSourcing\Aggregate\AggregateVersion;
use Gears\Identity\Identity;
use Gears\Immutability\ImmutabilityBehaviour;

/**
 * Abstract immutable aggregate event.
 */
abstract class AbstractAggregateEvent implements AggregateEvent
{
    use ImmutabilityBehaviour, ScalarPayloadBehaviour, AggregateEventBehaviour {
        ScalarPayloadBehaviour::__call insteadof ImmutabilityBehaviour;
    }

    /**
     * Prevent aggregate event direct instantiation.
     *
     * @param Identity             $aggregateId
     * @param AggregateVersion     $aggregateVersion
     * @param array<string, mixed> $payload
     * @param \DateTimeImmutable   $createdAt
     */
    final protected function __construct(
        Identity $aggregateId,
        AggregateVersion $aggregateVersion,
        array $payload,
        \DateTimeImmutable $createdAt
    ) {
        $this->checkImmutability();

        $this->identity = $aggregateId;
        $this->version = $aggregateVersion;
        $this->createdAt = $createdAt->setTimezone(new \DateTimeZone('UTC'));

        $this->setPayload($payload);
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

        return new static(
            $aggregateId,
            new AggregateVersion(0),
            $payload,
            $timeProvider->getCurrentTime()
        );
    }

    /**
     * {@inheritdoc}
     *
     * @return mixed|self
     */
    final public static function reconstitute(array $payload, array $attributes = [])
    {
        return new static(
            $attributes['aggregateId'],
            $attributes['aggregateVersion'],
            $payload,
            $attributes['createdAt']
        );
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
