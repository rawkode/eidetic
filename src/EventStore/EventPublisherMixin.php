<?php

namespace Rawkode\Eidetic\EventStore;

trait EventPublisherMixin
{
    /**
     * @var object
     */
    private $eventSubscribers = [ ];

    /**
     * @param object $eventSubscriber
     */
    public function registerEventSubscriber($eventSubscriber)
    {
        array_push($this->eventSubscribers, $eventSubscriber);
    }
}
