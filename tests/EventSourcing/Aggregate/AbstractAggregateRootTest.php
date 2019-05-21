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

use Gears\EventSourcing\Event\AggregateEvent;
use Gears\EventSourcing\Event\AggregateEventArrayCollection;
use Gears\EventSourcing\Tests\Stub\AbstractAggregateEventStub;
use Gears\EventSourcing\Tests\Stub\AbstractAggregateRootStub;
use Gears\Identity\UuidIdentity;
use PHPUnit\Framework\TestCase;

/**
 * Abstract aggregate root test.
 */
class AbstractAggregateRootTest extends TestCase
{
    /**
     * @expectedException  \Gears\EventSourcing\Aggregate\Exception\AggregateException
     * @expectedExceptionMessageRegExp /^Aggregate event handling method apply.+ for event .+ does not exist$/
     */
    public function testNoApplyHandler(): void
    {
        $aggregateEvent = $this->getMockBuilder(AggregateEvent::class)->getMock();
        $aggregateEvent->expects($this->any())
            ->method('withAggregateVersion')
            ->will($this->returnSelf());

        /* @var AggregateEvent $aggregateEvent */

        AbstractAggregateRootStub::instantiateWithEvent($aggregateEvent);
    }

    public function testRecordedAggregateEvents(): void
    {
        $aggregateEvent = AbstractAggregateEventStub::instance(
            UuidIdentity::fromString('3247cb6e-e9c7-4f3a-9c6c-0dec26a0353f'),
            []
        );

        $aggregateRoot = AbstractAggregateRootStub::instantiateWithEvent($aggregateEvent);

        $this->assertCount(1, $aggregateRoot->getRecordedAggregateEvents());
        $aggregateRoot->clearRecordedAggregateEvents();
        $this->assertCount(0, $aggregateRoot->getRecordedAggregateEvents());

        $aggregateRoot = AbstractAggregateRootStub::instantiateWithEvent($aggregateEvent);

        $this->assertCount(1, $aggregateRoot->getRecordedAggregateEvents());
        $recordedAggregateEvents = $aggregateRoot->collectRecordedAggregateEvents();
        $this->assertCount(0, $aggregateRoot->collectRecordedAggregateEvents());
        $this->assertCount(1, $recordedAggregateEvents);

        $this->assertEquals([$aggregateEvent], \iterator_to_array($recordedAggregateEvents));
    }

    public function testReconstitute(): void
    {
        $aggregateEvent = AbstractAggregateEventStub::reconstitute(
            [],
            [
                'aggregateId' => UuidIdentity::fromString('3247cb6e-e9c7-4f3a-9c6c-0dec26a0353f'),
                'aggregateVersion' => 10,
                'createdAt' => new \DateTimeImmutable('now'),
            ]
        );

        $collection = new AggregateEventArrayCollection([$aggregateEvent]);

        $aggregateRoot = AbstractAggregateRootStub::reconstituteFromAggregateEvents($collection);

        $this->assertEquals($aggregateEvent->getAggregateId(), $aggregateRoot->getIdentity());
        $this->assertEquals($aggregateEvent->getAggregateVersion(), $aggregateRoot->getVersion());
        $this->assertCount(0, $aggregateRoot->collectRecordedAggregateEvents());
    }
}
