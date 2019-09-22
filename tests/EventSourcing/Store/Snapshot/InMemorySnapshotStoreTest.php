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

use Gears\EventSourcing\Aggregate\AggregateVersion;
use Gears\EventSourcing\Aggregate\Serializer\NativeAggregateSerializer;
use Gears\EventSourcing\Event\AggregateEventArrayStream;
use Gears\EventSourcing\Store\GenericStoreStream;
use Gears\EventSourcing\Store\Snapshot\GenericSnapshot;
use Gears\EventSourcing\Store\Snapshot\InMemorySnapshotStore;
use Gears\EventSourcing\Tests\Stub\AbstractAggregateEventStub;
use Gears\EventSourcing\Tests\Stub\AbstractAggregateRootStub;
use Gears\Identity\Identity;
use Gears\Identity\UuidIdentity;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * In memory snapshot store test.
 */
class InMemorySnapshotStoreTest extends TestCase
{
    public function testLoadNoSnapshot(): void
    {
        /** @var Identity $identity */
        $identity = $this->getMockBuilder(Identity::class)->disableOriginalConstructor()->getMock();
        $stream = GenericStoreStream::fromAggregateData(AbstractAggregateRootStub::class, $identity);

        static::assertNull((new InMemorySnapshotStore(new NativeAggregateSerializer()))->load($stream));
    }

    public function testStore(): void
    {
        $identity = UuidIdentity::fromString(Uuid::uuid4()->toString());
        $event = AbstractAggregateEventStub::instance($identity)->withAggregateVersion(new AggregateVersion(1));
        $aggregateRoot = AbstractAggregateRootStub::reconstituteFromEventStream(
            new AggregateEventArrayStream([$event])
        );

        $snapshot = GenericSnapshot::fromAggregateRoot($aggregateRoot);

        $snapshotStore = new InMemorySnapshotStore(new NativeAggregateSerializer());
        $snapshotStore->store($snapshot);

        $loadedSnapshot = $snapshotStore->load(GenericStoreStream::fromAggregateRoot($aggregateRoot));

        static::assertNotNull($loadedSnapshot);
        static::assertEquals($aggregateRoot->getVersion(), $loadedSnapshot->getAggregateRoot()->getVersion());
        static::assertEquals($aggregateRoot, $loadedSnapshot->getAggregateRoot());

        $storeStream = $loadedSnapshot->getStoreStream();
        static::assertEquals(\get_class($aggregateRoot), $storeStream->getAggregateRootClass());
        static::assertEquals($identity, $storeStream->getAggregateId());
    }
}
