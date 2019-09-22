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

namespace Gears\EventSourcing\Tests\Store;

use Gears\EventSourcing\Aggregate\Exception\AggregateException;
use Gears\EventSourcing\Store\GenericStoreStream;
use Gears\EventSourcing\Tests\Stub\AbstractAggregateEventStub;
use Gears\EventSourcing\Tests\Stub\AbstractAggregateRootStub;
use Gears\Identity\Identity;
use PHPUnit\Framework\TestCase;

/**
 * Generic store stream test.
 */
class GenericStoreStreamTest extends TestCase
{
    public function testFromUnknownAggregateData(): void
    {
        $this->expectException(AggregateException::class);
        $this->expectExceptionMessage('Aggregate root class "unknownClass" cannot be found');

        /** @var Identity $identity */
        $identity = $this->getMockBuilder(Identity::class)->disableOriginalConstructor()->getMock();

        GenericStoreStream::fromAggregateData('unknownClass', $identity);
    }

    public function testFromInvalidAggregateData(): void
    {
        $this->expectException(AggregateException::class);
        $this->expectExceptionMessageRegExp(
            '/^Aggregate root class must implement ".+\\\AggregateRoot", ".+" given$/'
        );

        /** @var Identity $identity */
        $identity = $this->getMockBuilder(Identity::class)->disableOriginalConstructor()->getMock();

        GenericStoreStream::fromAggregateData(GenericStoreStream::class, $identity);
    }

    public function testFromAggregateData(): void
    {
        /** @var Identity $identity */
        $identity = $this->getMockBuilder(Identity::class)->disableOriginalConstructor()->getMock();

        $stream = GenericStoreStream::fromAggregateData(AbstractAggregateRootStub::class, $identity);

        static::assertEquals(AbstractAggregateRootStub::class, $stream->getAggregateRootClass());
        static::assertEquals($identity, $stream->getAggregateId());
    }

    public function testFromAggregateRoot(): void
    {
        /** @var Identity $identity */
        $identity = $this->getMockBuilder(Identity::class)->disableOriginalConstructor()->getMock();
        $event = AbstractAggregateEventStub::instance($identity);
        $aggregateRoot = AbstractAggregateRootStub::instantiateWithEvent($event);

        $stream = GenericStoreStream::fromAggregateRoot($aggregateRoot);

        static::assertEquals(AbstractAggregateRootStub::class, $stream->getAggregateRootClass());
        static::assertEquals($identity, $stream->getAggregateId());
    }
}
