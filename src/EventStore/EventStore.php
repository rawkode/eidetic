<?php

namespace Rawkode\Eidetic\EventStore;

interface EventStore
{
    // Subscriber hooks
    const EVENT_STORED = "eidetic.eventstore.event_stored";

    /**
     * @param string $key
     * @param array  $events
     */
    public function store($key, array $events);

    /**
     * @param string $key
     *
     * @return array
     */
    public function retrieve($key);

    /**
     * @param string $key
     */
    public function retrieveLogs($key);

    /**
     * @param  Subscriber $subscriber
     */
    public function registerSubscriber($subscriber);

    /**
     * @param  int $eventHook
     * @param  object $event
     */
    public function publish($eventHook, $event);
}
