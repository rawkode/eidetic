<?php

namespace Rawkode\Eidetic\EventStore;

use Rawkode\Eidetic\EventSourcing\EventSourcedEntity;

trait EventPublisherMixin
{
    /**
     * @var array
     */
    protected $subscribers = [];

    /**
     * @param Subscriber $subscriber
     */
    public function registerSubscriber($subscriber)
    {
        array_push($this->subscribers, $subscriber);
    }

    /**
     * @param string             $eventHook
     * @param EventSourcedEntity $eventSourcedEntity
     */
    public function publishAll($eventHook, EventSourcedEntity $eventSourcedEntity)
    {
        foreach ($eventSourcedEntity->stagedEvents() as $event) {
            $this->publish($eventHook, $eventSourcedEntity, $event);
        }
    }

    /**
     * @param int                $eventHook
     * @param EventSourcedEntity $eventSourcedEntity
     * @param object             $event
     */
    public function publish($eventHook, EventSourcedEntity $eventSourcedEntity, $event)
    {
        /** @var Subscriber $subscriber */
        foreach ($this->subscribers as $subscriber) {
            $subscriber->handle($eventHook, $eventSourcedEntity, $event);
        }
    }
}
