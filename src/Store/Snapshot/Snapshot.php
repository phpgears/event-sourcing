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

namespace Gears\EventSourcing\Store\Snapshot;

use Gears\EventSourcing\Aggregate\AggregateRoot;
use Gears\EventSourcing\Store\StoreStream;

interface Snapshot
{
    /**
     * Get store stream.
     *
     * @return StoreStream
     */
    public function getStoreStream(): StoreStream;

    /**
     * Get aggregate root.
     *
     * @return AggregateRoot
     */
    public function getAggregateRoot(): AggregateRoot;
}
