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

namespace Gears\EventSourcing\Event;

use Gears\Event\EventBehaviour;
use Gears\Event\Exception\EventException;
use Gears\EventSourcing\Aggregate\AggregateBehaviour;
use Gears\EventSourcing\Aggregate\AggregateVersion;
use Gears\Identity\Identity;

use function DeepCopy\deep_copy;

/**
 * Aggregate event behaviour.
 */
trait AggregateEventBehaviour
{
    use EventBehaviour, AggregateBehaviour {
        AggregateBehaviour::getIdentity as private;
        AggregateBehaviour::getVersion as private;
    }

    /**
     * Get aggregate id.
     *
     * @return Identity
     */
    final public function getAggregateId(): Identity
    {
        return $this->identity;
    }

    /**
     * Get aggregate version.
     *
     * @return AggregateVersion
     */
    final public function getAggregateVersion(): AggregateVersion
    {
        return $this->version;
    }

    /**
     * Get event with new aggregate version.
     *
     * @param AggregateVersion $aggregateVersion
     *
     * @throws \Gears\Event\Exception\EventException
     *
     * @return mixed|self
     */
    final public function withAggregateVersion(AggregateVersion $aggregateVersion)
    {
        if (!$this->version->isEqualTo(new AggregateVersion(0))) {
            throw new EventException(\sprintf(
                'Only new events can get a new version, event "%s" already at version "%s"',
                \get_class($this),
                $this->version->getValue()
            ));
        }

        if ($aggregateVersion->isEqualTo(new AggregateVersion(0))) {
            throw new EventException(\sprintf(
                'Aggregate events can not get version 0 set, version "0" given to event "%s"',
                \get_class($this)
            ));
        }

        /* @var self $self */
        $self = deep_copy($this);
        $self->version = $aggregateVersion;

        return $self;
    }
}
