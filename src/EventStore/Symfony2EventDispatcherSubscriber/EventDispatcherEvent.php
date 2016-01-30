<?php

namespace Rawkode\Eidetic\EventStore\Symfony2EventDispatcherSubscriber;

use Symfony\Component\EventDispatcher\Event;
use Rawkode\Eidetic\EventSourcing\EventSourcedEntity;

final class EventDispatcherEvent extends Event
{
    /** @var EventSourcedEntity */
    private $entity;

    /** @var object */
    private $event;

    /**
     * @param object $event
     */
    public function __construct(EventSourcedEntity $eventSourcedEntity, $event)
    {
        $this->entity = $eventSourcedEntity;
        $this->event = $event;
    }

    /**
     * @return EventSourcedEntity
     */
    public function entity()
    {
        return $this->entity;
    }

    /**
     * @return object
     */
    public function event()
    {
        return $this->event;
    }
}
