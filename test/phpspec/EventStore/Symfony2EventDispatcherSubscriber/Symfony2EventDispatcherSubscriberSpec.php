<?php

namespace phpspec\Rawkode\Eidetic\EventStore\Symfony2EventDispatcherSubscriber;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Rawkode\Eidetic\EventStore\EventStore;
use Rawkode\Eidetic\EventStore\Symfony2EventDispatcherSubscriber\EventDispatcherEvent;

class Symfony2EventDispatcherSubscriberSpec extends ObjectBehavior
{
    /** @var EventDispatcher */
    private $eventDispatcher;

    function let(EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;

        $this->beConstructedWith($eventDispatcher);
    }

    function it_implements_event_subscriber_interface()
    {
        $this->shouldHaveType('Rawkode\Eidetic\EventStore\EventSubscriber');
    }

    function it_can_dispatch_the_event()
    {
        $event = new \stdClass;
        $eventDispatcherEvent = new EventDispatcherEvent($event);

        $this->eventDispatcher
            ->dispatch(EventStore::EVENT_STORED, $eventDispatcherEvent)
            ->shouldBeCalled();

        $this->handle(EventStore::EVENT_STORED, $event);
    }
}
