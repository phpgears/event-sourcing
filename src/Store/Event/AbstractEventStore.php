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

        return $this->loadEventsFrom($stream, $fromVersion, $count);
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

        return $this->loadEventsTo($stream, $toVersion, $fromVersion);
    }

    /**
     * {@inheritdoc}
     */
    public function store(
        StoreStream $stream,
        AggregateEventStream $eventStream,
        AggregateVersion $expectedVersion
    ): void {
        if ($eventStream->count() === 0) {
            return;
        }
        $eventStream->rewind();

        if (!$this->streamExists($stream)) {
            $this->createStream($stream);
        }

        $startVersion = $this->getStreamVersion($stream);
        if (!$startVersion->isEqualTo($expectedVersion)) {
            throw new ConcurrencyException(\sprintf(
                'Expected stream version "%s" does not match current version "%s"',
                $expectedVersion->getValue(),
                $startVersion->getValue()
            ));
        }

        $this->storeEvents($stream, $eventStream, $expectedVersion);

        $eventStream->rewind();
        $events = \iterator_to_array($eventStream);
        /** @var AggregateVersion $finalVersion */
        $finalVersion = \end($events)->getAggregateVersion();

        $endVersion = $this->getStreamVersion($stream);
        if (!$endVersion->isEqualTo($finalVersion)) {
            throw new ConcurrencyException(\sprintf(
                'Expected final stream version "%s" does not match current version "%s"',
                $finalVersion->getValue(),
                $endVersion->getValue()
            ));
        }
    }

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
     * Load aggregate events from a version.
     *
     * @param StoreStream      $stream
     * @param AggregateVersion $fromVersion
     * @param int|null         $count
     *
     * @return AggregateEventStream
     */
    abstract protected function loadEventsFrom(
        StoreStream $stream,
        AggregateVersion $fromVersion,
        ?int $count = null
    ): AggregateEventStream;

    /**
     * Load aggregate events up to a version.
     *
     * @param StoreStream      $stream
     * @param AggregateVersion $toVersion
     * @param AggregateVersion $fromVersion
     *
     * @return AggregateEventStream
     */
    abstract protected function loadEventsTo(
        StoreStream $stream,
        AggregateVersion $toVersion,
        AggregateVersion $fromVersion
    ): AggregateEventStream;

    /**
     * Append events to store.
     *
     * @param StoreStream          $stream
     * @param AggregateEventStream $eventStream
     * @param AggregateVersion     $expectedVersion
     */
    abstract protected function storeEvents(
        StoreStream $stream,
        AggregateEventStream $eventStream,
        AggregateVersion $expectedVersion
    ): void;

    /**
     * Get current stream version.
     *
     * @param StoreStream $stream
     *
     * @return AggregateVersion
     */
    abstract protected function getStreamVersion(StoreStream $stream): AggregateVersion;
}
