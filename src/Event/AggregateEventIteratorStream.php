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

final class AggregateEventIteratorStream implements AggregateEventStream
{
    /**
     * @var \Iterator
     */
    private $iterator;

    /**
     * AggregateEventIteratorStream constructor.
     *
     * @param \Iterator $iterator
     */
    public function __construct(\Iterator $iterator)
    {
        $this->iterator = $iterator;
    }

    /**
     * {@inheritdoc}
     *
     * @return AggregateEvent
     */
    public function current(): AggregateEvent
    {
        $event = $this->iterator->current();

        if (!$event instanceof AggregateEvent) {
            throw new InvalidAggregateEventException(\sprintf(
                'Aggregate event stream only accepts "%s", "%s" given',
                AggregateEvent::class,
                \is_object($event) ? \get_class($event) : \gettype($event)
            ));
        }

        return $event;
    }

    /**
     * {@inheritdoc}
     */
    public function next(): void
    {
        $this->iterator->next();
    }

    /**
     * {@inheritdoc}
     *
     * @return string|int|null
     */
    public function key()
    {
        return $this->iterator->key();
    }

    /**
     * {@inheritdoc}
     */
    public function valid(): bool
    {
        return $this->iterator->valid();
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        $this->iterator->rewind();
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        if ($this->iterator instanceof \Countable) {
            return $this->iterator->count();
        }

        $currentKey = $this->iterator->key();
        $this->iterator->rewind();

        $count = 0;
        while ($this->iterator->valid()) {
            $count++;

            $this->iterator->next();
        }

        $this->iterator->rewind();
        while ($this->iterator->key() !== $currentKey) {
            $this->iterator->next();
        }

        return $count;
    }
}
