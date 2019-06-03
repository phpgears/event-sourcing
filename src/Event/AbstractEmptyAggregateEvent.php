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

use Gears\DTO\ScalarPayloadBehaviour;
use Gears\Event\Time\SystemTimeProvider;
use Gears\Event\Time\TimeProvider;
use Gears\EventSourcing\Aggregate\AggregateBehaviour;
use Gears\EventSourcing\Aggregate\AggregateVersion;
use Gears\Identity\Identity;
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
     * @param Identity           $aggregateId
     * @param AggregateVersion   $aggregateVersion
     * @param \DateTimeImmutable $createdAt
     */
    final protected function __construct(
        Identity $aggregateId,
        AggregateVersion $aggregateVersion,
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
     * @param Identity          $aggregateId
     * @param TimeProvider|null $timeProvider
     *
     * @return mixed|self
     */
    final protected static function occurred(Identity $aggregateId, ?TimeProvider $timeProvider = null)
    {
        $timeProvider = $timeProvider ?? new SystemTimeProvider();

        return new static(
            $aggregateId,
            new AggregateVersion(1),
            $timeProvider->getCurrentTime()
        );
    }

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
