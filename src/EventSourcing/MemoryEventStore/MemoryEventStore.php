<?php

namespace Rawkode\Eidetic\EventSourcing\MemoryEventStore;

use Rawkode\Eidetic\EventSourcing\EventSourcedEntity;
use Rawkode\Eidetic\EventSourcing\EventStore\EntityDoesNotExist;
use Rawkode\Eidetic\EventSourcing\EventStore\EventStore;

final class MemoryEventStore implements EventStore
{
    /**
     * @var array
     */
    private $events = [ ];

    /**
     * @param  string $entityIdentifier
     * @throws EntityDoesNotExist
     * @return array
     */
    public function fetchEntityEvents($entityIdentifier)
    {
        if (false === array_key_exists($entityIdentifier, $this->events)) {
            throw new EntityDoesNotExist();
        }

        return $this->events[$entityIdentifier];
    }

    /**
     * @param  EventSourcedEntity $eventSourcedEntity
     */
    public function save(EventSourcedEntity &$eventSourcedEntity)
    {
        // New Entity?
        if (false === array_key_exists($eventSourcedEntity->identifier(), $this->events)) {
            $this->events[$eventSourcedEntity->identifier()] = [
                'version' => 0,
                'events' => [ ]
            ];
        }

        // Version mis-match?
        if ($this->events[$eventSourcedEntity->identifier()]['version'] !== $eventSourcedEntity->version()) {
            // TODO: Should we try and replay staged events ontop of the current version
            //  throwing an exception if we can't continue?
            throw new OutOfSync();
        }

        foreach ($eventSourcedEntity->stagedEvents() as $event) {
            $this->events[$eventSourcedEntity->identifier()]['version'] += 1;
            $this->events[$eventSourcedEntity->identifier()]['events'][] = $event;
        }
    }
}
