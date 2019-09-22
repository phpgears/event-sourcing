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

use Gears\EventSourcing\Store\Event\EventStore;
use Gears\EventSourcing\Store\GenericStoreStream;
use Gears\EventSourcing\Store\Repository\GenericAggregateRepository;
use Gears\EventSourcing\Tests\Stub\AbstractAggregateEventStub;
use Gears\EventSourcing\Tests\Stub\AbstractAggregateRootStub;
use Gears\Identity\Identity;
use PHPUnit\Framework\TestCase;

/**
 * Generic aggregate repository test.
 */
class GenericAggregateRepositoryTest extends TestCase
{
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
            ->method('store')
            ->will(static::returnCallback(function (GenericStoreStream $stream) use ($identity): void {
                static::assertEquals(AbstractAggregateRootStub::class, $stream->getAggregateRootClass());
                static::assertEquals($identity, $stream->getAggregateId());
            }));
        /* @var EventStore $eventStore */

        (new GenericAggregateRepository(AbstractAggregateRootStub::class, $eventStore))
            ->saveAggregateRoot($aggregateRoot);
    }
}
