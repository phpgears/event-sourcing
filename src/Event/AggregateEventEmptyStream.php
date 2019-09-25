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

use Gears\EventSourcing\Event\Exception\AggregateEventStreamException;

final class AggregateEventEmptyStream extends \EmptyIterator implements AggregateEventStream
{
    /**
     * {@inheritdoc}
     *
     * @return AggregateEvent
     */
    public function current(): AggregateEvent
    {
        throw new AggregateEventStreamException('"Current" method must not be called on AggregateEventEmptyStream');
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return 0;
    }
}
