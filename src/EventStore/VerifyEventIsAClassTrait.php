<?php

namespace Rawkode\Eidetic\EventStore;

use Rawkode\Eidetic\EventStore\InvalidEventException;

trait VerifyEventIsAClassTrait
{
    /**
     * @param object $event
     *
     * @throws InvalidArgumentException
     */
    private function verifyEventIsAClass($event)
    {
        try {
            $class = get_class($event);
        } catch (\Exception $exception) {
            throw new InvalidEventException();
        }

        if ($class === false) {
            throw new InvalidEventException();
        }
    }
}
