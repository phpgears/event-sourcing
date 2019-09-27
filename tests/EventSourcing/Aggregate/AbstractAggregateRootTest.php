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
}
