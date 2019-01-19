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

use Gears\Aggregate\AggregateIdentity;
use Gears\Event\Event;

/**
 * AggregateEvent interface.
 */
interface AggregateEvent extends Event
{
    /**
     * Get aggregate id.
     *
     * @return AggregateIdentity
     */
    public function getAggregateId(): AggregateIdentity;

    /**
     * Get aggregate version.
     *
     * @return int
     */
    public function getAggregateVersion(): int;

    /**
     * Get event with new aggregate version.
     *
     * @param int $aggregateVersion
     *
     * @return mixed|self
     */
    public function withAggregateVersion(int $aggregateVersion);
}
