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

use Gears\EventSourcing\Store\GenericStoreStream;
use Gears\EventSourcing\Store\Snapshot\Exception\SnapshotStoreException;
use Gears\EventSourcing\Store\Snapshot\GenericSnapshot;
use Gears\EventSourcing\Tests\Stub\AbstractAggregateEventStub;
use Gears\EventSourcing\Tests\Stub\AbstractAggregateRootStub;
use Gears\EventSourcing\Tests\Stub\AbstractSnapshotStoreStub;
use Gears\Identity\Identity;
use PHPUnit\Framework\TestCase;

/**
 * Abstract snapshot store test.
 */
class AbstractSnapshotStoreTest extends TestCase
{
    public function testInvalidAggregateRootStore(): void
    {
        $this->expectException(SnapshotStoreException::class);
        $this->expectExceptionMessage('Aggregate root cannot have recorded events in order to be snapshoted');

        $identity = $this->getMockBuilder(Identity::class)->disableOriginalConstructor()->getMock();
        $identity->expects(static::once())
            ->method('getValue')
            ->will(static::returnValue('aaa'));
        /** @var Identity $identity */
        $event = AbstractAggregateEventStub::instance($identity);
        $aggregateRoot = AbstractAggregateRootStub::instantiateWithEvent($event);

        (new AbstractSnapshotStoreStub())->store(GenericSnapshot::fromAggregateRoot($aggregateRoot));
    }

    public function testInvalidAggregateRootLoad(): void
    {
        $this->expectException(SnapshotStoreException::class);
        $this->expectExceptionMessage('Aggregate root coming from snapshot cannot have recorded events');

        $identity = $this->getMockBuilder(Identity::class)->disableOriginalConstructor()->getMock();
        $identity->expects(static::any())
            ->method('getValue')
            ->will(static::returnValue('aaa'));
        /** @var Identity $identity */
        $event = AbstractAggregateEventStub::instance($identity);
        $aggregateRoot = AbstractAggregateRootStub::instantiateWithEvent($event);

        $stream = [
            'aaa' => \serialize($aggregateRoot),
        ];

        (new AbstractSnapshotStoreStub($stream))->load(GenericStoreStream::fromAggregateRoot($aggregateRoot));
    }
}
