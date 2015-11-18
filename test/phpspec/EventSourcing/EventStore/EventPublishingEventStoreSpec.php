<?php

namespace phpspec\Rawkode\Eidetic\EventSourcing\EventStore;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

use Rawkode\Eidetic\EventSourcing\EventSourcedEntity;
use Rawkode\Eidetic\EventSourcing\EventStore\EventStore;
use Rawkode\Eidetic\EventSourcing\EventStore\EventPublisher;

final class EventPublishingEventStoreSpec extends ObjectBehavior
{
    private $eventStore;
    private $eventPublisher;

    function let(EventStore $eventStore, EventPublisher $eventPublisher)
    {
        $this->eventStore = $eventStore;
        $this->eventPublisher = $eventPublisher;

        $this->beConstructedWith($eventStore, $eventPublisher);
    }

    function it_implements_event_store()
    {
        $this->shouldHaveType('Rawkode\Eidetic\EventSourcing\EventStore\EventStore');
    }

    function it_proxies_the_save_command(EventSourcedEntity $eventSourcedEntity)
    {
        $eventSourcedEntity->stagedEvents()->willReturn([ new \stdClass ]);

        $this->eventStore->save($eventSourcedEntity)->shouldBeCalled();

        $this->save($eventSourcedEntity);
    }

    function it_publishes_events_to_event_publisher(EventSourcedEntity $eventSourcedEntity)
    {
        $eventSourcedEntity->stagedEvents()->willReturn([ new \stdClass ]);

        $this->eventPublisher->publish(Argument::type('stdClass'))->shouldBeCalled();

        $this->save($eventSourcedEntity);
    }
}
