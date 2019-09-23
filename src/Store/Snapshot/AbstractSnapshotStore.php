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
use Gears\EventSourcing\Aggregate\Serializer\AggregateSerializer;
use Gears\EventSourcing\Store\Snapshot\Exception\SnapshotStoreException;

/**
 * Abstract snapshot implementation.
 */
abstract class AbstractSnapshotStore implements SnapshotStore
{
    /**
     * @var AggregateSerializer
     */
    private $serializer;

    /**
     * AbstractSnapshotStore constructor.
     *
     * @param AggregateSerializer $serializer
     */
    public function __construct(AggregateSerializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Deserialize aggregate root.
     *
     * @param string $serialized
     *
     * @throws SnapshotStoreException
     *
     * @return AggregateRoot
     */
    final protected function deserializeAggregateRoot(string $serialized): AggregateRoot
    {
        $aggregateRoot = $this->serializer->fromSerialized($serialized);

        if ($aggregateRoot->getRecordedAggregateEvents()->count() !== 0
            || $aggregateRoot->getRecordedEvents()->count() !== 0
        ) {
            throw new SnapshotStoreException('Aggregate root coming from snapshot cannot have recorded events');
        }

        return $aggregateRoot;
    }

    /**
     * Serialize aggregate root.
     *
     * @param AggregateRoot $aggregateRoot
     *
     * @throws SnapshotStoreException
     *
     * @return string
     */
    final protected function serializeAggregateRoot(AggregateRoot $aggregateRoot): string
    {
        if ($aggregateRoot->getRecordedAggregateEvents()->count() !== 0
            || $aggregateRoot->getRecordedEvents()->count() !== 0
        ) {
            throw new SnapshotStoreException('Aggregate root cannot have recorded events in order to be snapshoted');
        }

        return $this->serializer->serialize($aggregateRoot);
    }
}
