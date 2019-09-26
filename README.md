[![PHP version](https://img.shields.io/badge/PHP-%3E%3D7.1-8892BF.svg?style=flat-square)](http://php.net)
[![Latest Version](https://img.shields.io/packagist/v/phpgears/event-sourcing.svg?style=flat-square)](https://packagist.org/packages/phpgears/event-sourcing)
[![License](https://img.shields.io/github/license/phpgears/event-sourcing.svg?style=flat-square)](https://github.com/phpgears/event-sourcing/blob/master/LICENSE)

[![Build Status](https://img.shields.io/travis/phpgears/event-sourcing.svg?style=flat-square)](https://travis-ci.org/phpgears/event-sourcing)
[![Style Check](https://styleci.io/repos/149037535/shield)](https://styleci.io/repos/149037535)
[![Code Quality](https://img.shields.io/scrutinizer/g/phpgears/event-sourcing.svg?style=flat-square)](https://scrutinizer-ci.com/g/phpgears/event-sourcing)
[![Code Coverage](https://img.shields.io/coveralls/phpgears/event-sourcing.svg?style=flat-square)](https://coveralls.io/github/phpgears/event-sourcing)

[![Total Downloads](https://img.shields.io/packagist/dt/phpgears/event-sourcing.svg?style=flat-square)](https://packagist.org/packages/phpgears/event-sourcing/stats)
[![Monthly Downloads](https://img.shields.io/packagist/dm/phpgears/event-sourcing.svg?style=flat-square)](https://packagist.org/packages/phpgears/event-sourcing/stats)

# Event Sourcing

Event Sourcing base

## Installation

### Composer

```
composer require phpgears/event-sourcing
```

## Usage

Require composer autoload file

```php
require './vendor/autoload.php';
```

### Aggregate identity

Aggregate identities are provided by [gears/identity](https://github.com/phpgears/identity), head over there to learn about them

### Aggregate events

Aggregate Events must implement `Gears\EventSourcing\Event\AggregateEvent` interface so they can be used by Aggregate roots. You can extend from `Gears\EventSourcing\Event\AbstractAggregateEvent` for simplicity 

```php
use Gears\EventSourcing\Event\AbstractAggregateEvent;
use Gears\Identity\Identity;

class AggregateCreated extends AbstractAggregateEvent
{
    public static function instantiate(Identity $aggregateId)
    {
        return self::occurred($aggregateId, []);
    }
}

/**
 * @method getThing(): string
 */
class SomethingHappened extends AbstractAggregateEvent
{
    public static function hasHappened(Identity $aggregateId, string $whatHappened)
    {
        return self::occurred($aggregateId, ['thing' => $whatHappened]);
    }
}

/**
 * @method getReason(): string
 */
class SomethingFinished extends AbstractAggregateEvent
{
    public static function hasFinished(Identity $aggregateId, string $reason)
    {
        return self::occurred($aggregateId, ['reason' => $reason]);
    }
}
```

Mind that AbstractAggregateEvent constructor is protected forcing you to create static named constructors methods so you can take advantage of type hinting for payload. Although you could use protected method to create aggregate events you're encouraged to use `occurred` method

This events are then recorded and applied on Aggregate root

### Aggregate root

Aggregate root should implement `Gears\EventSourcing\Aggregate\AggregateRoot` interface. You can extend from `Gears\EventSourcing\Aggregate\AbstractAggregateRoot` for simplicity

```php
use Gears\EventSourcing\Aggregate\AbstractAggregateRoot;
use Gears\Identity\Identity;

class CustomAggregate extends AbstractAggregateRoot
{
    public static function create(Identity $identity): self
    {
        $instance = new self();
        
        $instance->recordAggregateEvent(AggregateCreated::instantiate($identity));
        
        return $instance;
    }

    public function doSomething(): void
    {
        $this->recordAggregateEvent(SomethingHappened::hasHappened($this->getIdentity(), 'this happened'));
    }

    public function finishSomething(): void
    {
        $this->recordAggregateEvent(SomethingFinished::hasFinished($this->getIdentity(), 'this finished'));
        $this->recordEvent(WhateverFinished::instance());
    }

    protected function applyAggregateCreated(AggregateCreated $event): void
    {
        $this->setIdentity($event->getAggregateId());
    }

    protected function applySomethingHappened(SomethingHappened $event): void
    {
        // do something with $event->getThing();
    }

    protected function applySomethingFinished(SomethingFinished $event): void
    {
        // do something with $event->getReason();
    }
}
```

#### Aggregate operations

Every operation in aggregates should be made through aggregate events, even aggregate's own creation (see example above), that's the reason AbstractAggregateRoot constructor is protected.

Aggregate events represent facts relevant to the Event Sourcing system such as AggregateCreated, SomethingHappened and SomethingFinished in the previous example

Aggregate events should be finally collected, persisted on an event store and eventually dispatched to an event bus depending on the Projection mechanism at work

```php
$aggregateId = CustomAggregateIdentity::fromString('4c4316cb-b48b-44fb-a034-90d789966bac');
$customAggregate = CustomAggregate::create($aggregateId);
$customAggregate->doSomething();

$eventStore->store($customAggregate->collectRecordedAggregateEvents());
```

#### Aggregate Events vs Domain Events

> This is a key difference between this package and other Event Sourcing libraries out there

Aggregate roots can collect two fundamentally different types of events, Aggregate events have already been discussed in the section above, the second kind of events are Domain events which represent facts relevant to the Domain

They differ drastically and conceptually on who are this events relevant or meant to. While Aggregate Events are meant _only_ for the Event Sourcing system, that is Event Store persistence, Aggregate root reconstitution, Projection, Snapshotting, Sagas and others, Domain Events are relevant to the application Domain in itself, that is this and/or other parts or Bounded Contexts of your system

**You should NEVER use Aggregate events to drive your application execution or signal other parts of your system, You MUST use Domain events for those purposes**

Domain events must be collected and sent to an event bus, head to [gears/event](https://github.com/phpgears/event) to learn more about this topic

```php
foreach ($customAggregate->collectRecordedEvents() as $domainEvent) {
    /** @var \Gears\Event\EventBus $eventBus */
    $eventBus->dispatch($domainEvent);
}
```

##### Event granularity

Granularity is key when dealing with this two kinds of events. As a rule of thumb you can consider that Aggregate Events can be mapped to atomic actions that can be performed on an aggregate root, while Domain events can be seen as actions that a user can perform on the domain, they will naturally have a 1:1 relationship in most cases, but not necessarily in all cases

An example that can be of some help to understand this difference is a two step process of user sign up in a system with email validation

While on a first step a user is created in the system (data is fully or partially collected) the user cannot be considered completely registered until he validates his email on a second step

You can consider two methods on the User aggregate to accomplish this task, createUser() that creates the user Aggregate Root with whatever data provided, and validateUser() which validates the user based for example on a code sent to his email

Both of these methods will create an Aggregate Event that will be eventually persisted onto the Event Store, but only the later will create a UserRegistered domain event which can be relevant for other parts of your system (e.g. creating a user wallet on the billing Bounded Context)

### Aggregate Repository


### Event Store


#### Concurrency


### Snapshot Store


## Contributing

Found a bug or have a feature request? [Please open a new issue](https://github.com/phpgears/event-sourcing/issues). Have a look at existing issues before.

See file [CONTRIBUTING.md](https://github.com/phpgears/event-sourcing/blob/master/CONTRIBUTING.md)

## License

See file [LICENSE](https://github.com/phpgears/event-sourcing/blob/master/LICENSE) included with the source code for a copy of the license terms.
