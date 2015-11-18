<?php

namespace Rawkode\Eidetic\EventSourcing\InMemoryEventStore;

use Rawkode\Eidetic\EventSourcing\EventSourcedEntity;
use Rawkode\Eidetic\EventSourcing\EventStore\EntityDoesNotExistException;
use Rawkode\Eidetic\EventSourcing\EventStore\TransactionAlreadyInProgressException;
use Rawkode\Eidetic\EventSourcing\EventStore\EventStore;
use Rawkode\Eidetic\EventSourcing\EventStore\VersionMismatchException;
use Rawkode\Eidetic\EventSourcing\InvalidEventException;

final class InMemoryEventStore implements EventStore
{
    /** @var array */
    private $events = [];

    /** @var bool */
    private $transactionInProgress = false;

    /** @var array */
    private $transactionBackup = [];

    /**
     * @param string $entityIdentifier
     *
     * @throws EntityDoesNotExist
     *
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
     * @param EventSourcedEntity $eventSourcedEntity
     *
     * @throws OutOfSyncException
     * @throws InvalidEventException
     */
    public function save(EventSourcedEntity $eventSourcedEntity)
    {
        $this->verifyVersion($eventSourcedEntity);

        $version = $eventSourcedEntity->version();

        try {
            $this->startTransaction();

            foreach ($eventSourcedEntity->stagedEvents() as $event) {
                $this->storeEvent($eventSourcedEntity->identifier(), $event, ++$version);
            }
        } catch (TransactionAlreadyInProgressException $transactionAlreadyInProgressExeception) {
            throw $transactionAlreadyInProgressExeception;
        } catch (InvalidEventException $invalidEventException) {
            $this->abortTransaction();

            throw $invalidEventException;
        }

        $this->completeTransaction();
    }

    /**
     */
    private function startTransaction()
    {
        if (true === $this->transactionInProgress) {
            throw new TransactionAlreadyInProgressException();
        }

        $this->transactionBackup = $this->events;
        $this->transactionInProgress = true;
    }

    /**
     */
    private function abortTransaction()
    {
        $this->events = $this->transactionBackup;
        $this->transactionInProgress = false;
    }

    /**
     */
    private function completeTransaction()
    {
        $this->transactionBackup = [];
        $this->transactionInProgress = false;
    }

    /**
     * @param string $entityIdentifier
     * @param  $event
     * @param int $version
     *
     * @throws InvalidEventException
     */
    private function storeEvent($entityIdentifier, $event, $version)
    {
        $this->verifyEventIsAClass($event);

        $this->events[$entityIdentifier]['events'][] = [
            'date_time' => new \DateTime('now', new \DateTimeZone('UTC')),
            'version' => $version,
            'event_class' => get_class($event),
            'event' => $event,
        ];
    }

    /**
     * @param $event
     */
    private function verifyEventIsAClass($event)
    {
        try {
            // Do we need to worry about E_WARNING not raising an exception depending on php.ini?
            get_class($event);
        } catch (\Exception $exception) {
            throw new InvalidEventException();
        }
    }

    /**
     * @param EventSourcedEntity $eventSourcedEntity
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
     * @param string $identifier
     *
     * @throws EntityDoesNotExist
     *
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
