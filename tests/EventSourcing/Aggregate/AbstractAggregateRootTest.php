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

namespace Gears\EventSourcing\Tests\Aggregate;

use Gears\EventSourcing\Aggregate\AggregateVersion;
use Gears\EventSourcing\Aggregate\Exception\AggregateException;
use Gears\EventSourcing\Aggregate\Exception\AggregateVersionException;
use Gears\EventSourcing\Aggregate\Serializer\Exception\AggregateSerializationException;
use Gears\EventSourcing\Event\AggregateEvent;
use Gears\EventSourcing\Event\AggregateEventArrayStream;
use Gears\EventSourcing\Tests\Stub\AbstractAggregateEventStub;
use Gears\EventSourcing\Tests\Stub\AbstractAggregateRootStub;
use Gears\Identity\UuidIdentity;
use PHPUnit\Framework\TestCase;

/**
 * Abstract aggregate root test.
 */
class AbstractAggregateRootTest extends TestCase
{
    public function testInvalidEventApply(): void
    {
        $this->expectException(AggregateVersionException::class);
        $this->expectExceptionMessageRegExp(
            '/^Only new aggregate events can be recorded, event .+ with version "10" given$/'
        );

        $aggregateEvent = $this->getMockBuilder(AggregateEvent::class)->getMock();
        $aggregateEvent->expects(static::any())
            ->method('getAggregateVersion')
            ->will(static::returnValue(new AggregateVersion(10)));

        /* @var AggregateEvent $aggregateEvent */

        AbstractAggregateRootStub::instantiateWithEvent($aggregateEvent);
    }

    public function testNoApplyHandler(): void
    {
        $this->expectException(AggregateException::class);
        $this->expectExceptionMessageRegExp(
            '/^Aggregate event handling method "apply.+" for event ".+" does not exist$/'
        );

        $aggregateEvent = $this->getMockBuilder(AggregateEvent::class)->getMock();
        $aggregateEvent->expects(static::any())
            ->method('getAggregateVersion')
            ->will(static::returnValue(new AggregateVersion(0)));
        $aggregateEvent->expects(static::any())
            ->method('getEventType')
            ->will(static::returnValue(\get_class($aggregateEvent)));
        /* @var AggregateEvent $aggregateEvent */

        AbstractAggregateRootStub::instantiateWithEvent($aggregateEvent);
    }

    public function testRecordedAggregateEvents(): void
    {
        $aggregateEvent = AbstractAggregateEventStub::instance(
            UuidIdentity::fromString('3247cb6e-e9c7-4f3a-9c6c-0dec26a0353f')
        );

        $aggregateRoot = AbstractAggregateRootStub::instantiateWithEvent($aggregateEvent);

        static::assertCount(1, $aggregateRoot->getRecordedAggregateEvents());
        $aggregateRoot->clearRecordedAggregateEvents();
        static::assertCount(0, $aggregateRoot->getRecordedAggregateEvents());

        $aggregateRoot = AbstractAggregateRootStub::instantiateWithEvent($aggregateEvent);

        static::assertCount(1, $aggregateRoot->getRecordedAggregateEvents());
        $recordedAggregateEvents = $aggregateRoot->collectRecordedAggregateEvents();
        static::assertCount(0, $aggregateRoot->collectRecordedAggregateEvents());
        static::assertCount(1, $recordedAggregateEvents);

        static::assertEquals(
            [AbstractAggregateEventStub::withVersion($aggregateEvent, new AggregateVersion(1))],
            \iterator_to_array($recordedAggregateEvents)
        );
    }

    public function testReconstituteFromEmptyStream(): void
    {
        $this->expectException(AggregateException::class);
        $this->expectExceptionMessage('Aggregate cannot be reconstituted from empty event stream');

        $eventStream = new AggregateEventArrayStream([]);

        AbstractAggregateRootStub::reconstituteFromEventStream($eventStream);
    }

    public function testReconstituteFromInvalidEvent(): void
    {
        $this->expectException(AggregateVersionException::class);
        $this->expectExceptionMessageRegExp(
            '/^Aggregate event .+ cannot be replayed, event version is "10" and aggregate is "0"$/'
        );

        $aggregateEvent = AbstractAggregateEventStub::reconstitute(
            [],
            new \DateTimeImmutable('now'),
            [
                'aggregateId' => UuidIdentity::fromString('3247cb6e-e9c7-4f3a-9c6c-0dec26a0353f'),
                'aggregateVersion' => new AggregateVersion(10),
                'metadata' => [],
            ]
        );

        $eventStream = new AggregateEventArrayStream([$aggregateEvent]);

        AbstractAggregateRootStub::reconstituteFromEventStream($eventStream);
    }

    public function testReconstitute(): void
    {
        $aggregateEvent = AbstractAggregateEventStub::reconstitute(
            [],
            new \DateTimeImmutable('now'),
            [
                'aggregateId' => UuidIdentity::fromString('3247cb6e-e9c7-4f3a-9c6c-0dec26a0353f'),
                'aggregateVersion' => new AggregateVersion(1),
                'metadata' => ['userId' => '123456'],
            ]
        );

        $eventStream = new AggregateEventArrayStream([$aggregateEvent]);

        $aggregateRoot = AbstractAggregateRootStub::reconstituteFromEventStream($eventStream);

        static::assertEquals($aggregateEvent->getAggregateId(), $aggregateRoot->getIdentity());
        static::assertEquals($aggregateEvent->getAggregateVersion(), $aggregateRoot->getVersion());
        static::assertCount(0, $aggregateRoot->collectRecordedAggregateEvents());
    }

    public function testNoSerialization(): void
    {
        $this->expectException(AggregateSerializationException::class);
        $this->expectExceptionMessage('Aggregate root with recorded events cannot be serialized');

        $aggregateEvent = AbstractAggregateEventStub::instance(
            UuidIdentity::fromString('3247cb6e-e9c7-4f3a-9c6c-0dec26a0353f')
        );

        \serialize(AbstractAggregateRootStub::instantiateWithEvent($aggregateEvent));
    }

    public function testSerialization(): void
    {
        $identity = UuidIdentity::fromString('828b9a86-959c-47f8-af97-fc7dda3325ca');
        $aggregateRoot = AbstractAggregateRootStub::instantiateFromIdentity($identity);

        $serialized = \version_compare(\PHP_VERSION, '7.4.0') >= 0
            ? 'O:56:"Gears\EventSourcing\Tests\Stub\AbstractAggregateRootStub":3:{'
                . 's:14:"aggregateParam";s:5:"value";'
                . 's:8:"identity";O:27:"Gears\Identity\UuidIdentity":1:{'
                . 's:5:"value";s:36:"828b9a86-959c-47f8-af97-fc7dda3325ca";'
                . '}s:7:"version";O:46:"Gears\EventSourcing\Aggregate\AggregateVersion":1:{s:5:"value";i:0;}}'
            : 'C:56:"Gears\EventSourcing\Tests\Stub\AbstractAggregateRootStub":215:{a:3:{'
                . 's:14:"aggregateParam";s:5:"value";'
                . 's:8:"identity";C:27:"Gears\Identity\UuidIdentity":44:{s:36:"828b9a86-959c-47f8-af97-fc7dda3325ca";}'
                . 's:7:"version";C:46:"Gears\EventSourcing\Aggregate\AggregateVersion":4:{i:0;}}}';

        static::assertSame($serialized, \serialize($aggregateRoot));
        static::assertSame(
            $aggregateRoot->getIdentity()->getValue(),
            (\unserialize($serialized))->getIdentity()->getValue()
        );
    }
}
