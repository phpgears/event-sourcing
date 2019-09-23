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

namespace Gears\EventSourcing\Tests\Aggregate;

use Gears\EventSourcing\Aggregate\AggregateVersion;
use Gears\EventSourcing\Aggregate\Exception\AggregateException;
use PHPUnit\Framework\TestCase;

/**
 * AggregateVersion tests.
 */
class AggregateVersionTest extends TestCase
{
    public function testInvalidValue(): void
    {
        $this->expectException(AggregateException::class);
        $this->expectExceptionMessage('Version value should be higher than 0, "-1" given');

        new AggregateVersion(-1);
    }

    public function testCreation(): void
    {
        $version = new AggregateVersion(10);

        static::assertEquals(10, $version->getValue());
    }

    public function testGetNext(): void
    {
        $version = new AggregateVersion(10);

        $next = $version->getNext();

        static::assertEquals(11, $next->getValue());
        static::assertNotSame($version, $next);
    }

    public function testInvalidPrevious(): void
    {
        $this->expectException(AggregateException::class);
        $this->expectExceptionMessage('Version value cannot be lowered below 0');

        $version = new AggregateVersion(0);

        $version->getPrevious();
    }

    public function testGetPrevious(): void
    {
        $version = new AggregateVersion(10);

        $previous = $version->getPrevious();

        static::assertEquals(9, $previous->getValue());
        static::assertNotSame($version, $previous);
    }

    public function testEquality(): void
    {
        $version = new AggregateVersion(10);

        static::assertTrue($version->isEqualTo(new AggregateVersion(10)));
        static::assertFalse($version->isEqualTo(new AggregateVersion(11)));
    }
}
