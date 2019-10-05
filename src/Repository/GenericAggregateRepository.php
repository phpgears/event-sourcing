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

namespace Gears\EventSourcing\Repository;

use Gears\EventSourcing\Store\Event\EventStore;
use Gears\EventSourcing\Store\GenericStoreStream;
use Gears\EventSourcing\Store\Snapshot\SnapshotStore;
use Gears\EventSourcing\Store\StoreStream;
use Gears\Identity\Identity;

/**
 * Abstract aggregate repository.
 */
final class GenericAggregateRepository extends AbstractAggregateRepository
{
    /**
     * @var string
     */
    private $aggregateRootClass;

    /**
     * AbstractAggregateRepository constructor.
     *
     * @param string             $aggregateRootClass
     * @param EventStore         $eventStore
     * @param SnapshotStore|null $snapshotStore
     */
    public function __construct(
        string $aggregateRootClass,
        EventStore $eventStore,
        ?SnapshotStore $snapshotStore = null
    ) {
        parent::__construct($eventStore, $snapshotStore);

        $this->aggregateRootClass = $aggregateRootClass;
    }

    /**
     * {@inheritdoc}
     */
    protected function getStoreStream(Identity $aggregateId): StoreStream
    {
        return GenericStoreStream::fromAggregateData($this->aggregateRootClass, $aggregateId);
    }
}
