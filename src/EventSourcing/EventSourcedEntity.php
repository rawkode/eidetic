<?php

namespace Rawkode\Eidetic\EventSourcing;

interface EventSourcedEntity
{
    /**
     * @return string
     */
    public function identifier();

    /**
     * @return int
     */
    public function version();

    /**
     * Initialise without staging the events.
     *
     * @param array $eventStream
     */
    public function initialise(array $eventStream);

    /**
     * Return a list of staged events.
     *
     * @return array
     */
    public function stagedEvents();

    /**
     */
    public function commit();
}
