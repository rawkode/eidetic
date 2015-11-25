<?php

namespace Rawkode\Eidetic\EventStore;

use Doctrine\Common\EventSubscriber;

trait EventPublisherMixin
{
    /**
     * @var array
     */
    private $eventSubscribers = [ ];

    /**
     * @param EventSubscriber $eventSubscriber
     */
    public function registerEventSubscriber($eventSubscriber)
    {
        array_push($this->eventSubscribers, $eventSubscriber);
    }

    /**
     * @param  int $eventHook
     * @param  object $event
     */
    public function publish($eventHook, $event)
    {
        /** @var EventSubscriber $eventSubscriber */
        foreach ($this->eventSubscribers as $eventSubscriber) {
            $this->eventSubscriber->handle($eventHook, $event);
        }
    }
}
