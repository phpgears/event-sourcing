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
     * Serialize aggregate root.
     *
     * @param AggregateRoot $aggregateRoot
     *
     * @return string
     */
    final protected function serializeAggregateRoot(AggregateRoot $aggregateRoot): string
    {
        return $this->serializer->serialize($aggregateRoot);
    }

    /**
     * Deserialize aggregate root.
     *
     * @param string $serialized
     *
     * @return AggregateRoot
     */
    final protected function deserializeAggregateRoot(string $serialized): AggregateRoot
    {
        return $this->serializer->fromSerialized($serialized);
    }
}
