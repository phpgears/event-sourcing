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

use Gears\Aggregate\EventBehaviour;
use Gears\EventSourcing\Aggregate\Exception\AggregateException;
use Gears\EventSourcing\Event\AggregateEvent;
use Gears\EventSourcing\Event\AggregateEventArrayCollection;
use Gears\EventSourcing\Event\AggregateEventCollection;
use Gears\Identity\Identity;

/**
 * Abstract aggregate root class.
 */
abstract class AbstractAggregateRoot implements AggregateRoot
{
    use AggregateBehaviour, EventBehaviour;

    /**
     * @var AggregateEvent[]
     */
    private $recordedAggregateEvents = [];

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
     * @param Identity $identity
     */
    final protected function setIdentity(Identity $identity): void
    {
        $this->identity = $identity;
    }

    /**
     * {@inheritdoc}
     */
    final public static function reconstituteFromAggregateEvents(AggregateEventCollection $events): self
    {
        $instance = new static();
        $instance->replayAggregateEvents($events);

        return $instance;
    }

    /**
     * {@inheritdoc}
     *
     * @throws AggregateException
     */
    final public function replayAggregateEvents(AggregateEventCollection $events): void
    {
        foreach ($events as $event) {
            $this->version = $event->getAggregateVersion();

            $this->applyAggregateEvent($event);
        }
    }

    /**
     * Record aggregate event.
     *
     * @param AggregateEvent $event
     *
     * @throws AggregateException
     */
    final protected function recordAggregateEvent(AggregateEvent $event): void
    {
        $this->version++;

        $this->recordedAggregateEvents[] = $event->withAggregateVersion($this->version);

        $this->applyAggregateEvent($event);
    }

    /**
     * Apply aggregate event.
     *
     * @param AggregateEvent $event
     *
     * @throws AggregateException
     */
    final protected function applyAggregateEvent(AggregateEvent $event): void
    {
        $method = $this->getAggregateEventApplyMethodName($event);

        if (!\method_exists($this, $method)) {
            throw new AggregateException(\sprintf(
                'Aggregate event handling method %s for event %s does not exist',
                $method,
                \get_class($event)
            ));
        }

        $this->$method($event);
    }

    /**
     * Get event apply method name.
     *
     * @param AggregateEvent $event
     *
     * @return string
     */
    protected function getAggregateEventApplyMethodName(AggregateEvent $event): string
    {
        $classParts = \explode('\\', \get_class($event));
        /** @var string $className */
        $className = \end($classParts);

        return 'apply' . \str_replace(' ', '', \ucwords(\strtr($className, '_-', '  ')));
    }

    /**
     * {@inheritdoc}
     */
    final public function getRecordedAggregateEvents(): AggregateEventCollection
    {
        return new AggregateEventArrayCollection($this->recordedAggregateEvents);
    }

    /**
     * {@inheritdoc}
     */
    final public function clearRecordedAggregateEvents(): void
    {
        $this->recordedAggregateEvents = [];
    }

    /**
     * {@inheritdoc}
     */
    final public function collectRecordedAggregateEvents(): AggregateEventCollection
    {
        $recordedEvents = new AggregateEventArrayCollection($this->recordedAggregateEvents);

        $this->recordedAggregateEvents = [];

        return $recordedEvents;
    }
}
