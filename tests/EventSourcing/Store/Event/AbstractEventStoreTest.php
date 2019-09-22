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
use Gears\EventSourcing\Event\AggregateEventEmptyStream;
use Gears\EventSourcing\Store\Event\Exception\ConcurrencyException;
use Gears\EventSourcing\Store\Event\Exception\EventStoreException;
use Gears\EventSourcing\Store\GenericStoreStream;
use Gears\EventSourcing\Tests\Stub\AbstractAggregateEventStub;
use Gears\EventSourcing\Tests\Stub\AbstractAggregateRootStub;
use Gears\EventSourcing\Tests\Stub\AbstractEventStoreStub;
use Gears\Identity\Identity;
use PHPUnit\Framework\TestCase;

/**
 * Abstract event store test.
 */
class AbstractEventStoreTest extends TestCase
{
    public function testInvalidLoadFromVersion(): void
    {
        $this->expectException(EventStoreException::class);
        $this->expectExceptionMessage('Event store load from version must be at least 1, "0" given');

        /** @var Identity $identity */
        $identity = $this->getMockBuilder(Identity::class)->disableOriginalConstructor()->getMock();

        $stream = GenericStoreStream::fromAggregateData(AbstractAggregateRootStub::class, $identity);
        (new AbstractEventStoreStub())->loadFrom($stream, new AggregateVersion(0));
    }

    public function testInvalidLoadFromCount(): void
    {
        $this->expectException(EventStoreException::class);
        $this->expectExceptionMessage('Event store load count must be at least 1, "0" given');

        /** @var Identity $identity */
        $identity = $this->getMockBuilder(Identity::class)->disableOriginalConstructor()->getMock();

        $stream = GenericStoreStream::fromAggregateData(AbstractAggregateRootStub::class, $identity);
        (new AbstractEventStoreStub())->loadFrom($stream, new AggregateVersion(1), 0);
    }

    public function testLoadFromUnknownEventStream(): void
    {
        $identity = $this->getMockBuilder(Identity::class)->disableOriginalConstructor()->getMock();
        $identity->expects(static::any())
            ->method('getValue')
            ->will(static::returnValue('aaa'));
        /** @var Identity $identity */
        $stream = GenericStoreStream::fromAggregateData(AbstractAggregateRootStub::class, $identity);
        $eventStream = (new AbstractEventStoreStub())
            ->loadFrom($stream, new AggregateVersion(1));

        static::assertCount(0, $eventStream);
    }

    public function testLoadFromEmptyEventStream(): void
    {
        $identity = $this->getMockBuilder(Identity::class)->disableOriginalConstructor()->getMock();
        $identity->expects(static::any())
            ->method('getValue')
            ->will(static::returnValue('aaa'));
        /** @var Identity $identity */
        $stream = GenericStoreStream::fromAggregateData(AbstractAggregateRootStub::class, $identity);
        $eventStream = (new AbstractEventStoreStub($identity->getValue()))
            ->loadFrom($stream, new AggregateVersion(1));

        static::assertCount(0, $eventStream);
    }

    public function testInvalidLoadToVersion(): void
    {
        $this->expectException(EventStoreException::class);
        $this->expectExceptionMessage('Event store load to version must be at least 1, "0" given');

        /** @var Identity $identity */
        $identity = $this->getMockBuilder(Identity::class)->disableOriginalConstructor()->getMock();

        $stream = GenericStoreStream::fromAggregateData(AbstractAggregateRootStub::class, $identity);
        (new AbstractEventStoreStub())->loadTo($stream, new AggregateVersion(0));
    }

    public function testInvalidLoadToFrom(): void
    {
        $this->expectException(EventStoreException::class);
        $this->expectExceptionMessage('Event store load from version must be at least 1, "0" given');

        /** @var Identity $identity */
        $identity = $this->getMockBuilder(Identity::class)->disableOriginalConstructor()->getMock();

        $stream = GenericStoreStream::fromAggregateData(AbstractAggregateRootStub::class, $identity);
        (new AbstractEventStoreStub())->loadTo($stream, new AggregateVersion(1), new AggregateVersion(0));
    }

    public function testInvalidLoadToLessThanFrom(): void
    {
        $this->expectException(EventStoreException::class);
        $this->expectExceptionMessage('Event store load to version "1" must be greater than from version "10"');

        /** @var Identity $identity */
        $identity = $this->getMockBuilder(Identity::class)->disableOriginalConstructor()->getMock();

        $stream = GenericStoreStream::fromAggregateData(AbstractAggregateRootStub::class, $identity);
        (new AbstractEventStoreStub())->loadTo($stream, new AggregateVersion(1), new AggregateVersion(10));
    }

    public function testLoadToUnknownEventStream(): void
    {
        /** @var Identity $identity */
        $identity = $this->getMockBuilder(Identity::class)->disableOriginalConstructor()->getMock();

        $stream = GenericStoreStream::fromAggregateData(AbstractAggregateRootStub::class, $identity);
        $eventStream = (new AbstractEventStoreStub())->loadTo($stream, new AggregateVersion(1));

        static::assertCount(0, $eventStream);
    }

    public function testLoadToEmptyEventStream(): void
    {
        /** @var Identity $identity */
        $identity = $this->getMockBuilder(Identity::class)->disableOriginalConstructor()->getMock();

        $stream = GenericStoreStream::fromAggregateData(AbstractAggregateRootStub::class, $identity);
        $eventStream = (new AbstractEventStoreStub($identity->getValue()))
            ->loadTo($stream, new AggregateVersion(1));

        static::assertCount(0, $eventStream);
    }

    public function testStoreInvalidVersion(): void
    {
        $this->expectException(ConcurrencyException::class);
        $this->expectExceptionMessage('Expected stream version "10" does not match current version "0"');

        $identity = $this->getMockBuilder(Identity::class)->disableOriginalConstructor()->getMock();
        $identity->expects(static::any())
            ->method('getValue')
            ->will(static::returnValue('aaa'));
        /** @var Identity $identity */
        $stream = GenericStoreStream::fromAggregateData(AbstractAggregateRootStub::class, $identity);

        $event = AbstractAggregateEventStub::instance($identity);
        $eventStream = new AggregateEventArrayStream([$event->withAggregateVersion(new AggregateVersion(10))]);

        (new AbstractEventStoreStub())->store($stream, $eventStream, new AggregateVersion(10));
    }

    public function testStoreEmpty(): void
    {
        $identity = $this->getMockBuilder(Identity::class)->disableOriginalConstructor()->getMock();
        $identity->expects(static::any())
            ->method('getValue')
            ->will(static::returnValue('aaa'));
        /** @var Identity $identity */
        $stream = GenericStoreStream::fromAggregateData(AbstractAggregateRootStub::class, $identity);

        $eventStream = new AggregateEventEmptyStream();

        $eventStore = (new AbstractEventStoreStub());
        $eventStore->store($stream, $eventStream, new AggregateVersion(1));

        $loadedEvents = $eventStore->loadFrom($stream, new AggregateVersion(2));
        static::assertCount(0, $loadedEvents);
    }

    public function testStoreError(): void
    {
        $this->expectException(ConcurrencyException::class);
        $this->expectExceptionMessage('Expected final stream version "1" does not match');

        $identity = $this->getMockBuilder(Identity::class)->disableOriginalConstructor()->getMock();
        $identity->expects(static::any())
            ->method('getValue')
            ->will(static::returnValue('aaa'));
        /** @var Identity $identity */
        $stream = GenericStoreStream::fromAggregateData(AbstractAggregateRootStub::class, $identity);

        $event = AbstractAggregateEventStub::instance($identity);
        $eventStream = new AggregateEventArrayStream([$event->withAggregateVersion(new AggregateVersion(1))]);

        $eventStore = new AbstractEventStoreStub(null, true);
        $eventStore->store($stream, $eventStream, new AggregateVersion(0));
    }

    public function testStore(): void
    {
        $identity = $this->getMockBuilder(Identity::class)->disableOriginalConstructor()->getMock();
        $identity->expects(static::any())
            ->method('getValue')
            ->will(static::returnValue('aaa'));
        /** @var Identity $identity */
        $stream = GenericStoreStream::fromAggregateData(AbstractAggregateRootStub::class, $identity);

        $event = AbstractAggregateEventStub::instance($identity);
        $eventStream = new AggregateEventArrayStream([$event->withAggregateVersion(new AggregateVersion(1))]);

        $eventStore = new AbstractEventStoreStub();
        $eventStore->store($stream, $eventStream, new AggregateVersion(0));

        $loadedFromEvents = $eventStore->loadTo($stream, new AggregateVersion(1));

        static::assertCount(1, $loadedFromEvents);
    }

    public function testLoadFrom(): void
    {
        $identity = $this->getMockBuilder(Identity::class)->disableOriginalConstructor()->getMock();
        $identity->expects(static::any())
            ->method('getValue')
            ->will(static::returnValue('aaa'));
        /** @var Identity $identity */
        $stream = GenericStoreStream::fromAggregateData(AbstractAggregateRootStub::class, $identity);

        $event = AbstractAggregateEventStub::instance($identity);

        $eventStore = new AbstractEventStoreStub();
        $eventStore->store(
            $stream,
            new AggregateEventArrayStream([
                $event->withAggregateVersion(new AggregateVersion(1)),
                $event->withAggregateVersion(new AggregateVersion(2)),
                $event->withAggregateVersion(new AggregateVersion(3)),
            ]),
            new AggregateVersion(0)
        );
        $eventStore->store(
            $stream,
            new AggregateEventArrayStream([
                $event->withAggregateVersion(new AggregateVersion(4)),
                $event->withAggregateVersion(new AggregateVersion(5)),
            ]),
            new AggregateVersion(3)
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
        $eventStream = new AggregateEventArrayStream([
            $event->withAggregateVersion(new AggregateVersion(1)),
            $event->withAggregateVersion(new AggregateVersion(2)),
            $event->withAggregateVersion(new AggregateVersion(3)),
            $event->withAggregateVersion(new AggregateVersion(4)),
            $event->withAggregateVersion(new AggregateVersion(5)),
        ]);

        $eventStore = new AbstractEventStoreStub();
        $eventStore->store($stream, $eventStream, new AggregateVersion(0));

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
