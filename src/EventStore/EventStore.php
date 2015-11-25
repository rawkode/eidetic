<?php

namespace Rawkode\Eidetic\EventStore;

interface EventStore
{
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
     * @param  object $eventSubscriber
     */
    public function registerEventSubscriber($eventSubscriber);
}
