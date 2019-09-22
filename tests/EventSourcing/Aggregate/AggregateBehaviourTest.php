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
use Gears\EventSourcing\Tests\Stub\AggregateBehaviourStub;
use Gears\Identity\UuidIdentity;
use PHPUnit\Framework\TestCase;

/**
 * AggregateBehaviour trait test.
 */
class AggregateBehaviourTest extends TestCase
{
    public function testGetters(): void
    {
        $identity = UuidIdentity::fromString('3247cb6e-e9c7-4f3a-9c6c-0dec26a0353f');

        $stub = new AggregateBehaviourStub($identity, new AggregateVersion(10));

        static::assertSame($identity, $stub->getIdentity());
        static::assertSame(10, $stub->getVersion()->getValue());
    }
}
