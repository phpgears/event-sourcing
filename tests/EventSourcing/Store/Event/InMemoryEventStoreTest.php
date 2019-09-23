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

namespace Gears\EventSourcing\Tests\Store\Event;

use Gears\EventSourcing\Aggregate\AggregateVersion;
use Gears\EventSourcing\Event\AggregateEventArrayStream;
use Gears\EventSourcing\Store\Event\InMemoryEventStore;
use Gears\EventSourcing\Store\GenericStoreStream;
use Gears\EventSourcing\Tests\Stub\AbstractAggregateEventStub;
use Gears\EventSourcing\Tests\Stub\AbstractAggregateRootStub;
use Gears\Identity\Identity;
use PHPUnit\Framework\TestCase;

/**
 * In memory event store test.
 */
class InMemoryEventStoreTest extends TestCase
{
    public function testLoadFrom(): void
    {
        $identity = $this->getMockBuilder(Identity::class)->disableOriginalConstructor()->getMock();
        $identity->expects(static::any())
            ->method('getValue')
            ->will(static::returnValue('aaa'));
        /** @var Identity $identity */
        $stream = GenericStoreStream::fromAggregateData(AbstractAggregateRootStub::class, $identity);

        $event = AbstractAggregateEventStub::instance($identity);

        $eventStore = (new InMemoryEventStore());
        $eventStore->store(
            $stream,
            new AggregateEventArrayStream([
                $event->withAggregateVersion(new AggregateVersion(1)),
                $event->withAggregateVersion(new AggregateVersion(2)),
                $event->withAggregateVersion(new AggregateVersion(3)),
            ])
        );
        $eventStore->store(
            $stream,
            new AggregateEventArrayStream([
                $event->withAggregateVersion(new AggregateVersion(4)),
                $event->withAggregateVersion(new AggregateVersion(5)),
            ])
        );

        $loadedEvents = $eventStore->loadFrom($stream, new AggregateVersion(2), 2);

        static::assertCount(2, $loadedEvents);
        static::assertEquals(2, $loadedEvents->current()->getAggregateVersion()->getValue());
        $loadedEvents->next();
        static::assertEquals(3, $loadedEvents->current()->getAggregateVersion()->getValue());

        $loadedEvents = $eventStore->loadFrom($stream, new AggregateVersion(3));

        static::assertCount(3, $loadedEvents);
        static::assertEquals(3, $loadedEvents->current()->getAggregateVersion()->getValue());
        $loadedEvents->next();
        static::assertEquals(4, $loadedEvents->current()->getAggregateVersion()->getValue());
        $loadedEvents->next();
        static::assertEquals(5, $loadedEvents->current()->getAggregateVersion()->getValue());
    }

    public function testLoadTo(): void
    {
        $identity = $this->getMockBuilder(Identity::class)->disableOriginalConstructor()->getMock();
        $identity->expects(static::any())
            ->method('getValue')
            ->will(static::returnValue('aaa'));
        /** @var Identity $identity */
        $stream = GenericStoreStream::fromAggregateData(AbstractAggregateRootStub::class, $identity);

        $event = AbstractAggregateEventStub::instance($identity);

        $eventStore = (new InMemoryEventStore());
        $eventStore->store(
            $stream,
            new AggregateEventArrayStream([
                $event->withAggregateVersion(new AggregateVersion(1)),
                $event->withAggregateVersion(new AggregateVersion(2)),
                $event->withAggregateVersion(new AggregateVersion(3)),
            ])
        );
        $eventStore->store(
            $stream,
            new AggregateEventArrayStream([
                $event->withAggregateVersion(new AggregateVersion(4)),
                $event->withAggregateVersion(new AggregateVersion(5)),
            ])
        );

        $loadedEvents = $eventStore->loadTo($stream, new AggregateVersion(4), new AggregateVersion(2));

        static::assertCount(3, $loadedEvents);
        static::assertEquals(2, $loadedEvents->current()->getAggregateVersion()->getValue());
        $loadedEvents->next();
        static::assertEquals(3, $loadedEvents->current()->getAggregateVersion()->getValue());
        $loadedEvents->next();
        static::assertEquals(4, $loadedEvents->current()->getAggregateVersion()->getValue());

        $loadedEvents = $eventStore->loadTo($stream, new AggregateVersion(3));

        static::assertCount(3, $loadedEvents);
        static::assertEquals(1, $loadedEvents->current()->getAggregateVersion()->getValue());
        $loadedEvents->next();
        static::assertEquals(2, $loadedEvents->current()->getAggregateVersion()->getValue());
        $loadedEvents->next();
        static::assertEquals(3, $loadedEvents->current()->getAggregateVersion()->getValue());
    }
}
