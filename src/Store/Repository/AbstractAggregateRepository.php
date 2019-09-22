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

namespace Gears\EventSourcing\Store\Repository;

use Gears\EventSourcing\Aggregate\AggregateRoot;
use Gears\EventSourcing\Aggregate\AggregateVersion;
use Gears\EventSourcing\Store\Event\EventStore;
use Gears\EventSourcing\Store\Repository\Exception\AggregateRepositoryException;
use Gears\EventSourcing\Store\Repository\Exception\AggregateRootNotFoundException;
use Gears\EventSourcing\Store\Snapshot\SnapshotStore;
use Gears\EventSourcing\Store\StoreStream;
use Gears\Identity\Identity;

/**
 * Abstract aggregate repository.
 */
abstract class AbstractAggregateRepository implements AggregateRepository
{
    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @var SnapshotStore|null
     */
    private $snapshotStore;

    /**
     * AbstractAggregateRepository constructor.
     *
     * @param EventStore         $eventStore
     * @param SnapshotStore|null $snapshotStore
     */
    public function __construct(EventStore $eventStore, ?SnapshotStore $snapshotStore = null)
    {
        $this->eventStore = $eventStore;
        $this->snapshotStore = $snapshotStore;
    }

    /**
     * {@inheritdoc}
     */
    final public function getAggregateRoot(Identity $aggregateId): AggregateRoot
    {
        $stream = $this->getStoreStream($aggregateId);

        $aggregateRoot = $this->getAggregateRootFromSnapshotStore($stream);
        if ($aggregateRoot !== null) {
            $this->assertAggregateRootType($stream, $aggregateRoot);

            $eventStream = $this->eventStore->loadFrom($stream, $aggregateRoot->getVersion()->getNext());
            $aggregateRoot->replayAggregateEventStream($eventStream);

            return $aggregateRoot;
        }

        $eventStream = $this->eventStore->loadFrom($stream, new AggregateVersion(1));
        if ($eventStream->count() !== 0) {
            $eventStream->rewind();

            /* @var AggregateRoot $aggregateRootClass */
            $aggregateRootClass = $stream->getAggregateRootClass();

            return $aggregateRootClass::reconstituteFromEventStream($eventStream);
        }

        throw new AggregateRootNotFoundException(
            \sprintf('Aggregate root from identity "%s" not found', $aggregateId->getValue())
        );
    }

    /**
     * Get aggregate root from snapshot store.
     *
     * @param StoreStream $stream
     *
     * @return AggregateRoot|null
     */
    private function getAggregateRootFromSnapshotStore(StoreStream $stream): ?AggregateRoot
    {
        if ($this->snapshotStore === null) {
            return null;
        }

        $snapshot = $this->snapshotStore->load($stream);

        return $snapshot !== null ? $snapshot->getAggregateRoot() : null;
    }

    /**
     * {@inheritdoc}
     */
    final public function saveAggregateRoot(AggregateRoot $aggregateRoot): void
    {
        $storeStream = $this->getStoreStream($aggregateRoot->getIdentity());

        $this->assertAggregateRootType($storeStream, $aggregateRoot);

        $eventStream = $aggregateRoot->collectRecordedAggregateEvents();
        if ($eventStream->count() === 0) {
            return;
        }

        $originalVersion = $eventStream->current()->getAggregateVersion()->getPrevious();

        $this->eventStore->store($storeStream, $eventStream, $originalVersion);
    }

    /**
     * Assert aggregate root of correct type.
     *
     * @param StoreStream   $stream
     * @param AggregateRoot $aggregateRoot
     *
     * @throws AggregateRepositoryException
     */
    private function assertAggregateRootType(StoreStream $stream, AggregateRoot $aggregateRoot): void
    {
        if (!\is_a($aggregateRoot, $stream->getAggregateRootClass())) {
            throw new AggregateRepositoryException(\sprintf(
                'Aggregate root should be a "%s", "%s" given',
                $stream->getAggregateRootClass(),
                \get_class($aggregateRoot)
            ));
        }
    }

    /**
     * Get store stream.
     *
     * @param Identity $aggregateId
     *
     * @return StoreStream
     */
    abstract protected function getStoreStream(Identity $aggregateId): StoreStream;
}
