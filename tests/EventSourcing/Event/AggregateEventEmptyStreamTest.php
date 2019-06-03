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

use Gears\EventSourcing\Event\AggregateEventEmptyStream;
use PHPUnit\Framework\TestCase;

/**
 * Aggregate event empty stream test.
 */
class AggregateEventEmptyStreamTest extends TestCase
{
    /**
     * @expectedException \Gears\EventSourcing\Event\Exception\AggregateEventStreamException
     * @expectedExceptionMessage Current method must not be called on AggregateEventEmptyStream
     */
    public function testInvalidTypeStream(): void
    {
        (new AggregateEventEmptyStream())->current();
    }

    public function testStream(): void
    {
        $eventStream = new AggregateEventEmptyStream();

        $eventStream->rewind();
        $eventStream->next();

        $this->assertCount(0, $eventStream);
        $this->assertFalse($eventStream->valid());
    }
}
