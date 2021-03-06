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

use Gears\EventSourcing\Aggregate\Exception\AggregateVersionException;

final class AggregateVersion implements \Serializable
{
    /**
     * @var int
     */
    private $value;

    /**
     * Version constructor.
     *
     * @param int $value
     *
     * @throws AggregateVersionException
     */
    public function __construct(int $value)
    {
        if ($value < 0) {
            throw new AggregateVersionException(\sprintf('Version value should be higher than 0, "%s" given', $value));
        }

        $this->value = $value;
    }

    /**
     * Get version value.
     *
     * @return int
     */
    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * Check equality.
     *
     * @param self $version
     *
     * @return bool
     */
    public function isEqualTo(self $version): bool
    {
        return $this->value === $version->getValue();
    }

    /**
     * Get next version.
     *
     * @return self
     */
    public function getNext(): self
    {
        $clone = clone $this;
        $clone->value = $this->value + 1;

        return $clone;
    }

    /**
     * Get previous version.
     *
     * @throws AggregateVersionException
     *
     * @return AggregateVersion
     */
    public function getPrevious(): self
    {
        if ($this->value === 0) {
            throw new AggregateVersionException('Version value cannot be lowered below 0');
        }

        $clone = clone $this;
        $clone->value = $this->value - 1;

        return $clone;
    }

    /**
     * @return array<string, mixed>
     */
    public function __serialize(): array
    {
        return ['value' => $this->value];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function __unserialize(array $data): void
    {
        $this->value = $data['value'];
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): string
    {
        return \serialize($this->value);
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $serialized
     */
    public function unserialize($serialized): void
    {
        $this->value = \unserialize($serialized, ['allowed_classes' => false]);
    }
}
