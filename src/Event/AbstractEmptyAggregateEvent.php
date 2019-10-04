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
 * Abstract empty immutable aggregate event.
 */
abstract class AbstractEmptyAggregateEvent implements AggregateEvent
{
    use AggregateEventBehaviour;

    /**
     * Prevent aggregate event direct instantiation.
     *
     * @param Identity           $aggregateId
     * @param \DateTimeImmutable $createdAt
     */
    final protected function __construct(Identity $aggregateId, \DateTimeImmutable $createdAt)
    {
        $this->assertImmutable();

        $this->identity = $aggregateId;
        $this->version = new AggregateVersion(0);
        $this->createdAt = $createdAt->setTimezone(new \DateTimeZone('UTC'));
    }

    /**
     * Instantiate new aggregate event.
     *
     * @param Identity          $aggregateId
     * @param TimeProvider|null $timeProvider
     *
     * @return mixed|self
     */
    final protected static function occurred(Identity $aggregateId, ?TimeProvider $timeProvider = null)
    {
        $timeProvider = $timeProvider ?? new SystemTimeProvider();

        return new static($aggregateId, $timeProvider->getCurrentTime());
    }

    /**
     * {@inheritdoc}
     *
     * @throws AggregateEventException
     *
     * @return mixed|self
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    final public static function reconstitute(array $payload, \DateTimeImmutable $createdAt, array $attributes = [])
    {
        $event = new static($attributes['aggregateId'], $createdAt);

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
     * {@inheritdoc}
     *
     * @return string[]
     */
    final protected function getAllowedInterfaces(): array
    {
        return [AggregateEvent::class];
    }
}
