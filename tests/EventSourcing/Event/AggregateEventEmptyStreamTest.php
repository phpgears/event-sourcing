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
use Gears\EventSourcing\Event\Exception\AggregateEventStreamException;
use PHPUnit\Framework\TestCase;

/**
 * Aggregate event empty stream test.
 */
class AggregateEventEmptyStreamTest extends TestCase
{
    public function testInvalidTypeStream(): void
    {
        $this->expectException(AggregateEventStreamException::class);
        $this->expectExceptionMessage('Current method must not be called on AggregateEventEmptyStream');

        (new AggregateEventEmptyStream())->current();
    }

    public function testStream(): void
    {
        $eventStream = new AggregateEventEmptyStream();

        $eventStream->rewind();
        $eventStream->next();

        static::assertCount(0, $eventStream);
        static::assertFalse($eventStream->valid());
    }
}
