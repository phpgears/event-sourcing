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

use Gears\Aggregate\UuidAggregateIdentity;
use Gears\EventSourcing\Tests\Stub\AbstractAggregateEventStub;
use Gears\EventSourcing\Tests\Stub\AbstractEmptyAggregateEventStub;
use PHPUnit\Framework\TestCase;

/**
 * Abstract aggregate event test.
 */
class AbstractAggregateEventTest extends TestCase
{
    public function testCreation(): void
    {
        $aggregateEvent = AbstractAggregateEventStub::instance(
            UuidAggregateIdentity::fromString('3247cb6e-e9c7-4f3a-9c6c-0dec26a0353f'),
            []
        );

        $this->assertLessThanOrEqual(new \DateTimeImmutable('now'), $aggregateEvent->getCreatedAt());
    }

    public function testReconstitution(): void
    {
        $identity = UuidAggregateIdentity::fromString('3247cb6e-e9c7-4f3a-9c6c-0dec26a0353f');
        $now = new \DateTimeImmutable('now');

        $aggregateEvent = AbstractEmptyAggregateEventStub::reconstitute(
            [],
            [
                'aggregateId' => $identity,
                'aggregateVersion' => 10,
                'createdAt' => $now,
            ]
        );

        $this->assertSame($identity, $aggregateEvent->getAggregateId());
        $this->assertSame(10, $aggregateEvent->getAggregateVersion());
        $this->assertEquals($now, $aggregateEvent->getCreatedAt());
    }

    public function testNewWith(): void
    {
        $aggregateEvent = AbstractEmptyAggregateEventStub::instance(
            UuidAggregateIdentity::fromString('3247cb6e-e9c7-4f3a-9c6c-0dec26a0353f')
        );

        $newAggregateEvent = $aggregateEvent->withAggregateVersion(10);

        $this->assertNotSame($aggregateEvent, $newAggregateEvent);
        $this->assertEquals(10, $newAggregateEvent->getAggregateVersion());
    }
}
