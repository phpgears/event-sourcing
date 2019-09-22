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

interface AggregateSerializer
{
    /**
     * Serialize an aggregate root.
     *
     * @param AggregateRoot $aggregateRoot
     *
     * @return string
     */
    public function serialize(AggregateRoot $aggregateRoot): string;

    /**
     * Deserialize an aggregate root.
     *
     * @param string $serialized
     *
     * @throws \Gears\EventSourcing\Aggregate\Serializer\Exception\AggregateSerializationException
     *
     * @return AggregateRoot
     */
    public function fromSerialized(string $serialized): AggregateRoot;
}
