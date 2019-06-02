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

use Gears\EventSourcing\Aggregate\Exception\AggregateException;

final class AggregateVersion
{
    /**
     * @var int
     */
    private $value;

    /**
     * Version constructor.
     *
     * @param int $value
     */
    public function __construct(int $value)
    {
        if ($value < 0) {
            throw new AggregateException(\sprintf('Version value should be higher than 0, %s given', $value));
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
     * @param AggregateVersion $version
     *
     * @return bool
     */
    public function isEqualTo(self $version): bool
    {
        return $this->value === $version->getValue();
    }

    /**
     * Get next.
     *
     * @return self
     */
    public function getNext(): self
    {
        $clone = clone $this;
        $clone->value = $this->value + 1;

        return $clone;
    }
}