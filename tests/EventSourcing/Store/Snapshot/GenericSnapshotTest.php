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

namespace Gears\EventSourcing\Tests\Store\Snapshot;

use Gears\EventSourcing\Store\Snapshot\Exception\SnapshotStoreException;
use Gears\EventSourcing\Store\Snapshot\GenericSnapshot;
use Gears\EventSourcing\Tests\Stub\AbstractAggregateEventStub;
use Gears\EventSourcing\Tests\Stub\AbstractAggregateRootStub;
use Gears\Identity\Identity;
use PHPUnit\Framework\TestCase;

/**
 * Generic snapshot test.
 */
class GenericSnapshotTest extends TestCase
{
    public function testInvalidAggregateRoot(): void
    {
        $this->expectException(SnapshotStoreException::class);
        $this->expectExceptionMessage('Cannot create an snapshot of an Aggregate root with recorded events');

        $identity = $this->getMockBuilder(Identity::class)->disableOriginalConstructor()->getMock();
        $identity->expects(static::any())
            ->method('getValue')
            ->will(static::returnValue('aaa'));
        /** @var Identity $identity */
        $event = AbstractAggregateEventStub::instance($identity);

        GenericSnapshot::fromAggregateRoot(AbstractAggregateRootStub::instantiateWithEvent($event));
    }

    public function testFromAggregateRoot(): void
    {
        /** @var Identity $identity */
        $identity = $this->getMockBuilder(Identity::class)->disableOriginalConstructor()->getMock();
        $event = AbstractAggregateEventStub::instance($identity);
        $aggregateRoot = AbstractAggregateRootStub::instantiateWithEvent($event);
        $aggregateRoot->collectRecordedAggregateEvents();

        $snapshot = GenericSnapshot::fromAggregateRoot($aggregateRoot);

        static::assertEquals($aggregateRoot, $snapshot->getAggregateRoot());

        $storeStream = $snapshot->getStoreStream();
        static::assertEquals(AbstractAggregateRootStub::class, $storeStream->getAggregateRootClass());
        static::assertEquals($identity, $storeStream->getAggregateId());
    }
}
