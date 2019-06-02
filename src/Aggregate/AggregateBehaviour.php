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

namespace Gears\EventSourcing\Aggregate;

use Gears\Identity\Identity;

/**
 * Aggregate behaviour trait.
 */
trait AggregateBehaviour
{
    /**
     * @var Identity
     */
    private $identity;

    /**
     * @var AggregateVersion
     */
    private $version;

    /**
     * Get aggregate identity.
     *
     * @return Identity
     */
    final public function getIdentity(): Identity
    {
        return $this->identity;
    }

    /**
     * Get version.
     *
     * @return AggregateVersion
     */
    final public function getVersion(): AggregateVersion
    {
        return $this->version;
    }
}
