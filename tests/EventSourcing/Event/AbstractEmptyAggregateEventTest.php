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

use Gears\Event\Exception\EventException;
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

        static::assertLessThanOrEqual(new \DateTimeImmutable('now'), $aggregateEvent->getCreatedAt());
    }

    public function testReconstitution(): void
    {
        $identity = UuidIdentity::fromString('3247cb6e-e9c7-4f3a-9c6c-0dec26a0353f');
        $metadata = ['userId' => '123456'];
        $now = new \DateTimeImmutable('now');

        $aggregateEvent = AbstractEmptyAggregateEventStub::reconstitute(
            [],
            [
                'aggregateId' => $identity,
                'aggregateVersion' => new AggregateVersion(10),
                'metadata' => $metadata,
                'createdAt' => $now,
            ]
        );

        static::assertSame($identity, $aggregateEvent->getAggregateId());
        static::assertSame(10, $aggregateEvent->getAggregateVersion()->getValue());
        static::assertEquals($metadata, $aggregateEvent->getMetadata());
        static::assertEquals($now, $aggregateEvent->getCreatedAt());
    }

    public function testMetadata(): void
    {
        $aggregateEvent = AbstractEmptyAggregateEventStub::instance(
            UuidIdentity::fromString('3247cb6e-e9c7-4f3a-9c6c-0dec26a0353f')
        );

        static::assertEmpty($aggregateEvent->getMetadata());

        $newAggregateEvent = $aggregateEvent->withMetadata(['userId' => '123456']);

        static::assertEmpty($aggregateEvent->getMetadata());
        static::assertEquals(['userId' => '123456'], $newAggregateEvent->getMetadata());
    }

    public function testNoNewVersion(): void
    {
        $this->expectException(EventException::class);
        $this->expectExceptionMessageRegExp(
            '/^Only new events can get a new version, event ".+" already at version "10"$/'
        );

        $aggregateEvent = AbstractEmptyAggregateEventStub::instance(
            UuidIdentity::fromString('3247cb6e-e9c7-4f3a-9c6c-0dec26a0353f')
        );

        $newAggregateEvent = $aggregateEvent->withAggregateVersion(new AggregateVersion(10));

        static::assertEquals(10, $newAggregateEvent->getAggregateVersion()->getValue());

        $newAggregateEvent->withAggregateVersion(new AggregateVersion(20));
    }

    public function testNoNewVersionZero(): void
    {
        $this->expectException(EventException::class);
        $this->expectExceptionMessageRegExp(
            '/^Aggregate events can not get version 0 set, version "0" given to event ".+"$/'
        );

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

        static::assertNotSame($aggregateEvent, $newAggregateEvent);
        static::assertEquals(10, $newAggregateEvent->getAggregateVersion()->getValue());
    }
}
