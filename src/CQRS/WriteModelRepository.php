<?php

namespace Rawkode\Eidetic\CQRS;

use Rawkode\Eidetic\EventSourcing\EventSourcedEntity;

interface WriteModelRepository
{
    /**
     * @param string $identifier
     *
     * @return array
     */
    public function load($identifier);

    /**
     * @param EventSourcedEntity $eventSourcedEntity
     *
     * @throws \Exception
     */
    public function save(EventSourcedEntity $eventSourcedEntity);
}
