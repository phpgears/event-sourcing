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

use Gears\EventSourcing\Aggregate\AbstractAggregateRoot;
use Gears\EventSourcing\Event\AggregateEvent;

/**
 * Abstract aggregate root stub class.
 */
class AbstractAggregateRootStub extends AbstractAggregateRoot
{
    /**
     * @param AggregateEvent $event
     *
     * @return self
     */
    public static function instantiateWithEvent(AggregateEvent $event): self
    {
        $aggregateRoot = new self();

        $aggregateRoot->recordEvent($event);

        return $aggregateRoot;
    }

    /**
     * @param AbstractAggregateEventStub $event
     */
    protected function applyAbstractAggregateEventStub(AbstractAggregateEventStub $event): void
    {
        $this->setIdentity($event->getAggregateId());
    }
}
