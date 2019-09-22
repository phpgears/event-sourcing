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
    public function testFromAggregateRoot(): void
    {
        /** @var Identity $identity */
        $identity = $this->getMockBuilder(Identity::class)->disableOriginalConstructor()->getMock();
        $event = AbstractAggregateEventStub::instance($identity);
        $aggregateRoot = AbstractAggregateRootStub::instantiateWithEvent($event);

        $snapshot = GenericSnapshot::fromAggregateRoot($aggregateRoot);

        static::assertEquals($aggregateRoot, $snapshot->getAggregateRoot());

        $storeStream = $snapshot->getStoreStream();
        static::assertEquals(AbstractAggregateRootStub::class, $storeStream->getAggregateRootClass());
        static::assertEquals($identity, $storeStream->getAggregateId());
    }
}
