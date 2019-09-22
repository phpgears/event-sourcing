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
use Gears\EventSourcing\Store\GenericStoreStream;
use Gears\EventSourcing\Store\StoreStream;

/**
 * Generic Snapshot.
 */
final class GenericSnapshot implements Snapshot
{
    /**
     * @var StoreStream
     */
    private $stream;

    /**
     * @var AggregateRoot
     */
    private $aggregateRoot;

    /**
     * GenericSnapshot constructor.
     *
     * @param StoreStream   $stream
     * @param AggregateRoot $aggregateRoot
     */
    private function __construct(StoreStream $stream, AggregateRoot $aggregateRoot)
    {
        $this->stream = $stream;
        $this->aggregateRoot = $aggregateRoot;
    }

    /**
     * Create from aggregateRoot.
     *
     * @param AggregateRoot $aggregateRoot
     *
     * @return self
     */
    public static function fromAggregateRoot(AggregateRoot $aggregateRoot): self
    {
        return new self(GenericStoreStream::fromAggregateRoot($aggregateRoot), $aggregateRoot);
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreStream(): StoreStream
    {
        return $this->stream;
    }

    /**
     * {@inheritdoc}
     */
    public function getAggregateRoot(): AggregateRoot
    {
        return $this->aggregateRoot;
    }
}
