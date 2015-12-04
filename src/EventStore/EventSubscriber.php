<?php

namespace Rawkode\Eidetic\EventStore;

use Rawkode\Eidetic\EventSourcing\EventSourcedEntity;

interface EventSubscriber
{
    /**
     * @param int                $eventHook
     * @param EventSourcedEntity $eventSourcedEntity
     * @param object             $event
     */
    public function handle($eventHook, EventSourcedEntity $eventSourcedEntity, $event);
}
