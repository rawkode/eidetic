<?php

namespace Rawkode\Eidetic\EventStore;

use Doctrine\Common\EventSubscriber;

interface EventStore
{
    // Subscriber hooks
    const EVENT_STORED = 0b00000001;

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
     * @param  EventSubscriber $eventSubscriber
     */
    public function registerEventSubscriber($eventSubscriber);

    /**
     * @param  int $eventHook
     * @param  object $event
     */
    public function publish($eventHook, $event);
}
