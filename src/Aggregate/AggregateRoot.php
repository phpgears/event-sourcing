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

use Gears\Aggregate\AggregateRoot as BaseAggregateRoot;
use Gears\EventSourcing\Event\AggregateEventStream;

/**
 * AggregateRoot interface.
 */
interface AggregateRoot extends BaseAggregateRoot, \Serializable
{
    /**
     * Get aggregate version.
     *
     * @return AggregateVersion
     */
    public function getVersion(): AggregateVersion;

    /**
     * Reconstitute aggregate from a list of aggregate event stream.
     *
     * @param AggregateEventStream $eventStream
     *
     * @throws \Gears\EventSourcing\Aggregate\Exception\AggregateException
     *
     * @return mixed|self
     */
    public static function reconstituteFromEventStream(AggregateEventStream $eventStream);

    /**
     * Replay aggregate event stream.
     *
     * @param AggregateEventStream $eventStream
     */
    public function replayAggregateEventStream(AggregateEventStream $eventStream): void;

    /**
     * Get recorded aggregate event stream.
     *
     * @return AggregateEventStream
     */
    public function getRecordedAggregateEvents(): AggregateEventStream;

    /**
     * Remove recorded aggregate events from aggregate root.
     */
    public function clearRecordedAggregateEvents(): void;

    /**
     * Collect recorded aggregate event stream and remove events from aggregate root.
     *
     * @return AggregateEventStream
     */
    public function collectRecordedAggregateEvents(): AggregateEventStream;
}
