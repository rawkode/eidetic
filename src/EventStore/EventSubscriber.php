<?php

namespace Rawkode\Eidetic\EventStore;

interface EventSubscriber
{
    /**
     * @param int    $eventHook
     * @param object $event
     */
    public function handle($eventHook, $event);
}
