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
use Gears\EventSourcing\Aggregate\Exception\AggregateVersionException;
use Gears\EventSourcing\Aggregate\Serializer\Exception\AggregateSerializationException;
use Gears\EventSourcing\Event\AggregateEvent;
use Gears\EventSourcing\Event\AggregateEventIteratorStream;
use Gears\EventSourcing\Event\AggregateEventStream;
use Gears\Identity\Identity;

/**
 * Abstract aggregate root class.
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
abstract class AbstractAggregateRoot implements AggregateRoot
{
    use AggregateBehaviour, EventBehaviour;

    /**
     * @var \ArrayObject<string, AggregateEvent>|null
     */
    private $recordedAggregateEvents;

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
     * @throws AggregateVersionException
     */
    final public function replayAggregateEventStream(AggregateEventStream $eventStream): void
    {
        foreach ($eventStream as $event) {
            $aggregateVersion = $event->getAggregateVersion();

            if (!$this->version->getNext()->isEqualTo($aggregateVersion)) {
                throw new AggregateVersionException(\sprintf(
                    'Aggregate event "%s" cannot be replayed, event version is "%s" and aggregate is "%s"',
                    \get_class($event),
                    $aggregateVersion->getValue(),
                    $this->version->getValue()
                ));
            }

            $this->applyAggregateEvent($event);

            $this->version = $aggregateVersion;
        }
    }

    /**
     * Record aggregate event.
     *
     * @param AggregateEvent $event
     *
     * @throws AggregateVersionException
     */
    final protected function recordAggregateEvent(AggregateEvent $event): void
    {
        if (!$event->getAggregateVersion()->isEqualTo(new AggregateVersion(0))) {
            throw new AggregateVersionException(\sprintf(
                'Only new aggregate events can be recorded, event "%s" with version "%s" given',
                \get_class($event),
                $event->getAggregateVersion()->getValue()
            ));
        }

        $this->applyAggregateEvent($event);

        $this->version = $this->version->getNext();

        /** @var AggregateEvent $recordedEvent */
        $recordedEvent = $event::reconstitute(
            $event->getPayload(),
            $event->getCreatedAt(),
            [
                'aggregateId' => $event->getAggregateId(),
                'aggregateVersion' => $this->version,
                'metadata' => $event->getMetadata(),
            ]
        );

        if ($this->recordedAggregateEvents === null) {
            $this->recordedAggregateEvents = new \ArrayObject();
        }

        $this->recordedAggregateEvents->append($recordedEvent);
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
                'Aggregate event handling method "%s" for event "%s" does not exist',
                $method,
                \get_class($event)
            ));
        }

        /** @var callable $callable */
        $callable = [$this, $method];

        \call_user_func($callable, $event);
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
        $typeParts = \explode('\\', $event->getEventType());
        /** @var string $eventType */
        $eventType = \end($typeParts);

        return 'apply' . \str_replace(' ', '', \ucwords(\strtr($eventType, '_-', '  ')));
    }

    /**
     * {@inheritdoc}
     */
    final public function getRecordedAggregateEvents(): AggregateEventStream
    {
        return new AggregateEventIteratorStream(
            $this->recordedAggregateEvents !== null
                ? $this->recordedAggregateEvents->getIterator()
                : new \EmptyIterator()
        );
    }

    /**
     * {@inheritdoc}
     */
    final public function clearRecordedAggregateEvents(): void
    {
        $this->recordedAggregateEvents = null;
    }

    /**
     * {@inheritdoc}
     */
    final public function collectRecordedAggregateEvents(): AggregateEventStream
    {
        $recordedEvents = new AggregateEventIteratorStream(
            $this->recordedAggregateEvents !== null
                ? $this->recordedAggregateEvents->getIterator()
                : new \EmptyIterator()
        );

        $this->recordedAggregateEvents = null;

        return $recordedEvents;
    }

    /**
     * @return array<string, mixed>
     */
    final public function __serialize(): array
    {
        return $this->getSerializationAttributes();
    }

    /**
     * @param array<string, mixed> $data
     */
    final public function __unserialize(array $data): void
    {
        $this->unserializeAttributes($data);
    }

    /**
     * {@inheritdoc}
     */
    final public function serialize(): string
    {
        return \serialize($this->getSerializationAttributes());
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $serialized
     */
    final public function unserialize($serialized): void
    {
        $this->unserializeAttributes(\unserialize($serialized));
    }

    /**
     * Get serialization data.
     *
     * @throws AggregateSerializationException
     *
     * @return array<string, mixed>
     */
    private function getSerializationAttributes(): array
    {
        if (($this->recordedAggregateEvents !== null && $this->recordedAggregateEvents->count() !== 0)
            || ($this->recordedEvents !== null && $this->recordedEvents->count() !== 0)
        ) {
            throw new AggregateSerializationException('Aggregate root with recorded events cannot be serialized');
        }

        $attributes = [];
        foreach ((new \ReflectionObject($this))->getProperties() as $reflectionProperty) {
            if (!$reflectionProperty->isStatic()) {
                $reflectionProperty->setAccessible(true);
                $attributes[$reflectionProperty->getName()] = $reflectionProperty->getValue($this);
            }
        }

        $attributes['identity'] = $this->identity;
        $attributes['version'] = $this->version;

        return $attributes;
    }

    /**
     * Unserialize attributes.
     *
     * @param array<string, mixed> $attributes
     */
    private function unserializeAttributes(array $attributes): void
    {
        foreach ($attributes as $attribute => $value) {
            if (!\in_array($attribute, ['recordedAggregateEvents', 'recordedEvents'], true)) {
                $this->{$attribute} = $value;
            }
        }
    }
}
