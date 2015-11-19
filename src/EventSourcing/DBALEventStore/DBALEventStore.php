<?php

namespace Rawkode\Eidetic\EventSourcing\DBALEventStore;

use Doctrine\DBAL\Connection;
use Rawkode\Eidetic\EventSourcing\EventSourcedEntity;
use Rawkode\Eidetic\EventSourcing\EventStore\EventStore;
use Rawkode\Eidetic\EventSourcing\InvalidEventException;

final class DBALEventStore implements EventStore
{
    /**
     * @var Doctrine\DBAL\Connection
     */
    private $dbalConnection;

    public function __construct(Connection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    /**
     * @param EventSourcedEntity $eventSourcedEntity
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
     * @param string $entityIdentifier
     *
     * @return array
     */
    public function fetchEntityEvents($entityIdentifier)
    {
    }

    /**
     */
    private function startTransaction()
    {
        $this->dbalConnection->beginTransaction();
    }

    /**
     */
    private function abortTransaction()
    {
        $this->dbalConnection->rollBack();
    }

    /**
     */
    private function completeTransaction()
    {
        $this->dbalConnection->commit();
    }

    /**
     * @param string $entityIdentifier
     * @param  $event
     * @param int $version
     */
    private function storeEvent($identifier, $event, $version)
    {
        $this->verifyEventIsAClass($event);
    }

    /**
     * @param EventSourcedEntity $eventSourcedEntity
     */
    private function verifyVersion(EventSourcedEntity $eventSourcedEntity)
    {
        // Get the latest version from database and compare with our entity version
    }

    /**
     * @param object $event
     *
     * @throws InvalidArgumentException
     */
    private function verifyEventIsAClass($event)
    {
        try {
            if (false === get_class($event)) {
                throw new InvalidEventException();
            }
        } catch (\Exception $exception) {
            throw new InvalidEventException();
        }
    }
}
