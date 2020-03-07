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
use Gears\EventSourcing\Aggregate\Exception\AggregateVersionException;
use PHPUnit\Framework\TestCase;

/**
 * AggregateVersion tests.
 */
class AggregateVersionTest extends TestCase
{
    public function testInvalidValue(): void
    {
        $this->expectException(AggregateVersionException::class);
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
        $this->expectException(AggregateVersionException::class);
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

    public function testSerialization(): void
    {
        $version = new AggregateVersion(10);

        $serialized = \version_compare(\PHP_VERSION, '7.4.0') >= 0
            ? 'O:46:"Gears\EventSourcing\Aggregate\AggregateVersion":1:{s:5:"value";i:10;}'
            : 'C:46:"Gears\EventSourcing\Aggregate\AggregateVersion":5:{i:10;}';

        static::assertSame($serialized, \serialize($version));
        static::assertSame(10, (\unserialize($serialized))->getValue());
    }
}
