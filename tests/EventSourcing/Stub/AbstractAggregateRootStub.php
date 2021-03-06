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
use Gears\Identity\Identity;

/**
 * Abstract aggregate root stub class.
 */
class AbstractAggregateRootStub extends AbstractAggregateRoot
{
    protected $aggregateParam = 'value';

    /**
     * @param AggregateEvent $event
     *
     * @return self
     */
    public static function instantiateWithEvent(AggregateEvent $event): self
    {
        $aggregateRoot = new self();

        $aggregateRoot->recordAggregateEvent($event);

        return $aggregateRoot;
    }

    /**
     * @param Identity $identity
     *
     * @return self
     */
    public static function instantiateFromIdentity(Identity $identity): self
    {
        $aggregateRoot = new self();

        $aggregateRoot->setIdentity($identity);

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
