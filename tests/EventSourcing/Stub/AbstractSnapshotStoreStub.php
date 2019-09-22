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

use Gears\EventSourcing\Aggregate\Serializer\NativeAggregateSerializer;
use Gears\EventSourcing\Store\Snapshot\AbstractSnapshotStore;
use Gears\EventSourcing\Store\Snapshot\GenericSnapshot;
use Gears\EventSourcing\Store\Snapshot\Snapshot;
use Gears\EventSourcing\Store\StoreStream;

/**
 * Abstract snapshot store stub class.
 */
class AbstractSnapshotStoreStub extends AbstractSnapshotStore
{
    /**
     * @var string[]
     */
    protected $stream = [];

    /**
     * AbstractSnapshotStoreStub constructor.
     *
     * @param string[] $stream
     */
    public function __construct(array $stream = [])
    {
        parent::__construct(new NativeAggregateSerializer());

        $this->stream = $stream;
    }

    /**
     * {@inheritdoc}
     */
    public function load(StoreStream $stream): ?Snapshot
    {
        $id = $stream->getAggregateId()->getValue();
        if (!isset($this->stream[$id])) {
            return null;
        }

        return GenericSnapshot::fromAggregateRoot($this->deserializeAggregateRoot($this->stream[$id]));
    }

    /**
     * {@inheritdoc}
     */
    public function store(Snapshot $snapshot): void
    {
        $id = $snapshot->getStoreStream()->getAggregateId()->getValue();

        $this->stream[$id] = $this->serializeAggregateRoot($snapshot->getAggregateRoot());
    }
}
