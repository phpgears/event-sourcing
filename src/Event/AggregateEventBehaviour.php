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
use Gears\EventSourcing\Aggregate\AggregateBehaviour;
use Gears\EventSourcing\Aggregate\AggregateVersion;
use Gears\Identity\Identity;

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
}
