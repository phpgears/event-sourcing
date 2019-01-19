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

By extending `Gears\Aggregate\AbstractAggregateIdentity` you can easily create your own event-sourcing identities

```php
use Gears\EventSourcing\AbstractAggregateIdentity;

class CustomAggregateIdentity extends AbstractAggregateIdentity
{
    public static function fromString(string $value)
    {
        // Check $value validity

        return new static($value);
    }
}
```

Most used event-sourcing identities are UUID values, for that reason there is already a `Gears\Aggregate\UuidAggregateIdentity` identity ready to be used

If you want to expand on event-sourcing identities head to [gears/identity](https://github.com/phpgears/identity)

#### Aggregate events

Aggregate Events must implement `Gears\EventSourcing\Event\AggregateEvent` interface so they can be used by Aggregate roots. You can extend from `Gears\EventSourcing\Event\AbstractAggregateEvent` for simplicity 

```php
use Gears\Aggregate\AggregateIdentity;
use Gears\EventSourcing\Event\AbstractAggregateEvent;

class AggregateCreated extends AbstractAggregateEvent
{
    public static function instantiate(AggregateIdentity $aggregateId)
    {
        return self::occurred($aggregateId, []);
    }
}

class SomethingHappened extends AbstractAggregateEvent
{
    public static function hasHappened(AggregateIdentity $aggregateId, string $whatHappened)
    {
        return self::occurred($aggregateId, ['thing' => $whatHappened]);
    }
}
```

Mind that AbstractAggregateEvent constructor is protected forcing you to create static named constructors methods so you can take advantage on type hinting for payload. For simplicity protected method `occurred` in available

This events are then recorded and applied on Aggregate roots

### Aggregate root

Aggregate roots should implement `Gears\EventSourcing\Aggregate\AggregateRoot` interface. You can extend from `Gears\EventSourcing\Aggregate\AbstractAggregateRoot` for simplicity

```php
use Gears\Aggregate\AggregateIdentity;
use Gears\EventSourcing\Aggregate\AbstractAggregateRoot;

class CustomAggregate extends AbstractAggregateRoot
{
    public static function create(AggregateIdentity $identity): self
    {
        $instance = new self();
        
        $instance->recordEvent(AggregateCreated::instantiate($identity));
        
        return $instance;
    }

    public function doSomething(): void
    {
        $this->recordEvent(SomethingHappened::hasHappened($this->getIdentity(), 'this happened'));
    }

    protected function applyAggregateCreated(AggregateCreated $event): void
    {
        $this->setIdentity($event->getAggregateId());
    }

    protected function applySomethingHappened(SomethingHappened $event): void
    {
        // do something with $event->get('thing');
    }
}
```

Every operation in aggregates should be made through aggregate events, even aggregate own creation (see example above), that's the reason AbstractAggregateRoot constructor is protected

This events should be later collected and stored on an event store and sent to an event bus such as [gears/event](https://github.com/phpgears/event)

```php
$aggregateId = new UuidAggregate(CustomAggregateIdentity::fromString('4c4316cb-b48b-44fb-a034-90d789966bac'));
$customAggregate = CustomAggregate::create($aggregateId);
$customAggregate->doSomething();

foreach ($customAggregate->collectRecordedEvents() as $aggregateEvent) {
    /** @var \Gears\Event\EventBus $eventBus */
    $eventBus->dispatch($aggregateEvent);
}
```

## Contributing

Found a bug or have a feature request? [Please open a new issue](https://github.com/phpgears/event-sourcing/issues). Have a look at existing issues before.

See file [CONTRIBUTING.md](https://github.com/phpgears/event-sourcing/blob/master/CONTRIBUTING.md)

## License

See file [LICENSE](https://github.com/phpgears/event-sourcing/blob/master/LICENSE) included with the source code for a copy of the license terms.
