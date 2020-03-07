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
use Gears\EventSourcing\Event\Exception\AggregateEventException;
use Gears\EventSourcing\Tests\Stub\AbstractAggregateEventStub;
use Gears\Identity\UuidIdentity;
use PHPUnit\Framework\TestCase;

/**
 * Abstract aggregate event test.
 */
class AbstractAggregateEventTest extends TestCase
{
    public function testCommandType(): void
    {
        $stub = AbstractAggregateEventStub::instance(
            UuidIdentity::fromString('3247cb6e-e9c7-4f3a-9c6c-0dec26a0353f')
        );

        static::assertEquals(AbstractAggregateEventStub::class, $stub->getEventType());
    }

    public function testCreation(): void
    {
        $aggregateEvent = AbstractAggregateEventStub::instance(
            UuidIdentity::fromString('3247cb6e-e9c7-4f3a-9c6c-0dec26a0353f')
        );

        static::assertLessThanOrEqual(new \DateTimeImmutable('now'), $aggregateEvent->getCreatedAt());
    }

    public function testReconstitutionInvalidVersion(): void
    {
        $this->expectException(AggregateEventException::class);
        $this->expectExceptionMessage('Invalid aggregate version, "0" given');

        AbstractAggregateEventStub::reconstitute(
            [],
            new \DateTimeImmutable('now'),
            [
                'aggregateId' => UuidIdentity::fromString('3247cb6e-e9c7-4f3a-9c6c-0dec26a0353f'),
                'aggregateVersion' => new AggregateVersion(0),
                'metadata' => ['userId' => '123456'],
            ]
        );
    }

    public function testReconstitution(): void
    {
        $identity = UuidIdentity::fromString('3247cb6e-e9c7-4f3a-9c6c-0dec26a0353f');
        $metadata = ['userId' => '123456'];
        $now = new \DateTimeImmutable('now');

        $aggregateEvent = AbstractAggregateEventStub::reconstitute(
            [],
            $now,
            [
                'aggregateId' => $identity,
                'aggregateVersion' => new AggregateVersion(10),
                'metadata' => $metadata,
            ]
        );

        static::assertSame($identity, $aggregateEvent->getAggregateId());
        static::assertSame(10, $aggregateEvent->getAggregateVersion()->getValue());
        static::assertEquals($metadata, $aggregateEvent->getMetadata());
        static::assertEquals($now, $aggregateEvent->getCreatedAt());
    }

    public function testMetadata(): void
    {
        $aggregateEvent = AbstractAggregateEventStub::instance(
            UuidIdentity::fromString('3247cb6e-e9c7-4f3a-9c6c-0dec26a0353f')
        );

        static::assertEmpty($aggregateEvent->getMetadata());

        $newAggregateEvent = $aggregateEvent->withAddedMetadata(['userId' => '123456']);

        static::assertEmpty($aggregateEvent->getMetadata());
        static::assertEquals(['userId' => '123456'], $newAggregateEvent->getMetadata());
    }

    public function testNewVersion(): void
    {
        $aggregateEvent = AbstractAggregateEventStub::instance(
            UuidIdentity::fromString('3247cb6e-e9c7-4f3a-9c6c-0dec26a0353f')
        );

        $newAggregateEvent = AbstractAggregateEventStub::withVersion($aggregateEvent, new AggregateVersion(10));

        static::assertNotSame($aggregateEvent, $newAggregateEvent);
        static::assertEquals(10, $newAggregateEvent->getAggregateVersion()->getValue());
    }

    public function testNoSerialization(): void
    {
        $this->expectException(AggregateEventException::class);
        $this->expectExceptionMessage(
            'Aggregate event "Gears\EventSourcing\Tests\Stub\AbstractAggregateEventStub" cannot be serialized'
        );

        $aggregateEvent = AbstractAggregateEventStub::instance(
            UuidIdentity::fromString('3247cb6e-e9c7-4f3a-9c6c-0dec26a0353f')
        );
        \serialize($aggregateEvent);
    }

    public function testNoDeserialization(): void
    {
        $this->expectException(AggregateEventException::class);
        $this->expectExceptionMessage(
            'Aggregate event "Gears\EventSourcing\Tests\Stub\AbstractAggregateEventStub" cannot be unserialized'
        );

        \unserialize('O:57:"Gears\EventSourcing\Tests\Stub\AbstractAggregateEventStub":0:{}');
    }
}
