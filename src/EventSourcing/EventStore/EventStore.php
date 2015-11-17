<?php

namespace Rawkode\Eidetic\EventSourcing\EventStore;

use Rawkode\Eidetic\CQRS\WriteModelRepository;
use Rawkode\Eidetic\EventSourcing\EventSourcedEntity;

interface EventStore
{
    /**
     * @param EventSourcedEntity $eventSourcedEntity
     */
    public function save(EventSourcedEntity &$eventSourcedEntity);

    /**
     * @param string $entityIdentifier
     * @return array
     */
    public function fetchEntityEvents($entityIdentifier);
}
