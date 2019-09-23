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

namespace Gears\EventSourcing\Store\Event;

use Gears\EventSourcing\Aggregate\AggregateVersion;
use Gears\EventSourcing\Event\AggregateEventStream;
use Gears\EventSourcing\Store\StoreStream;

/**
 * EventStore interface.
 */
interface EventStore
{
    /**
     * Get aggregate event stream from a version.
     *
     * @param StoreStream      $stream
     * @param AggregateVersion $fromVersion
     * @param int|null         $count
     *
     * @throws \Gears\EventSourcing\Store\Event\Exception\EventStoreException
     *
     * @return AggregateEventStream
     */
    public function loadFrom(
        StoreStream $stream,
        AggregateVersion $fromVersion,
        ?int $count = null
    ): AggregateEventStream;

    /**
     * Get aggregate event stream up to a version.
     *
     * @param StoreStream           $stream
     * @param AggregateVersion      $toVersion
     * @param AggregateVersion|null $fromVersion
     *
     * @throws \Gears\EventSourcing\Store\Event\Exception\EventStoreException
     *
     * @return AggregateEventStream
     */
    public function loadTo(
        StoreStream $stream,
        AggregateVersion $toVersion,
        ?AggregateVersion $fromVersion = null
    ): AggregateEventStream;

    /**
     * Append events to store.
     *
     * @param StoreStream          $stream
     * @param AggregateEventStream $eventStream
     *
     * @throws \Gears\EventSourcing\Store\Event\Exception\ConcurrencyException
     * @throws \Gears\EventSourcing\Store\Event\Exception\EventStoreException
     */
    public function store(StoreStream $stream, AggregateEventStream $eventStream): void;
}
