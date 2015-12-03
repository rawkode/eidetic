<?php

namespace Rawkode\Eidetic\EventSourcing;

trait VerifyEventIsAClassTrait
{
    /**
     * @param object $event
     *
     * @throws InvalidArgumentException
     */
    protected function verifyEventIsAClass($event)
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
