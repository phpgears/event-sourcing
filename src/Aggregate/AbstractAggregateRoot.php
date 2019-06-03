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
use Gears\EventSourcing\Event\AggregateEventArrayStream;
use Gears\EventSourcing\Event\AggregateEventStream;
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
        $this->version = new AggregateVersion(0);
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
    final public static function reconstituteFromEventStream(AggregateEventStream $eventStream): self
    {
        $instance = new static();
        $instance->replayAggregateEventStream($eventStream);

        if ($instance->getVersion()->isEqualTo(new AggregateVersion(0))) {
            throw new AggregateException('Aggregate cannot be reconstituted from empty event stream');
        }

        return $instance;
    }

    /**
     * {@inheritdoc}
     *
     * @throws AggregateException
     */
    final public function replayAggregateEventStream(AggregateEventStream $eventStream): void
    {
        foreach ($eventStream as $event) {
            $aggregateVersion = $event->getAggregateVersion();

            if (!$aggregateVersion->isEqualTo($this->version->getNext())) {
                throw new AggregateException(\sprintf(
                    'Aggregate event %s cannot be replayed, event version is %s and aggregate is %s',
                    \get_class($event),
                    $aggregateVersion->getValue(),
                    $this->version->getValue()
                ));
            }

            $this->version = $aggregateVersion;

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
        if (!$event->getAggregateVersion()->isEqualTo(new AggregateVersion(0))) {
            throw new AggregateException(\sprintf(
                'Only new aggregate events can be recorded, event %s with version %s given',
                \get_class($event),
                $event->getAggregateVersion()->getValue()
            ));
        }

        $this->version = $this->version->getNext();

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
    final public function getRecordedAggregateEvents(): AggregateEventStream
    {
        return new AggregateEventArrayStream($this->recordedAggregateEvents);
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
    final public function collectRecordedAggregateEvents(): AggregateEventStream
    {
        $recordedEvents = new AggregateEventArrayStream($this->recordedAggregateEvents);

        $this->recordedAggregateEvents = [];

        return $recordedEvents;
    }
}
