<?php

namespace Rawkode\Eidetic\EventStore;

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
     * @param int    $eventHook
     * @param object $event
     */
    public function publish($eventHook, $event)
    {
        /** @var Subscriber $subscriber */
        foreach ($this->subscribers as $subscriber) {
            $subscriber->handle($eventHook, $event);
        }
    }
}
