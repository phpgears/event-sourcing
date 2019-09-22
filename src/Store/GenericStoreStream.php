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

use Gears\EventSourcing\Aggregate\AggregateRoot;
use Gears\EventSourcing\Aggregate\Exception\AggregateException;
use Gears\Identity\Identity;
use Gears\Immutability\ImmutabilityBehaviour;

/**
 * GenericStoreStream class.
 */
final class GenericStoreStream implements StoreStream
{
    use ImmutabilityBehaviour;

    /**
     * @var string
     */
    private $aggregateRootClass;

    /**
     * @var Identity
     */
    private $aggregateId;

    /**
     * SimpleStoreStream constructor.
     *
     * @param string   $aggregateRootClass
     * @param Identity $aggregateId
     */
    private function __construct(string $aggregateRootClass, Identity $aggregateId)
    {
        $this->assertImmutable();

        $this->aggregateRootClass = $aggregateRootClass;
        $this->aggregateId = $aggregateId;
    }

    /**
     * Create from aggregate data.
     *
     * @param string   $aggregateRootClass
     * @param Identity $aggregateId
     *
     * @throws AggregateException
     *
     * @return self
     */
    public static function fromAggregateData(string $aggregateRootClass, Identity $aggregateId): self
    {
        if (!\class_exists($aggregateRootClass)) {
            throw new AggregateException(
                \sprintf('Aggregate root class "%s" cannot be found', $aggregateRootClass)
            );
        }

        if (!\in_array(AggregateRoot::class, \class_implements($aggregateRootClass), true)) {
            throw new AggregateException(\sprintf(
                'Aggregate root class must implement "%s", "%s" given',
                AggregateRoot::class,
                $aggregateRootClass
            ));
        }

        return new self($aggregateRootClass, $aggregateId);
    }

    /**
     * Create from aggregate root.
     *
     * @param AggregateRoot $aggregateRoot
     *
     * @return GenericStoreStream
     */
    public static function fromAggregateRoot(AggregateRoot $aggregateRoot): self
    {
        return new self(\get_class($aggregateRoot), $aggregateRoot->getIdentity());
    }

    /**
     * {@inheritdoc}
     */
    public function getAggregateRootClass(): string
    {
        return $this->aggregateRootClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getAggregateId(): Identity
    {
        return $this->aggregateId;
    }

    /**
     * {@inheritdoc}
     *
     * @return string[]
     */
    protected function getAllowedInterfaces(): array
    {
        return [StoreStream::class];
    }
}
