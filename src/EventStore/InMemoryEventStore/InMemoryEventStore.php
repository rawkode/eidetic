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
    protected function persist(EventSourcedEntity $eventSourcedEntity)
    {
        if (false === array_key_exists($eventSourcedEntity->identifier(), $this->events)) {
            $this->events[$eventSourcedEntity->identifier()] = [];
        }

        foreach ($eventSourcedEntity->stagedEvents() as $event) {
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
    protected function startTransaction()
    {
        $this->transactionBackup = $this->events;
        $this->stagedEvents = [];
    }

    /**
     */
    protected function abortTransaction()
    {
        $this->events = $this->transactionBackup;
        $this->stagedEvents = [];
    }

    /**
     */
    protected function completeTransaction()
    {
        $this->transactionBackup = [];

        foreach ($this->stagedEvents as $event) {
            $this->publish(self::EVENT_STORED, $event);
        }

        $this->stagedEvents = [];
    }

    /**
     * @param string $entityIdentifier
     *
     * @throws NoEventsFoundForKeyException
     */
    protected function verifyEventExistsForKey($entityIdentifier)
    {
        if (false === array_key_exists($entityIdentifier, $this->events)) {
            throw new NoEventsFoundForKeyException();
        }
    }
}
