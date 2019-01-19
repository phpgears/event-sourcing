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

namespace Gears\EventSourcing\Aggregate;

use Gears\Aggregate\AggregateIdentity;
use Gears\EventSourcing\Event\AggregateEventCollection;

/**
 * AggregateRoot interface.
 */
interface AggregateRoot
{
    /**
     * Get aggregate identity.
     *
     * @return AggregateIdentity
     */
    public function getIdentity(): AggregateIdentity;

    /**
     * Get aggregate version.
     *
     * @return int
     */
    public function getVersion(): int;

    /**
     * Collect recorded events and remove them from aggregate root.
     *
     * @return AggregateEventCollection
     */
    public function collectRecordedEvents(): AggregateEventCollection;

    /**
     * Replay events.
     *
     * @param AggregateEventCollection $events
     */
    public function replayEvents(AggregateEventCollection $events): void;

    /**
     * Reconstitute aggregate from a list of events.
     *
     * @param AggregateEventCollection $events
     *
     * @return mixed|self
     */
    public static function reconstituteFromEvents(AggregateEventCollection $events);
}
