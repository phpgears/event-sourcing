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

namespace Gears\EventSourcing\Store\Snapshot;

use Gears\EventSourcing\Store\StoreStream;

/**
 * SnapshotStore interface.
 */
interface SnapshotStore
{
    /**
     * Get aggregate root.
     *
     * @param StoreStream $stream
     *
     * @throws \Gears\EventSourcing\Store\Snapshot\Exception\SnapshotStoreException
     *
     * @return Snapshot|null
     */
    public function load(StoreStream $stream): ?Snapshot;

    /**
     * Save aggregate root.
     *
     * @param Snapshot $snapshot
     *
     * @throws \Gears\EventSourcing\Store\Snapshot\Exception\SnapshotStoreException
     */
    public function store(Snapshot $snapshot): void;
}
