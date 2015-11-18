<?php

namespace Rawkode\Eidetic\EventSourcing\MemoryEventStore;

use Rawkode\Eidetic\EventSourcing\EventSourcedEntity;
use Rawkode\Eidetic\EventSourcing\EventStore\EntityDoesNotExistException;
use Rawkode\Eidetic\EventSourcing\EventStore\OutOfSync;
use Rawkode\Eidetic\EventSourcing\EventStore\EventStore;
use Rawkode\Eidetic\EventSourcing\EventStore\EventStoreException;
use Rawkode\Eidetic\EventSourcing\EventStore\VersionMismatchException;

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
            throw new EntityDoesNotExistException();
        }

        return $this->events[$entityIdentifier]['events'];
    }

    /**
     * @param  EventSourcedEntity $eventSourcedEntity
     * @throws OutOfSyncException
     */
    public function save(EventSourcedEntity $eventSourcedEntity)
    {
        $identifier = $eventSourcedEntity->identifier();
        $version = $eventSourcedEntity->version();

        $this->verifyVersion($eventSourcedEntity);

        foreach ($eventSourcedEntity->stagedEvents() as $event) {
            $this->events[$identifier]['events'][] = [
                'date_time' => new \DateTime('now', new \DateTimeZone('UTC')),
                'version' => ++$version,
                'event_class' => get_class($event),
                'event' => $event
            ];
        }
    }

    /**
     * @param  EventSourcedEntity $eventSourcedEntity
     */
    private function verifyVersion(EventSourcedEntity $eventSourcedEntity)
    {
        try {
            if ($this->entityVersion($eventSourcedEntity->identifier()) !== $eventSourcedEntity->version()) {
                throw new VersionMismatchException();
            }
        } catch (EntityDoesNotExistException $entityDoesNotExistException) {
            // We don't care, proceed
        }
    }

    /**
     * @param  string $identifier
     * @throws EntityDoesNotExist
     * @return int
     */
    private function entityVersion($identifier)
    {
        if (false === array_key_exists($identifier, $this->events)) {
            throw new EntityDoesNotExistException();
        }

        return end($this->events[$identifier]['events'])['version'];
    }
}
