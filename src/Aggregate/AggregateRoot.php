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

use Gears\Aggregate\AggregateRoot as BaseAggregateRoot;
use Gears\EventSourcing\Event\AggregateEventCollection;
use Gears\Identity\Identity;

/**
 * AggregateRoot interface.
 */
interface AggregateRoot extends BaseAggregateRoot
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
     * Reconstitute aggregate from a list of aggregate events.
     *
     * @param AggregateEventCollection $events
     *
     * @return mixed|self
     */
    public static function reconstituteFromAggregateEvents(AggregateEventCollection $events);

    /**
     * Replay aggregate events.
     *
     * @param AggregateEventCollection $events
     */
    public function replayAggregateEvents(AggregateEventCollection $events): void;

    /**
     * Get recorded aggregate events.
     *
     * @return AggregateEventCollection
     */
    public function getRecordedAggregateEvents(): AggregateEventCollection;

    /**
     * Remove recorded aggregate events from aggregate root.
     */
    public function clearRecordedAggregateEvents(): void;

    /**
     * Collect recorded aggregate events and remove them from aggregate root.
     *
     * @return AggregateEventCollection
     */
    public function collectRecordedAggregateEvents(): AggregateEventCollection;
}
