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

use Gears\EventSourcing\Aggregate\AggregateRoot;
use Gears\EventSourcing\Store\GenericStoreStream;
use Gears\EventSourcing\Tests\Stub\AbstractAggregateRootStub;
use Gears\EventSourcing\Tests\Stub\AbstractSnapshotStoreStub;
use Gears\EventSourcing\Tests\Stub\SnapshotStub;
use Gears\Identity\Identity;
use Gears\Identity\UuidIdentity;
use PHPUnit\Framework\TestCase;

/**
 * Abstract snapshot store test.
 */
class AbstractSnapshotStoreTest extends TestCase
{
    public function testStore(): void
    {
        $identity = $this->getMockBuilder(Identity::class)->disableOriginalConstructor()->getMock();
        $identity->expects(static::once())
            ->method('getValue')
            ->will(static::returnValue('aaa'));
        $aggregateRoot = AbstractAggregateRootStub::instantiateFromIdentity($identity);

        $snapshotStore = new AbstractSnapshotStoreStub();
        $snapshotStore->store(new SnapshotStub($aggregateRoot));

        static::assertArrayHasKey('aaa', $snapshotStore->getStream());
        static::assertInstanceOf(
            AggregateRoot::class,
            \unserialize(\stripslashes($snapshotStore->getStream()['aaa']))
        );
    }

    public function testInvalidAggregateRootLoad(): void
    {
        $aggregateRoot = AbstractAggregateRootStub::instantiateFromIdentity(
            UuidIdentity::fromString('3247cb6e-e9c7-4f3a-9c6c-0dec26a0353f')
        );

        $stream = [
            '3247cb6e-e9c7-4f3a-9c6c-0dec26a0353f' => \addslashes(\serialize($aggregateRoot)),
        ];

        $snapshotStore = new AbstractSnapshotStoreStub($stream);
        $returnAggregateRoot = $snapshotStore->load(GenericStoreStream::fromAggregateRoot($aggregateRoot));

        static::assertInstanceOf(AggregateRoot::class, $returnAggregateRoot->getAggregateRoot());
    }
}
