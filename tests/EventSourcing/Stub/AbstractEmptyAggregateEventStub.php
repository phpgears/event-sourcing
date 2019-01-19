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

use Gears\Aggregate\AggregateIdentity;
use Gears\EventSourcing\Event\AbstractEmptyAggregateEvent;

/**
 * Abstract empty event stub class.
 */
class AbstractEmptyAggregateEventStub extends AbstractEmptyAggregateEvent
{
    /**
     * Instantiate event.
     *
     * @param AggregateIdentity $aggregateId
     *
     * @return self
     */
    public static function instance(AggregateIdentity $aggregateId): self
    {
        return self::occurred($aggregateId);
    }
}
