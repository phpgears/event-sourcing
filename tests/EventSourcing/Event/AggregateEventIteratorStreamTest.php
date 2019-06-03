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
use Gears\EventSourcing\Tests\Stub\AbstractEmptyAggregateEventStub;
use Gears\Identity\UuidIdentity;
use PHPUnit\Framework\TestCase;

/**
 * Aggregate event iterator stream test.
 */
class AggregateEventIteratorStreamTest extends TestCase
{
    /**
     * @expectedException \Gears\EventSourcing\Event\Exception\InvalidAggregateEventException
     * @expectedExceptionMessageRegExp /Aggregate event stream only accepts .+, string given/
     */
    public function testInvalidTypeStream(): void
    {
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

        $eventStream->next();
        $currentKey = $eventStream->key();
        $this->assertCount(2, $eventStream);
        $this->assertEquals($currentKey, $eventStream->key());

        foreach ($eventStream as $event) {
            $this->assertInstanceOf(AggregateEvent::class, $event);
        }

        $this->assertNull($eventStream->key());
    }
}
