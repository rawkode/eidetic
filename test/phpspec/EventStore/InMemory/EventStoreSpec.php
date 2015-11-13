<?php

namespace phpspec\Rawkode\Eidetic\EventStore\InMemory;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Rawkode\Eidetic\EventSourcing\AggregateInterface;
use Rawkode\Eidetic\EventSourcing\AggregateTrait;
use Rawkode\Eidetic\SharedKernel\DomainEventInterface;

final class EventStoreSpec extends ObjectBehavior
{
    function it_implements_the_event_store_interface()
    {
        $this->shouldHaveType('Rawkode\Eidetic\EventStore\EventStoreInterface');
    }

    function it_can_create_an_aggregate(DomainEventInterface $domainEvent)
    {
        $aggregate = new AggregateFake();

        $this->shouldNotThrow('Rawkode\Eidetic\EventStore\SerialNumberIntegrityException')
            ->during('logDomainEvent', [$aggregate->identifier(), $aggregate->serialNumber(), $domainEvent]);

        $this->fetchDomainEventStream($aggregate->identifier())->count()->shouldBe(1);
    }

    function it_can_update_an_aggregate(DomainEventInterface $domainEvent1, DomainEventInterface $domainEvent2)
    {
        $aggregate = new AggregateFake();

        $this->logDomainEvent($aggregate->identifier(), $aggregate->serialNumber(), $domainEvent1);
        $aggregate->applyDomainEvent();

        $this->shouldNotThrow()->during('logDomainEvent', [$aggregate->identifier(), $aggregate->serialNumber(), $domainEvent2]);

        $this->fetchDomainEventStream($aggregate->identifier())->count()->shouldBe(2);
    }

    function it_will_throw_an_exception_during_an_update_if_not_sequential(
        DomainEventInterface $domainEvent1,
        DomainEventInterface $domainEvent2
    ) {
        $aggregate = new AggregateFake();

        $this->logDomainEvent($aggregate->identifier(), $aggregate->serialNumber(), $domainEvent1);
        $this->shouldThrow()->during('logDomainEvent', [$aggregate->identifier(), $aggregate->serialNumber(), $domainEvent2]);
    }

    function it_can_load_an_aggregate(
        DomainEventInterface $domainEvent1,
        DomainEventInterface $domainEvent2,
        DomainEventInterface $domainEvent3
    ) {
        $aggregate = new AggregateFake();

        $this->logDomainEvent($aggregate->identifier(), $aggregate->serialNumber(), $domainEvent1);
        $aggregate->applyDomainEvent();
        $this->logDomainEvent($aggregate->identifier(), $aggregate->serialNumber(), $domainEvent2);
        $aggregate->applyDomainEvent();
        $this->logDomainEvent($aggregate->identifier(), $aggregate->serialNumber(), $domainEvent3);
        $aggregate->applyDomainEvent();

        $this->fetchDomainEventStream($aggregate->identifier())->shouldHaveType('Rawkode\Eidetic\SharedKernel\DomainEventStreamInterface');
        $this->fetchDomainEventStream($aggregate->identifier())->count()->shouldBe(3);
    }
}

class AggregateFake implements AggregateInterface
{
    use AggregateTrait;

    public static $counter = 1;

    public function __construct()
    {
        $this->identifier = self::$counter++;
    }

    public function applyDomainEvent()
    {
        $this->serialNumber++;
    }
}
