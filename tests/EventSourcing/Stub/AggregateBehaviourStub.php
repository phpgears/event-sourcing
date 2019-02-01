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

use Gears\EventSourcing\Aggregate\AggregateBehaviour;
use Gears\Identity\Identity;

/**
 * AggregateBehaviour trait stub class.
 */
class AggregateBehaviourStub
{
    use AggregateBehaviour;

    /**
     * AggregateBehaviourStub constructor.
     *
     * @param Identity $aggregateId
     * @param int      $aggregateVersion
     */
    public function __construct(Identity $aggregateId, int $aggregateVersion)
    {
        $this->identity = $aggregateId;
        $this->version = $aggregateVersion;
    }
}
