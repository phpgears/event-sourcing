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

use Gears\EventSourcing\Aggregate\AggregateVersion;
use Gears\EventSourcing\Tests\Stub\AbstractEmptyAggregateEventStub;
use Gears\Identity\UuidIdentity;
use PHPUnit\Framework\TestCase;

/**
 * Abstract empty aggregate event test.
 */
class AbstractEmptyAggregateEventTest extends TestCase
{
    public function testCreation(): void
    {
        $aggregateEvent = AbstractEmptyAggregateEventStub::instance(
            UuidIdentity::fromString('3247cb6e-e9c7-4f3a-9c6c-0dec26a0353f')
        );

        $this->assertLessThanOrEqual(new \DateTimeImmutable('now'), $aggregateEvent->getCreatedAt());
    }

    public function testReconstitution(): void
    {
        $identity = UuidIdentity::fromString('3247cb6e-e9c7-4f3a-9c6c-0dec26a0353f');
        $now = new \DateTimeImmutable('now');

        $aggregateEvent = AbstractEmptyAggregateEventStub::reconstitute(
            [],
            [
                'aggregateId' => $identity,
                'aggregateVersion' => new AggregateVersion(10),
                'createdAt' => $now,
            ]
        );

        $this->assertSame($identity, $aggregateEvent->getAggregateId());
        $this->assertSame(10, $aggregateEvent->getAggregateVersion()->getValue());
        $this->assertEquals($now, $aggregateEvent->getCreatedAt());
    }

    /**
     * @expectedException  \Gears\Event\Exception\EventException
     * @expectedExceptionMessageRegExp /^Only new events can get a new version, event .+ already at version 10$/
     */
    public function testNoNewVersion(): void
    {
        $aggregateEvent = AbstractEmptyAggregateEventStub::instance(
            UuidIdentity::fromString('3247cb6e-e9c7-4f3a-9c6c-0dec26a0353f')
        );

        $newAggregateEvent = $aggregateEvent->withAggregateVersion(new AggregateVersion(10));

        $this->assertEquals(10, $newAggregateEvent->getAggregateVersion()->getValue());

        $newAggregateEvent->withAggregateVersion(new AggregateVersion(20));
    }

    /**
     * @expectedException  \Gears\Event\Exception\EventException
     * @expectedExceptionMessageRegExp /^Aggregate events can not get version 0 set, version 0 given to event .+$/
     */
    public function testNoNewVersionZero(): void
    {
        $aggregateEvent = AbstractEmptyAggregateEventStub::instance(
            UuidIdentity::fromString('3247cb6e-e9c7-4f3a-9c6c-0dec26a0353f')
        );

        $aggregateEvent->withAggregateVersion(new AggregateVersion(0));
    }

    public function testNewVersion(): void
    {
        $aggregateEvent = AbstractEmptyAggregateEventStub::instance(
            UuidIdentity::fromString('3247cb6e-e9c7-4f3a-9c6c-0dec26a0353f')
        );

        $newAggregateEvent = $aggregateEvent->withAggregateVersion(new AggregateVersion(10));

        $this->assertNotSame($aggregateEvent, $newAggregateEvent);
        $this->assertEquals(10, $newAggregateEvent->getAggregateVersion()->getValue());
    }
}
