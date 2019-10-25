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

namespace Gears\EventSourcing\Tests\Event;

use Gears\EventSourcing\Event\AggregateEvent;
use Gears\EventSourcing\Event\AggregateEventIteratorStream;
use Gears\EventSourcing\Event\Exception\InvalidAggregateEventException;
use Gears\EventSourcing\Tests\Stub\AbstractEmptyAggregateEventStub;
use Gears\Identity\UuidIdentity;
use PHPUnit\Framework\TestCase;

/**
 * Aggregate event iterator stream test.
 */
class AggregateEventIteratorStreamTest extends TestCase
{
    public function testInvalidTypeStream(): void
    {
        $this->expectException(InvalidAggregateEventException::class);
        $this->expectExceptionMessageRegExp('/^Aggregate event stream only accepts ".+", "string" given$/');

        (new AggregateEventIteratorStream(new \ArrayIterator(['event'])))->current();
    }

    public function testStream(): void
    {
        $identity = UuidIdentity::fromString('3247cb6e-e9c7-4f3a-9c6c-0dec26a0353f');

        $events = [
            AbstractEmptyAggregateEventStub::instance($identity),
            AbstractEmptyAggregateEventStub::instance($identity),
        ];
        $eventStream = new AggregateEventIteratorStream(new \ArrayIterator($events));

        static::assertCount(2, $eventStream);

        foreach ($eventStream as $event) {
            static::assertInstanceOf(AggregateEvent::class, $event);
        }

        static::assertNull($eventStream->key());
    }

    public function testCollectionCountEmpty(): void
    {
        $collection = new AggregateEventIteratorStream(new \EmptyIterator());

        static::assertCount(0, $collection);
    }

    public function testStreamCountCountable(): void
    {
        $identity = UuidIdentity::fromString('3247cb6e-e9c7-4f3a-9c6c-0dec26a0353f');

        $events = [
            AbstractEmptyAggregateEventStub::instance($identity),
            AbstractEmptyAggregateEventStub::instance($identity),
        ];
        $collection = new AggregateEventIteratorStream(new \ArrayIterator($events));

        static::assertCount(2, $collection);
    }

    public function testStreamCountNonCountable(): void
    {
        $identity = UuidIdentity::fromString('3247cb6e-e9c7-4f3a-9c6c-0dec26a0353f');

        $events = [
            AbstractEmptyAggregateEventStub::instance($identity),
            AbstractEmptyAggregateEventStub::instance($identity),
        ];
        $collection = new AggregateEventIteratorStream(new \IteratorIterator(new \ArrayIterator($events)));

        $collection->next();
        $currentKey = $collection->key();
        static::assertCount(2, $collection);
        static::assertEquals($currentKey, $collection->key());
    }
}
