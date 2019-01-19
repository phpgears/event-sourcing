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

namespace Gears\EventSourcing\Aggregate;

use Gears\Aggregate\AggregateIdentity;
use Gears\EventSourcing\Aggregate\Exception\AggregateException;
use Gears\EventSourcing\Event\AggregateEvent;
use Gears\EventSourcing\Event\AggregateEventArrayCollection;
use Gears\EventSourcing\Event\AggregateEventCollection;

/**
 * Abstract aggregate root class.
 */
abstract class AbstractAggregateRoot implements AggregateRoot
{
    use AggregateBehaviour;

    /**
     * @var AggregateEvent[]
     */
    private $recordedEvents = [];

    /**
     * Prevent aggregate root direct instantiation.
     */
    final protected function __construct()
    {
        $this->version = 0;
    }

    /**
     * Set aggregate identity.
     *
     * @param AggregateIdentity $identity
     */
    final protected function setIdentity(AggregateIdentity $identity): void
    {
        $this->identity = $identity;
    }

    /**
     * {@inheritdoc}
     */
    final public function collectRecordedEvents(): AggregateEventCollection
    {
        $recordedEvents = new AggregateEventArrayCollection($this->recordedEvents);

        $this->recordedEvents = [];

        return $recordedEvents;
    }

    /**
     * {@inheritdoc}
     */
    final public static function reconstituteFromEvents(AggregateEventCollection $events): self
    {
        $instance = new static();
        $instance->replayEvents($events);

        return $instance;
    }

    /**
     * {@inheritdoc}
     *
     * @throws AggregateException
     */
    final public function replayEvents(AggregateEventCollection $events): void
    {
        foreach ($events as $event) {
            $this->version = $event->getAggregateVersion();

            $this->applyEvent($event);
        }
    }

    /**
     * Record event.
     *
     * @param AggregateEvent $event
     *
     * @throws AggregateException
     */
    final protected function recordEvent(AggregateEvent $event): void
    {
        $this->version++;

        $this->recordedEvents[] = $event->withAggregateVersion($this->version);

        $this->applyEvent($event);
    }

    /**
     * Apply event.
     *
     * @param AggregateEvent $event
     *
     * @throws AggregateException
     */
    protected function applyEvent(AggregateEvent $event): void
    {
        $eventParts = \explode('\\', \ucfirst(\get_class($event)));
        $method = 'apply' . \end($eventParts);

        if (!\method_exists($this, $method)) {
            throw new AggregateException(\sprintf(
                'Aggregate event handling method %s for event %s does not exist',
                $method,
                \get_class($event)
            ));
        }

        $this->$method($event);
    }
}
