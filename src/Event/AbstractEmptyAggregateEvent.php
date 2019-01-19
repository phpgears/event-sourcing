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

use Gears\Aggregate\AggregateIdentity;
use Gears\DTO\ScalarPayloadBehaviour;
use Gears\Event\Time\SystemTimeProvider;
use Gears\Event\Time\TimeProvider;
use Gears\EventSourcing\Aggregate\AggregateBehaviour;
use Gears\Immutability\ImmutabilityBehaviour;

use function DeepCopy\deep_copy;

/**
 * Abstract empty immutable aggregate event.
 */
abstract class AbstractEmptyAggregateEvent implements AggregateEvent
{
    use ImmutabilityBehaviour, ScalarPayloadBehaviour, AggregateBehaviour {
        ScalarPayloadBehaviour::__call insteadof ImmutabilityBehaviour;
        AggregateBehaviour::getIdentity as private;
        AggregateBehaviour::getVersion as private;
    }

    /**
     * @var \DateTimeImmutable
     */
    private $createdAt;

    /**
     * Prevent aggregate event direct instantiation.
     *
     * @param AggregateIdentity  $aggregateId
     * @param int                $aggregateVersion
     * @param \DateTimeImmutable $createdAt
     */
    final protected function __construct(
        AggregateIdentity $aggregateId,
        int $aggregateVersion,
        \DateTimeImmutable $createdAt
    ) {
        $this->checkImmutability();

        $this->identity = $aggregateId;
        $this->version = $aggregateVersion;
        $this->createdAt = $createdAt->setTimezone(new \DateTimeZone('UTC'));
    }

    /**
     * Instantiate new aggregate event.
     *
     * @param AggregateIdentity $aggregateId
     * @param TimeProvider      $timeProvider
     *
     * @return mixed|self
     */
    final protected static function occurred(
        AggregateIdentity $aggregateId,
        ?TimeProvider $timeProvider = null
    ) {
        $timeProvider = $timeProvider ?? new SystemTimeProvider();

        return new static(
            $aggregateId,
            1,
            $timeProvider->getCurrentTime()
        );
    }

    /**
     * {@inheritdoc}
     */
    final public function getAggregateId(): AggregateIdentity
    {
        return $this->identity;
    }

    /**
     * {@inheritdoc}
     */
    final public function getAggregateVersion(): int
    {
        return $this->version;
    }

    /**
     * {@inheritdoc}
     */
    final public function withAggregateVersion(int $aggregateVersion)
    {
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

    /**
     * {@inheritdoc}
     *
     * @return mixed|self
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    final public static function reconstitute(array $payload, array $attributes = [])
    {
        return new static(
            $attributes['aggregateId'],
            $attributes['aggregateVersion'],
            $attributes['createdAt']
        );
    }

    /**
     * {@inheritdoc}
     *
     * @return string[]
     */
    final protected function getAllowedInterfaces(): array
    {
        return [AggregateEvent::class];
    }
}
