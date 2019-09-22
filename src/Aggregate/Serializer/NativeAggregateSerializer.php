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

namespace Gears\EventSourcing\Aggregate\Serializer;

use Gears\EventSourcing\Aggregate\AggregateRoot;
use Gears\EventSourcing\Aggregate\Serializer\Exception\AggregateSerializationException;

/**
 * PHP native aggregate root serializer.
 */
final class NativeAggregateSerializer implements AggregateSerializer
{
    /**
     * {@inheritdoc}
     */
    public function serialize(AggregateRoot $aggregateRoot): string
    {
        return \serialize($aggregateRoot);
    }

    /**
     * {@inheritdoc}
     */
    public function fromSerialized(string $serialized): AggregateRoot
    {
        $aggregateRoot = \unserialize($serialized);

        if (!$aggregateRoot instanceof AggregateRoot) {
            throw new AggregateSerializationException('Invalid unserialized aggregate root');
        }

        return $aggregateRoot;
    }
}
