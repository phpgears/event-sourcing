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
use Gears\EventSourcing\Aggregate\AggregateBehaviour;

/**
 * AggregateBehaviour trait stub class.
 */
class AggregateBehaviourStub
{
    use AggregateBehaviour;

    /**
     * AggregateBehaviourStub constructor.
     *
     * @param AggregateIdentity $aggregateId
     * @param int               $aggregateVersion
     */
    public function __construct(AggregateIdentity $aggregateId, int $aggregateVersion)
    {
        $this->identity = $aggregateId;
        $this->version = $aggregateVersion;
    }
}
