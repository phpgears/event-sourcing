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

namespace Gears\EventSourcing\Tests\Stub;

use Gears\EventSourcing\Aggregate\AggregateRoot;
use Gears\EventSourcing\Store\GenericStoreStream;
use Gears\EventSourcing\Store\Snapshot\Snapshot;
use Gears\EventSourcing\Store\StoreStream;

class SnapshotStub implements Snapshot
{
    /**
     * @var AggregateRoot
     */
    protected $aggregateRoot;

    /**
     * SnapshotStub constructor.
     *
     * @param AggregateRoot $aggregateRoot
     */
    public function __construct(AggregateRoot $aggregateRoot)
    {
        $this->aggregateRoot = $aggregateRoot;
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreStream(): StoreStream
    {
        return GenericStoreStream::fromAggregateRoot($this->aggregateRoot);
    }

    /**
     * {@inheritdoc}
     */
    public function getAggregateRoot(): AggregateRoot
    {
        return $this->aggregateRoot;
    }
}
