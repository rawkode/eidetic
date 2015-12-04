<?php

namespace Rawkode\Eidetic\EventStore\InMemoryEventStore;

use Rawkode\Eidetic\EventStore\InvalidEventException;
use Rawkode\Eidetic\EventStore\EventStore;
use Rawkode\Eidetic\EventStore\NoEventsFoundForKeyException;
use Rawkode\Eidetic\EventSourcing\EventSourcedEntity;

final class InMemoryEventStore extends EventStore
{
    /**
     * @var array
     */
    private $events = [];

    /**
     * @var array
     */
    private $transactionBackup = [];

    /**
     * @param EventSourcedEntity $eventSourcedEntity
     *
     * @throws InvalidEventException
     */
    protected function persist(EventSourcedEntity $eventSourcedEntity, $event)
    {
        if (false === array_key_exists($eventSourcedEntity->identifier(), $this->events)) {
            $this->events[$eventSourcedEntity->identifier()] = [];
        }

        $this->events[$eventSourcedEntity->identifier()][] = [
            'entity_identifier' => $eventSourcedEntity->identifier(),
            'serial_number' => count($this->events[$eventSourcedEntity->identifier()]) + 1,
            'entity_class' => get_class($eventSourcedEntity),
            'recorded_at' => new \DateTime('now', new \DateTimeZone('UTC')),
            'event_class' => get_class($event),
            'event' => $this->serialize($event),
        ];

        array_push($this->stagedEvents, $event);
    }

    /**
     * @param string $entityIdentifier
     *
     * @throws NoEventsFoundForKeyException
     *
     * @return array
     */
    protected function eventLog($entityIdentifier)
    {
        $this->verifyEventExistsForKey($entityIdentifier);

        return array_map(function ($eventLogEntry) {
            $eventLogEntry['event'] = $this->unserialize($eventLogEntry['event']);

            return $eventLogEntry;
        }, $this->events[$entityIdentifier]);
    }

    /**
     */
    protected function startTransaction(EventSourcedEntity $eventSourcedEntity)
    {
        $this->transactionBackup = $this->events;

        $this->stagedEvents = [];
    }

    /**
     */
    protected function abortTransaction(EventSourcedEntity $eventSourcedEntity)
    {
        $this->events = $this->transactionBackup;
        $this->stagedEvents = [];
    }

    /**
     */
    protected function completeTransaction(EventSourcedEntity $eventSourcedEntity)
    {
        $this->transactionBackup = [];

        $this->stagedEvents = [];
    }

    /**
     * @param string $entityIdentifier
     *
     * @throws NoEventsFoundForKeyException
     *
     * @return int
     */
    protected function countEntityEvents($entityIdentifier)
    {
        if (false === array_key_exists($entityIdentifier, $this->events)) {
            return 0;
        }

        return count($this->events[$entityIdentifier]);
    }

    /**
     * @param string $entityIdentifier
     *
     * @throws NoEventsFoundForKeyException
     *
     * @return string
     */
    public function entityClass($entityIdentifier)
    {
        $this->verifyEventExistsForKey($entityIdentifier);

        return $this->events[$entityIdentifier][0]['entity_class'];
    }
}
