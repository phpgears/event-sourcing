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

use Gears\EventSourcing\Event\AggregateEventCollection;
use Gears\Identity\Identity;

/**
 * AggregateRoot interface.
 */
interface AggregateRoot
{
    /**
     * Get aggregate identity.
     *
     * @return Identity
     */
    public function getIdentity(): Identity;

    /**
     * Get aggregate version.
     *
     * @return int
     */
    public function getVersion(): int;

    /**
     * Reconstitute aggregate from a list of events.
     *
     * @param AggregateEventCollection $events
     *
     * @return mixed|self
     */
    public static function reconstituteFromEvents(AggregateEventCollection $events);

    /**
     * Replay events.
     *
     * @param AggregateEventCollection $events
     */
    public function replayEvents(AggregateEventCollection $events): void;

    /**
     * Get recorded events.
     *
     * @return AggregateEventCollection
     */
    public function getRecordedEvents(): AggregateEventCollection;

    /**
     * Remove recorded events from aggregate root.
     */
    public function clearRecordedEvents(): void;

    /**
     * Collect recorded events and remove them from aggregate root.
     *
     * @return AggregateEventCollection
     */
    public function collectRecordedEvents(): AggregateEventCollection;
}
