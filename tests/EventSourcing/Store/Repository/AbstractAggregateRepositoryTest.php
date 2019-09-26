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

namespace Gears\EventSourcing\Tests\Store\Repository;

use Gears\Event\EventArrayCollection;
use Gears\EventSourcing\Aggregate\AggregateRoot;
use Gears\EventSourcing\Aggregate\AggregateVersion;
use Gears\EventSourcing\Event\AggregateEventArrayStream;
use Gears\EventSourcing\Store\Event\EventStore;
use Gears\EventSourcing\Store\Repository\Exception\AggregateRepositoryException;
use Gears\EventSourcing\Store\Repository\Exception\AggregateRootNotFoundException;
use Gears\EventSourcing\Store\Snapshot\GenericSnapshot;
use Gears\EventSourcing\Store\Snapshot\SnapshotStore;
use Gears\EventSourcing\Tests\Stub\AbstractAggregateEventStub;
use Gears\EventSourcing\Tests\Stub\AbstractAggregateRepositoryStub;
use Gears\EventSourcing\Tests\Stub\AbstractAggregateRootStub;
use Gears\Identity\Identity;
use PHPUnit\Framework\TestCase;

/**
 * Abstract aggregate repository test.
 */
class AbstractAggregateRepositoryTest extends TestCase
{
    public function testNoAggregateRoot(): void
    {
        $this->expectException(AggregateRootNotFoundException::class);
        $this->expectExceptionMessage('Aggregate root from identity "aaa" not found');

        $identity = $this->getMockBuilder(Identity::class)->disableOriginalConstructor()->getMock();
        $identity->expects(static::any())
            ->method('getValue')
            ->will(static::returnValue('aaa'));
        /** @var Identity $identity */
        $eventStore = $this->getMockBuilder(EventStore::class)->disableOriginalConstructor()->getMock();
        $eventStore->expects(static::once())
            ->method('loadFrom')
            ->will(static::returnValue(new AggregateEventArrayStream([])));
        /** @var EventStore $eventStore */
        $repository = new AbstractAggregateRepositoryStub($eventStore);

        $repository->getAggregateRoot($identity);
    }

    public function testReconstitutedAggregateRoot(): void
    {
        $identity = $this->getMockBuilder(Identity::class)->disableOriginalConstructor()->getMock();
        $identity->expects(static::any())
            ->method('getValue')
            ->will(static::returnValue('aaa'));
        /** @var Identity $identity */
        $event = AbstractAggregateEventStub::instance($identity);
        $eventStream = new AggregateEventArrayStream([
            AbstractAggregateEventStub::withVersion($event, new AggregateVersion(1)),
            AbstractAggregateEventStub::withVersion($event, new AggregateVersion(2)),
        ]);

        $eventStore = $this->getMockBuilder(EventStore::class)->disableOriginalConstructor()->getMock();
        $eventStore->expects(static::once())
            ->method('loadFrom')
            ->will(static::returnValue($eventStream));
        /** @var EventStore $eventStore */
        $repository = new AbstractAggregateRepositoryStub($eventStore);

        $aggregateRoot = $repository->getAggregateRoot($identity);

        static::assertEquals($identity, $aggregateRoot->getIdentity());
        static::assertEquals(2, $aggregateRoot->getVersion()->getValue());
    }

    public function testInvalidAggregateRootFromSnapshot(): void
    {
        $this->expectException(AggregateRepositoryException::class);
        $this->expectExceptionMessageRegExp(
            '/^Aggregate root should be a ".+\\\AbstractAggregateRootStub", ".+" given$/'
        );

        $identity = $this->getMockBuilder(Identity::class)->disableOriginalConstructor()->getMock();
        $identity->expects(static::any())
            ->method('getValue')
            ->will(static::returnValue('aaa'));
        /** @var Identity $identity */
        $eventStore = $this->getMockBuilder(EventStore::class)->disableOriginalConstructor()->getMock();
        /** @var EventStore $eventStore */
        $aggregateRoot = $this->getMockBuilder(AggregateRoot::class)->disableOriginalConstructor()->getMock();
        $aggregateRoot->expects(static::once())
            ->method('getIdentity')
            ->will(static::returnValue($identity));
        $aggregateRoot->expects(static::once())
            ->method('getRecordedAggregateEvents')
            ->will(static::returnValue(new AggregateEventArrayStream([])));
        $aggregateRoot->expects(static::once())
            ->method('getRecordedEvents')
            ->will(static::returnValue(new EventArrayCollection([])));
        $snapshot = GenericSnapshot::fromAggregateRoot($aggregateRoot);

        $snapshotStore = $this->getMockBuilder(SnapshotStore::class)->disableOriginalConstructor()->getMock();
        $snapshotStore->expects(static::once())
            ->method('load')
            ->will(static::returnValue($snapshot));
        /** @var SnapshotStore $snapshotStore */
        $repository = new AbstractAggregateRepositoryStub($eventStore, $snapshotStore);

        $repository->getAggregateRoot($identity);
    }

    public function testAggregateRootFromSnapshot(): void
    {
        $identity = $this->getMockBuilder(Identity::class)->disableOriginalConstructor()->getMock();
        $identity->expects(static::any())
            ->method('getValue')
            ->will(static::returnValue('aaa'));
        /** @var Identity $identity */
        $event = AbstractAggregateEventStub::instance($identity);
        $eventStream = new AggregateEventArrayStream([
            AbstractAggregateEventStub::withVersion($event, new AggregateVersion(2)),
            AbstractAggregateEventStub::withVersion($event, new AggregateVersion(3)),
        ]);

        $eventStore = $this->getMockBuilder(EventStore::class)->disableOriginalConstructor()->getMock();
        $eventStore->expects(static::once())
            ->method('loadFrom')
            ->will(static::returnValue($eventStream));
        /** @var EventStore $eventStore */
        $aggregateRoot = AbstractAggregateRootStub::instantiateWithEvent($event);
        $aggregateRoot->collectRecordedAggregateEvents();
        $snapshot = GenericSnapshot::fromAggregateRoot($aggregateRoot);

        $snapshotStore = $this->getMockBuilder(SnapshotStore::class)->disableOriginalConstructor()->getMock();
        $snapshotStore->expects(static::once())
            ->method('load')
            ->will(static::returnValue($snapshot));
        /** @var SnapshotStore $snapshotStore */
        $repository = new AbstractAggregateRepositoryStub($eventStore, $snapshotStore);

        $loadedAggregateRoot = $repository->getAggregateRoot($identity);

        static::assertEquals($identity, $loadedAggregateRoot->getIdentity());
        static::assertEquals(3, $loadedAggregateRoot->getVersion()->getValue());
    }

    public function testSaveInvalidAggregateRoot(): void
    {
        $this->expectException(AggregateRepositoryException::class);
        $this->expectExceptionMessageRegExp(
            '/^Aggregate root should be a ".+\\\AbstractAggregateRootStub", ".+" given$/'
        );

        $identity = $this->getMockBuilder(Identity::class)->disableOriginalConstructor()->getMock();
        $identity->expects(static::any())
            ->method('getValue')
            ->will(static::returnValue('aaa'));
        /** @var Identity $identity */
        $aggregateRoot = $this->getMockBuilder(AggregateRoot::class)->disableOriginalConstructor()->getMock();
        $aggregateRoot->expects(static::once())
            ->method('getIdentity')
            ->will(static::returnValue($identity));
        /** @var AggregateRoot $aggregateRoot */

        /** @var EventStore $eventStore */
        $eventStore = $this->getMockBuilder(EventStore::class)->disableOriginalConstructor()->getMock();

        (new AbstractAggregateRepositoryStub($eventStore))->saveAggregateRoot($aggregateRoot);
    }

    public function testSaveEmptyAggregateRoot(): void
    {
        $identity = $this->getMockBuilder(Identity::class)->disableOriginalConstructor()->getMock();
        $identity->expects(static::any())
            ->method('getValue')
            ->will(static::returnValue('aaa'));
        /** @var Identity $identity */
        $aggregateRoot = AbstractAggregateRootStub::instantiateFromIdentity($identity);

        $eventStore = $this->getMockBuilder(EventStore::class)->disableOriginalConstructor()->getMock();
        $eventStore->expects($this->never())
            ->method('store');
        /* @var EventStore $eventStore */

        (new AbstractAggregateRepositoryStub($eventStore))->saveAggregateRoot($aggregateRoot);
    }

    public function testSaveAggregateRoot(): void
    {
        $identity = $this->getMockBuilder(Identity::class)->disableOriginalConstructor()->getMock();
        $identity->expects(static::any())
            ->method('getValue')
            ->will(static::returnValue('aaa'));
        /** @var Identity $identity */
        $event = AbstractAggregateEventStub::instance($identity);
        $aggregateRoot = AbstractAggregateRootStub::instantiateWithEvent($event);

        $eventStore = $this->getMockBuilder(EventStore::class)->disableOriginalConstructor()->getMock();
        $eventStore->expects(static::once())
            ->method('store');
        /* @var EventStore $eventStore */

        (new AbstractAggregateRepositoryStub($eventStore))->saveAggregateRoot($aggregateRoot);
    }
}
