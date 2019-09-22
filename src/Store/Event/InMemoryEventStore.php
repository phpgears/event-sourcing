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
use Gears\EventSourcing\Event\AggregateEvent;
use Gears\EventSourcing\Event\AggregateEventIteratorStream;
use Gears\EventSourcing\Event\AggregateEventStream;
use Gears\EventSourcing\Store\Event\Exception\EventStoreException;
use Gears\EventSourcing\Store\StoreStream;

/**
 * In memory Event Store implementation.
 */
final class InMemoryEventStore extends AbstractEventStore
{
    /**
     * AggregateEvents streams.
     *
     * @var array<AggregateEvent[]>
     */
    private $streams = [];

    /**
     * {@inheritdoc}
     */
    protected function loadEventsFrom(
        StoreStream $stream,
        AggregateVersion $fromVersion,
        ?int $count = null
    ): AggregateEventStream {
        $events = new \ArrayObject();

        $streamId = $this->getStreamId($stream);
        if (!isset($this->streams[$streamId][$fromVersion->getValue()])) {
            // @codeCoverageIgnoreStart
            return new AggregateEventIteratorStream($events->getIterator());
            // @codeCoverageIgnoreEnd
        }

        foreach ($this->streams[$streamId] as $version => $event) {
            if ($version >= $fromVersion->getValue()) {
                $events->append($event);
            }

            if ($count !== null && \count($events) === $count) {
                break;
            }
        }

        return new AggregateEventIteratorStream($events->getIterator());
    }

    /**
     * {@inheritdoc}
     */
    protected function loadEventsTo(
        StoreStream $stream,
        AggregateVersion $toVersion,
        AggregateVersion $fromVersion
    ): AggregateEventStream {
        $events = new \ArrayObject();

        $streamId = $this->getStreamId($stream);
        if (!isset($this->streams[$streamId][$fromVersion->getValue()])) {
            // @codeCoverageIgnoreStart
            return new AggregateEventIteratorStream($events->getIterator());
            // @codeCoverageIgnoreEnd
        }

        foreach ($this->streams[$streamId] as $version => $event) {
            if ($version >= $fromVersion->getValue() && $version <= $toVersion->getValue()) {
                $events->append($event);
            }

            if ($version >= $toVersion->getValue()) {
                break;
            }
        }

        return new AggregateEventIteratorStream($events->getIterator());
    }

    /**
     * {@inheritdoc}
     */
    protected function storeEvents(
        StoreStream $stream,
        AggregateEventStream $eventStream,
        AggregateVersion $expectedVersion
    ): void {
        $lastVersion = $this->getStreamVersion($stream);

        $streamId = $this->getStreamId($stream);

        foreach ($eventStream as $aggregateEvent) {
            $aggregateVersion = $aggregateEvent->getAggregateVersion();

            if (!$aggregateVersion->getPrevious()->isEqualTo($lastVersion)) {
                throw new EventStoreException(\sprintf(
                    'Aggregate event for version "%s" cannot be stored',
                    $aggregateVersion->getValue()
                ));
            }

            $this->streams[$streamId][$aggregateVersion->getValue()] = $aggregateEvent;

            $lastVersion = $lastVersion->getNext();
        }

        \ksort($this->streams[$streamId]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getStreamVersion(StoreStream $stream): AggregateVersion
    {
        $streamId = $this->getStreamId($stream);

        if (\count($this->streams[$streamId]) === 0) {
            return new AggregateVersion(0);
        }

        $versions = \array_keys($this->streams[$streamId]);
        /** @var int $version */
        $version = \end($versions);

        return new AggregateVersion($version);
    }

    /**
     * {@inheritdoc}
     */
    protected function streamExists(StoreStream $stream): bool
    {
        return isset($this->streams[$this->getStreamId($stream)]);
    }

    /**
     * {@inheritdoc}
     */
    protected function createStream(StoreStream $stream): void
    {
        $this->streams[$this->getStreamId($stream)] = [];
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
