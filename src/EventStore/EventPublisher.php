<?php

namespace Rawkode\Eidetic\EventStore;

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
