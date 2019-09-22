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

namespace Gears\EventSourcing\Tests\Aggregate\Serializer;

use Gears\EventSourcing\Aggregate\Serializer\Exception\AggregateSerializationException;
use Gears\EventSourcing\Aggregate\Serializer\NativeAggregateSerializer;
use Gears\EventSourcing\Tests\Stub\AbstractAggregateRootStub;
use Gears\Identity\UuidIdentity;
use PHPUnit\Framework\TestCase;

/**
 * PHP native aggregate root serializer test.
 */
class NativeAggregateSerializerTest extends TestCase
{
    public function testSerialize(): void
    {
        $identity = UuidIdentity::fromString('828b9a86-959c-47f8-af97-fc7dda3325ca');
        $aggregateRoot = AbstractAggregateRootStub::instantiateFromIdentity($identity);

        $serialized = (new NativeAggregateSerializer())->serialize($aggregateRoot);

        if (\method_exists($this, 'assertStringContainsString')) {
            static::assertStringContainsString('{s:36:"828b9a86-959c-47f8-af97-fc7dda3325ca";}', $serialized);
        } else {
            static::assertContains('{s:36:"828b9a86-959c-47f8-af97-fc7dda3325ca";}', $serialized);
        }
    }

    public function testDeserialize(): void
    {
        $identity = UuidIdentity::fromString('828b9a86-959c-47f8-af97-fc7dda3325ca');
        $aggregateRoot = AbstractAggregateRootStub::instantiateFromIdentity($identity);

        $deserialized = (new NativeAggregateSerializer())->fromSerialized(\serialize($aggregateRoot));

        static::assertEquals($aggregateRoot, $deserialized);
    }

    public function testInvalidDeserialization(): void
    {
        $this->expectException(AggregateSerializationException::class);
        $this->expectExceptionMessage('Invalid unserialized aggregate root');

        (new NativeAggregateSerializer())->fromSerialized(\serialize(new \stdClass()));
    }
}
