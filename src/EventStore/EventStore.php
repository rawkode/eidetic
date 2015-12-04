<?php

namespace Rawkode\Eidetic\EventStore;

use Rawkode\Eidetic\EventSourcing\EventSourcedEntity;
use Rawkode\Eidetic\EventSourcing\VerifyEventIsAClassTrait;

abstract class EventStore implements Serializer
{
    use EventPublisherMixin;
    use VerifyEventIsAClassTrait;

    // Subscriber hooks
    const TRANSACTION_STARTED = 'eidetic.eventstore.transaction_started';
    const TRANSACTION_COMPLETED = 'eidetic.eventstore.transaction_completed';
    const EVENT_PRE_STORE = 'eidetic.eventstore.event_pre_store';
    const EVENT_STORED = 'eidetic.eventstore.event_stored';

    /**
     * @param EventSourcedEntity $eventSourcedEntity
     *
     * @throws InvalidEventException
     */
    abstract protected function persist(EventSourcedEntity $eventSourcedEntity, $event);

    /**
     * Return all event log entries for $entityIdentifier.
     *
     * @param string $entityIdentifier
     *
     * @return array
     */
    abstract protected function eventLog($entityIdentifier);

    abstract protected function startTransaction(EventSourcedEntity $eventSourcedEntity);
    abstract protected function abortTransaction(EventSourcedEntity $eventSourcedEntity);
    abstract protected function completeTransaction(EventSourcedEntity $eventSourcedEntity);

    abstract protected function countEntityEvents($entityIdentifier);

    /**
     * Returns the class associated with an entity identifier.
     *
     * @param string $entityIdentifier
     *
     * @return string
     */
    abstract public function entityClass($entityIdentifier);

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
            $this->startTransaction($eventSourcedEntity);
            $this->publishAll(self::TRANSACTION_STARTED, $eventSourcedEntity);

            $this->enforceEventIntegrity($eventSourcedEntity);

            foreach ($eventSourcedEntity->stagedEvents() as $event) {
                $this->publishAll(self::EVENT_PRE_STORE, $eventSourcedEntity, $eventSourcedEntity->stagedEvents());
                $this->persist($eventSourcedEntity, $event);
                $this->publishAll(self::EVENT_STORED, $eventSourcedEntity, $eventSourcedEntity->stagedEvents());
            }
        } catch (\Exception $exception) {
            $this->abortTransaction($eventSourcedEntity);
            throw $exception;
        }

        $this->completeTransaction($eventSourcedEntity);
        $this->publishAll(self::TRANSACTION_COMPLETED, $eventSourcedEntity);
    }

    /**
     * @param EventSourcedEntity $eventSourcedEntity [description]
     *
     * @throws InvalidEventException
     */
    private function enforceEventIntegrity(EventSourcedEntity $eventSourcedEntity)
    {
        foreach ($eventSourcedEntity->stagedEvents() as $event) {
            $this->verifyEventIsAClass($event);
        }
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
