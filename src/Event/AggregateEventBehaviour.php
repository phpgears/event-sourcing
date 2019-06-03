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

use Gears\Event\Exception\EventException;
use Gears\EventSourcing\Aggregate\AggregateBehaviour;
use Gears\EventSourcing\Aggregate\AggregateVersion;
use Gears\Identity\Identity;

use function DeepCopy\deep_copy;

/**
 * Abstract immutable aggregate event.
 */
trait AggregateEventBehaviour
{
    use AggregateBehaviour {
        AggregateBehaviour::getIdentity as private;
        AggregateBehaviour::getVersion as private;
    }

    /**
     * @var \DateTimeImmutable
     */
    private $createdAt;

    /**
     * {@inheritdoc}
     */
    final public function getAggregateId(): Identity
    {
        return $this->identity;
    }

    /**
     * {@inheritdoc}
     */
    final public function getAggregateVersion(): AggregateVersion
    {
        return $this->version;
    }

    /**
     * {@inheritdoc}
     */
    final public function withAggregateVersion(AggregateVersion $aggregateVersion)
    {
        if (!$this->version->isEqualTo(new AggregateVersion(0))) {
            throw new EventException(\sprintf(
                'Only new events can get a new version, event %s already at version %s',
                \get_class($this),
                $this->version->getValue()
            ));
        }

        if ($aggregateVersion->isEqualTo(new AggregateVersion(0))) {
            throw new EventException(\sprintf(
                'Aggregate events can not get version 0 set, version 0 given to event %s',
                \get_class($this)
            ));
        }

        /* @var self $self */
        $self = deep_copy($this);
        $self->version = $aggregateVersion;

        return $self;
    }

    /**
     * {@inheritdoc}
     */
    final public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
