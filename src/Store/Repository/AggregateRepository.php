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

namespace Gears\EventSourcing\Store\Repository;

use Gears\EventSourcing\Aggregate\AggregateRoot;
use Gears\Identity\Identity;

/**
 * Interface AggregateRepository.
 */
interface AggregateRepository
{
    /**
     * @param Identity $aggregateId
     *
     * @throws \Gears\EventSourcing\Store\Repository\Exception\AggregateRootNotFoundException
     *
     * @return AggregateRoot
     */
    public function getAggregateRoot(Identity $aggregateId): AggregateRoot;

    /**
     * @param AggregateRoot $aggregateRoot
     *
     * @throws \Gears\EventSourcing\Store\Repository\Exception\AggregateRepositoryException
     */
    public function saveAggregateRoot(AggregateRoot $aggregateRoot): void;
}
