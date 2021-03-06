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

namespace Gears\EventSourcing\Tests\Stub;

use Gears\EventSourcing\Aggregate\AggregateVersion;
use Gears\EventSourcing\Event\AggregateEvent;
use Gears\EventSourcing\Event\AggregateEventIteratorStream;
use Gears\EventSourcing\Event\AggregateEventStream;
use Gears\EventSourcing\Store\Event\AbstractEventStore;
use Gears\EventSourcing\Store\StoreStream;

/**
 * Abstract event store stub class.
 */
class AbstractEventStoreStub extends AbstractEventStore
{
    /**
     * @var AggregateVersion
     */
    protected $version;

    /**
     * @var string
     */
    protected $streamName;

    /**
     * @var AggregateEvent[]
     */
    protected $stream = [];

    /**
     * @var bool
     */
    protected $doNotStore;

    /**
     * AbstractEmptyEventStoreStub constructor.
     *
     * @param string|null $streamName
     * @param bool        $doNotStore
     */
    public function __construct(?string $streamName = null, bool $doNotStore = false)
    {
        $this->version = new AggregateVersion(0);
        $this->streamName = $streamName;
        $this->doNotStore = $doNotStore;
    }

    /**
     * {@inheritdoc}
     */
    protected function streamExists(StoreStream $stream): bool
    {
        return $this->streamName === $stream->getAggregateId()->getValue();
    }

    /**
     * {@inheritdoc}
     */
    protected function createStream(StoreStream $stream): void
    {
        $this->streamName = $stream->getAggregateId()->getValue();
    }

    /**
     * {@inheritdoc}
     */
    protected function loadEvents(
        StoreStream $stream,
        AggregateVersion $fromVersion,
        ?AggregateVersion $toVersion = null
    ): AggregateEventStream {
        if ($toVersion !== null) {
            $events = \array_slice(
                $this->stream,
                $fromVersion->getValue() - 1,
                $toVersion->getValue() - $fromVersion->getValue() + 1
            );
        } else {
            $events = \array_slice($this->stream, $fromVersion->getValue() - 1);
        }

        return new AggregateEventIteratorStream((new \ArrayObject($events))->getIterator());
    }

    /**
     * {@inheritdoc}
     */
    protected function storeEvents(StoreStream $stream, AggregateEventStream $eventStream): void
    {
        if ($this->doNotStore === true) {
            return;
        }

        foreach ($eventStream as $event) {
            $this->stream[] = $event;

            $this->version = $event->getAggregateVersion();
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getStreamVersion(StoreStream $stream): AggregateVersion
    {
        return $this->version;
    }
}
