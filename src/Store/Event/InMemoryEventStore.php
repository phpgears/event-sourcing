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
use Gears\EventSourcing\Event\AggregateEventEmptyStream;
use Gears\EventSourcing\Event\AggregateEventIteratorStream;
use Gears\EventSourcing\Event\AggregateEventStream;
use Gears\EventSourcing\Store\StoreStream;

/**
 * In memory Event Store implementation.
 */
final class InMemoryEventStore extends AbstractEventStore
{
    /**
     * AggregateEvents streams.
     *
     * @var array<string, array<int, AggregateEvent[]>>
     */
    private $streams = [];

    /**
     * {@inheritdoc}
     */
    protected function loadEvents(
        StoreStream $stream,
        AggregateVersion $fromVersion,
        ?AggregateVersion $toVersion = null
    ): AggregateEventStream {
        $streamId = $this->getStreamId($stream);
        if (!isset($this->streams[$streamId][$fromVersion->getValue()])) {
            // @codeCoverageIgnoreStart
            return new AggregateEventEmptyStream();
            // @codeCoverageIgnoreEnd
        }

        $length = $toVersion !== null ? $toVersion->getValue() - $fromVersion->getValue() + 1 : null;
        $events = \array_slice($this->streams[$streamId], $fromVersion->getValue() - 1, $length);

        return new AggregateEventIteratorStream(new \ArrayIterator($events));
    }

    /**
     * {@inheritdoc}
     */
    protected function storeEvents(StoreStream $stream, AggregateEventStream $eventStream): void
    {
        $streamId = $this->getStreamId($stream);

        foreach ($eventStream as $aggregateEvent) {
            $this->streams[$streamId][$aggregateEvent->getAggregateVersion()->getValue()] = $aggregateEvent;
        }

        \ksort($this->streams[$streamId]);
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
