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

use Gears\EventSourcing\Event\AbstractAggregateEvent;
use Gears\Identity\Identity;

/**
 * Abstract aggregate event stub class.
 */
class AbstractAggregateEventStub extends AbstractAggregateEvent
{
    /**
     * Instantiate event.
     *
     * @param Identity   $aggregateId
     * @param array|null $payload
     *
     * @return self
     */
    public static function instance(Identity $aggregateId, ?array $payload = []): self
    {
        return static::occurred($aggregateId, $payload);
    }

    /**
     * {@inheritdoc}
     */
    protected static function composeName(): string
    {
        return 'AbstractAggregateEventStub';
    }
}
