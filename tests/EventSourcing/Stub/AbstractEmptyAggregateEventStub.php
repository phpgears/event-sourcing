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

use Gears\EventSourcing\Aggregate\AggregateVersion;
use Gears\EventSourcing\Event\AbstractEmptyAggregateEvent;
use Gears\Identity\Identity;

/**
 * Abstract empty event stub class.
 */
class AbstractEmptyAggregateEventStub extends AbstractEmptyAggregateEvent
{
    /**
     * Instantiate event.
     *
     * @param Identity $aggregateId
     *
     * @return self
     */
    public static function instance(Identity $aggregateId): self
    {
        return self::occurred($aggregateId);
    }

    /**
     * Copy event with new version.
     *
     * @param self             $event
     * @param AggregateVersion $version
     *
     * @return static
     */
    public static function withVersion(self $event, AggregateVersion $version): self
    {
        return $event::reconstitute(
            $event->getPayload(),
            [
                'aggregateId' => $event->getAggregateId(),
                'aggregateVersion' => $version,
                'metadata' => $event->getMetadata(),
                'createdAt' => $event->getCreatedAt(),
            ]
        );
    }
}
