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

namespace Gears\Event\Tests;

use Gears\EventSourcing\Event\AggregateEvent;
use Gears\EventSourcing\Event\AggregateEventIteratorCollection;
use Gears\EventSourcing\Tests\Stub\AbstractEmptyAggregateEventStub;
use Gears\Identity\UuidIdentity;
use PHPUnit\Framework\TestCase;

/**
 * Aggregate event iterator collection test.
 */
class AggregateEventIteratorCollectionTest extends TestCase
{
    /**
     * @expectedException \Gears\EventSourcing\Event\Exception\InvalidAggregateEventException
     * @expectedExceptionMessageRegExp /Aggregate event collection only accepts .+, string given/
     */
    public function testInvalidTypeCollection(): void
    {
        (new AggregateEventIteratorCollection(new \ArrayIterator(['event'])))->current();
    }

    public function testCollection(): void
    {
        $identity = UuidIdentity::fromString('3247cb6e-e9c7-4f3a-9c6c-0dec26a0353f');

        $events = [
            AbstractEmptyAggregateEventStub::instance($identity),
            AbstractEmptyAggregateEventStub::instance($identity),
        ];
        $collection = new AggregateEventIteratorCollection(new \ArrayIterator($events));

        foreach ($collection as $event) {
            $this->assertInstanceOf(AggregateEvent::class, $event);
        }

        $this->assertNull($collection->key());
    }
}
