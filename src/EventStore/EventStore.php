<?php

namespace Rawkode\Eidetic\EventStore;

use Rawkode\Eidetic\EventSourcing\EventSourcedEntity;

abstract class EventStore implements Serializer
{
    use EventPublisherMixin;

    // Subscriber hooks
    const EVENT_STORED = 'eidetic.eventstore.event_stored';

    // Implement these in your concretion

    /**
     * @param EventSourcedEntity $eventSourcedEntity
     *
     * @throws InvalidEventException
     */
    abstract protected function persist(EventSourcedEntity $eventSourcedEntity);

    /**
     * Return all event log entries for $entityIdentifier.
     *
     * @param string $entityIdentifier
     *
     * @return array
     */
    abstract protected function eventLog($entityIdentifier);

    abstract protected function startTransaction();
    abstract protected function abortTransaction();
    abstract protected function completeTransaction();

    abstract protected function countEntityEvents($entityIdentifier);

    /**
     * Returns the class associated with an entity identifier.
     *
     * @param string $entityIdentifier
     *
     * @return string
     */
    abstract protected function entityClass($entityIdentifier);

    /** @var array */
    protected $stagedEvents = [];

    /** @var Serializer */
    protected $serializer;

    /**
     * Store an EventSourcedEntity's staged events.
     *
     * @param EventSourcedEntity $eventSourcedEntity
     */
    public function store(EventSourcedEntity $eventSourcedEntity)
    {
        try {
            $this->startTransaction();
            $this->persist($eventSourcedEntity);
        } catch (\Exception $exception) {
            $this->abortTransaction();
            throw $exception;
        }

        $this->completeTransaction();
    }

    /**
     * Returns all events for $entityIdentifier.
     *
     * @param string $entityIdentifier
     *
     * @return array
     */
    public function retrieve($entityIdentifier)
    {
        $eventLog = $this->eventLog($entityIdentifier);

        return array_map(function ($eventLogEntry) {
            return $eventLogEntry['event'];
        }, $eventLog);
    }

    /**
     * Returns all the log entries for $entityIdentifier.
     *
     * @param string $entityIdentifier
     *
     * @return array
     */
    public function retrieveLog($entityIdentifier)
    {
        return $this->eventLog($entityIdentifier);
    }

    /**
     * @param string $entityIdentifier
     *
     * @throws NoEventsFoundForKeyException
     */
    protected function verifyEventExistsForKey($entityIdentifier)
    {
        if (0 === $this->countEntityEvents($entityIdentifier)) {
            throw new NoEventsFoundForKeyException();
        }
    }

    /**
     * @param object $object
     *
     * @return string
     */
    public function serialize($object)
    {
        if (false === is_null($this->serializer)) {
            return $this->serializer->serialize($object);
        }

        return base64_encode(serialize($object));
    }

    /**
     * @param string $serializedObject
     *
     * @return object
     */
    public function unserialize($serializedObject)
    {
        if (false === is_null($this->serializer)) {
            return $this->serializer->serialize($serializedObject);
        }

        return unserialize(base64_decode($serializedObject));
    }
}
