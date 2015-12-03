<?php

namespace phpspec\Rawkode\Eidetic\EventStore\Symfony2EventDispatcherSubscriber;

use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Rawkode\Eidetic\EventStore\EventStore;
use Rawkode\Eidetic\EventStore\Symfony2EventDispatcherSubscriber\EventDispatcherEvent;
use Example\User;

class Symfony2EventDispatcherSubscriberSpec extends ObjectBehavior
{
    /** @var User */
    private $user;

    /** @var EventDispatcher */
    private $eventDispatcher;

    public function let(EventDispatcher $eventDispatcher)
    {
        $this->user = User::createWithUsername('Rawkode');
        $this->eventDispatcher = $eventDispatcher;

        $this->beConstructedWith($eventDispatcher);
    }

    public function it_implements_event_subscriber_interface()
    {
        $this->shouldHaveType('Rawkode\Eidetic\EventStore\EventSubscriber');
    }

    public function it_can_dispatch_the_event()
    {
        $eventDispatcherEvent = new EventDispatcherEvent($this->user, new \stdClass());

        $this->eventDispatcher
            ->dispatch(EventStore::EVENT_STORED, $eventDispatcherEvent)
            ->shouldBeCalled();

        $this->handle(EventStore::EVENT_STORED, new \stdClass());
    }
}
