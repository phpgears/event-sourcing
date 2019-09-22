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

namespace Gears\EventSourcing\Store;

use Gears\Identity\Identity;

/**
 * Interface StoreStream.
 */
interface StoreStream
{
    /**
     * Get aggregate root class.
     *
     * @return string
     */
    public function getAggregateRootClass(): string;

    /**
     * Get aggregate identity.
     *
     * @return Identity
     */
    public function getAggregateId(): Identity;
}
