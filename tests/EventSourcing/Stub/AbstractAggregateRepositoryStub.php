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

namespace Gears\EventSourcing\Tests\Stub;

use Gears\EventSourcing\Repository\AbstractAggregateRepository;
use Gears\EventSourcing\Store\GenericStoreStream;
use Gears\EventSourcing\Store\StoreStream;
use Gears\Identity\Identity;

/**
 * Abstract aggregate repository stub class.
 */
class AbstractAggregateRepositoryStub extends AbstractAggregateRepository
{
    /**
     * {@inheritdoc}
     */
    protected function getStoreStream(Identity $aggregateId): StoreStream
    {
        return GenericStoreStream::fromAggregateData(AbstractAggregateRootStub::class, $aggregateId);
    }
}
