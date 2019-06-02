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

/**
 * Aggregate event stream.
 */
interface AggregateEventStream extends \Iterator
{
    /**
     * {@inheritdoc}
     *
     * @return AggregateEvent
     */
    public function current(): AggregateEvent;
}