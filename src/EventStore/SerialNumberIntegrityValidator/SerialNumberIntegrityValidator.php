<?php

namespace Rawkode\Eidetic\EventStore\SerialNumberIntegrityValidator;

use Rawkode\Eidetic\EventStore\EventSubscriber;

final class SerialNumberIntegrityValidator implements EventSubscriber
{
    /**
     * @param int    $eventHook
     * @param object $event
     */
    public function handle($eventHook, $event)
    {
    }
}
