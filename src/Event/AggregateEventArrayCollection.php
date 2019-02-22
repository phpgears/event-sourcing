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

use Gears\EventSourcing\Event\Exception\InvalidAggregateEventException;

final class AggregateEventArrayCollection implements AggregateEventCollection
{
    /**
     * @var AggregateEvent[]
     */
    private $events = [];

    /**
     * AggregateEventArrayCollection constructor.
     *
     * @param (AggregateEvent|mixed)[] $events
     *
     * @throws InvalidAggregateEventException
     */
    public function __construct(array $events)
    {
        foreach ($events as $event) {
            if (!$event instanceof AggregateEvent) {
                throw new InvalidAggregateEventException(\sprintf(
                    'Aggregate event collection only accepts %s, %s given',
                    AggregateEvent::class,
                    \is_object($event) ? \get_class($event) : \gettype($event)
                ));
            }

            $this->events[] = $event;
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return AggregateEvent
     */
    public function current(): AggregateEvent
    {
        return \current($this->events);
    }

    /**
     * {@inheritdoc}
     */
    public function next(): void
    {
        \next($this->events);
    }

    /**
     * {@inheritdoc}
     *
     * @return string|int|null
     */
    public function key()
    {
        return \key($this->events);
    }

    /**
     * {@inheritdoc}
     */
    public function valid(): bool
    {
        return \key($this->events) !== null;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        \reset($this->events);
    }
}
