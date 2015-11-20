<?php

namespace Rawkode\Eidetic\EventStore;

interface EventStore
{
    /**
     * @param string $key
     * @param array  $events
     */
    public function saveEvents($key, array $events);

    /**
     * @param string $key
     *
     * @return array
     */
    public function fetchEvents($key);
}
