<?php

namespace Rawkode\Eidetic\EventSourcing\EventStore;

/**
 *
 */
interface EventPublisher
{
    /**
     * @param object $event
     */
    public function publish($event);
}
