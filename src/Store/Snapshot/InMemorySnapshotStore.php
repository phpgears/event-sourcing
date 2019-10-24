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

use Gears\EventSourcing\Store\StoreStream;

/**
 * In memory Event Store implementation.
 */
final class InMemorySnapshotStore extends AbstractSnapshotStore
{
    /**
     * Serialized aggregate root.
     *
     * @var string[]
     */
    private $streams = [];

    /**
     * {@inheritdoc}
     */
    public function load(StoreStream $stream): ?Snapshot
    {
        $streamId = $this->getStreamId($stream);
        if (!isset($this->streams[$streamId])) {
            return null;
        }

        return new GenericSnapshot($this->deserializeAggregateRoot($this->streams[$streamId]));
    }

    /**
     * {@inheritdoc}
     */
    public function store(Snapshot $snapshot): void
    {
        $streamId = $this->getStreamId($snapshot->getStoreStream());

        $this->streams[$streamId] = $this->serializeAggregateRoot($snapshot->getAggregateRoot());
    }

    /**
     * Get stream identifier.
     *
     * @param StoreStream $stream
     *
     * @return string
     */
    private function getStreamId(StoreStream $stream): string
    {
        return $stream->getAggregateId()->getValue();
    }
}
