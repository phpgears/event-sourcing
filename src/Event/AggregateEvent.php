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

use Gears\Event\Event;
use Gears\Identity\Identity;

/**
 * AggregateEvent interface.
 */
interface AggregateEvent extends Event
{
    /**
     * Get aggregate id.
     *
     * @return Identity
     */
    public function getAggregateId(): Identity;

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
