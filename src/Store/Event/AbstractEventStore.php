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

namespace Gears\EventSourcing\Store\Event;

use Gears\EventSourcing\Aggregate\AggregateVersion;
use Gears\EventSourcing\Event\AggregateEventEmptyStream;
use Gears\EventSourcing\Event\AggregateEventStream;
use Gears\EventSourcing\Store\Event\Exception\ConcurrencyException;
use Gears\EventSourcing\Store\Event\Exception\EventStoreException;
use Gears\EventSourcing\Store\StoreStream;

/**
 * In memory Event Store implementation.
 */
abstract class AbstractEventStore implements EventStore
{
    /**
     * {@inheritdoc}
     */
    public function loadFrom(
        StoreStream $stream,
        AggregateVersion $fromVersion,
        ?int $count = null
    ): AggregateEventStream {
        if ($fromVersion->getValue() < 1) {
            throw new EventStoreException(
                \sprintf('Event store load from version must be at least 1, "%s" given', $fromVersion->getValue())
            );
        }
        if ($count !== null && $count < 1) {
            throw new EventStoreException(\sprintf('Event store load count must be at least 1, "%s" given', $count));
        }

        if (!$this->streamExists($stream)) {
            $this->createStream($stream);

            return new AggregateEventEmptyStream();
        }

        $toVersion = $count !== null
            ? new AggregateVersion($fromVersion->getValue() + $count - 1)
            : null;

        return $this->loadEvents($stream, $fromVersion, $toVersion);
    }

    /**
     * {@inheritdoc}
     */
    public function loadTo(
        StoreStream $stream,
        AggregateVersion $toVersion,
        ?AggregateVersion $fromVersion = null
    ): AggregateEventStream {
        if ($toVersion->getValue() < 1) {
            throw new EventStoreException(
                \sprintf('Event store load to version must be at least 1, "%s" given', $toVersion->getValue())
            );
        }

        $fromVersion = $fromVersion ?? new AggregateVersion(1);
        if ($fromVersion->getValue() < 1) {
            throw new EventStoreException(
                \sprintf('Event store load from version must be at least 1, "%s" given', $fromVersion->getValue())
            );
        }
        if ($fromVersion->getValue() > $toVersion->getValue()) {
            throw new EventStoreException(\sprintf(
                'Event store load to version "%s" must be greater than from version "%s"',
                $toVersion->getValue(),
                $fromVersion->getValue()
            ));
        }

        if (!$this->streamExists($stream)) {
            $this->createStream($stream);

            return new AggregateEventEmptyStream();
        }

        return $this->loadEvents($stream, $fromVersion, $toVersion);
    }

    /**
     * Get aggregate event stream.
     *
     * @param StoreStream           $stream
     * @param AggregateVersion      $fromVersion
     * @param AggregateVersion|null $toVersion
     *
     * @return AggregateEventStream
     */
    abstract protected function loadEvents(
        StoreStream $stream,
        AggregateVersion $fromVersion,
        ?AggregateVersion $toVersion = null
    ): AggregateEventStream;

    /**
     * {@inheritdoc}
     */
    public function store(StoreStream $stream, AggregateEventStream $eventStream): void
    {
        if ($eventStream->count() === 0) {
            return;
        }

        if (!$this->streamExists($stream)) {
            $this->createStream($stream);
        }

        $eventStream->rewind();
        $expectedVersion = $eventStream->current()->getAggregateVersion()->getPrevious();

        $currentVersion = $this->getStreamVersion($stream);
        if (!$currentVersion->isEqualTo($expectedVersion)) {
            throw new ConcurrencyException(\sprintf(
                'Expected stream version "%s" does not match current version "%s"',
                $expectedVersion->getValue(),
                $currentVersion->getValue()
            ));
        }

        foreach ($eventStream as $aggregateEvent) {
            $aggregateVersion = $aggregateEvent->getAggregateVersion();

            if (!$aggregateVersion->getPrevious()->isEqualTo($currentVersion)) {
                throw new ConcurrencyException('Event stream cannot be stored due to versions mismatch');
            }

            $currentVersion = $currentVersion->getNext();
        }

        $eventStream->rewind();
        $this->storeEvents($stream, $eventStream);
    }

    /**
     * Append events to store.
     *
     * @param StoreStream          $stream
     * @param AggregateEventStream $eventStream
     */
    abstract protected function storeEvents(StoreStream $stream, AggregateEventStream $eventStream): void;

    /**
     * Check stream existence.
     *
     * @param StoreStream $stream
     *
     * @return bool
     */
    abstract protected function streamExists(StoreStream $stream): bool;

    /**
     * Create stream.
     *
     * @param StoreStream $stream
     */
    abstract protected function createStream(StoreStream $stream): void;

    /**
     * Get current stream version.
     *
     * @param StoreStream $stream
     *
     * @return AggregateVersion
     */
    abstract protected function getStreamVersion(StoreStream $stream): AggregateVersion;
}
