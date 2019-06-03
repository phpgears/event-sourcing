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
use PHPUnit\Framework\TestCase;

/**
 * AggregateVersion tests.
 */
class AggregateVersionTest extends TestCase
{
    /**
     * @expectedException \Gears\EventSourcing\Aggregate\Exception\AggregateException
     * @expectedExceptionMessage Version value should be higher than 0, -1 given
     */
    public function testInvalidValue(): void
    {
        new AggregateVersion(-1);
    }

    public function testCreation(): void
    {
        $version = new AggregateVersion(10);

        $this->assertEquals(10, $version->getValue());
    }

    public function testGetNext(): void
    {
        $version = new AggregateVersion(10);

        $next = $version->getNext();

        $this->assertEquals(11, $next->getValue());
        $this->assertNotSame($version, $next);
    }

    public function testGetPrevious(): void
    {
        $version = new AggregateVersion(10);

        $previous = $version->getPrevious();

        $this->assertEquals(9, $previous->getValue());
        $this->assertNotSame($version, $previous);
    }

    public function testEquality(): void
    {
        $version = new AggregateVersion(10);

        $this->assertTrue($version->isEqualTo(new AggregateVersion(10)));
        $this->assertFalse($version->isEqualTo(new AggregateVersion(11)));
    }
}
