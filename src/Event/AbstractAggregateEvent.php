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
use Gears\EventSourcing\Event\Exception\AggregateEventException;
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
     * @param Identity           $aggregateId
     * @param iterable<mixed>    $payload
     * @param \DateTimeImmutable $createdAt
     */
    final protected function __construct(Identity $aggregateId, iterable $payload, \DateTimeImmutable $createdAt)
    {
        $this->setPayload($payload);

        $this->identity = $aggregateId;
        $this->version = new AggregateVersion(0);
        $this->createdAt = $createdAt->setTimezone(new \DateTimeZone('UTC'));
    }

    /**
     * {@inheritdoc}
     */
    public function getEventType(): string
    {
        return static::class;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return $this->getPayloadRaw();
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
     * @throws AggregateEventException
     *
     * @return mixed|self
     */
    final public static function reconstitute(iterable $payload, \DateTimeImmutable $createdAt, array $attributes = [])
    {
        $event = new static($attributes['aggregateId'], $payload, $createdAt);

        if (!isset($attributes['aggregateVersion'])
            || !$attributes['aggregateVersion'] instanceof AggregateVersion
            || (new AggregateVersion(0))->isEqualTo($attributes['aggregateVersion'])
        ) {
            throw new AggregateEventException(\sprintf(
                'Invalid aggregate version, "%s" given',
                $attributes['aggregateVersion'] instanceof AggregateVersion
                    ? $attributes['aggregateVersion']->getValue()
                    : \gettype($attributes['aggregateVersion'])
            ));
        }

        $event->version = $attributes['aggregateVersion'];

        if (isset($attributes['metadata'])) {
            $event->addMetadata($attributes['metadata']);
        }

        return $event;
    }

    /**
     * @return string[]
     */
    final public function __sleep(): array
    {
        throw new AggregateEventException(\sprintf('Aggregate event "%s" cannot be serialized', static::class));
    }

    final public function __wakeup(): void
    {
        throw new AggregateEventException(\sprintf('Aggregate event "%s" cannot be unserialized', static::class));
    }

    /**
     * @return array<string, mixed>
     */
    final public function __serialize(): array
    {
        throw new AggregateEventException(\sprintf('Aggregate event "%s" cannot be serialized', static::class));
    }

    /**
     * @param array<string, mixed> $data
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    final public function __unserialize(array $data): void
    {
        throw new AggregateEventException(\sprintf('Aggregate event "%s" cannot be unserialized', static::class));
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
