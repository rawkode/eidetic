<?php

namespace Rawkode\Eidetic\EventStore\Symfony2EventDispatcherSubscriber;

use Symfony\Component\EventDispatcher\Event;

final class EventDispatcherEvent extends Event
{
    /** @var object */
    private $event;

    /**
     * @param obkect $event
     */
    public function __construct($event)
    {
        $this->event = $event;
    }

    /**
     * @return object
     */
    public function event()
    {
        return $this->event;
    }
}
