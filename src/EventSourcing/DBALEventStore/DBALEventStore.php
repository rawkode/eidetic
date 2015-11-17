<?php

namespace Rawkode\Eidetic\EventSourcing\DBALEventStore;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Rawkode\Eidetic\EventSourcing\EventStore\EntityDoesNotExist;
use Rawkode\Eidetic\EventSourcing\EventStore\OutOfSync;

class DBALEventStore implements EventStoreInterface
{
    /** @var  Connection */
    private $connection;

    /** @var string */
    private $tableName;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection, $tableName)
    {
        $this->connection = $connection;
        $this->tableName = $tableName;
    }

    /**
     * @param EventSourcedEntity $eventSourcedEntity
     */
    public function save(EventSourcedEntity &$eventSourcedEntity)
    {
        $version = 0;

        try {
            $version = $this->fetchEntityLatestVersion($eventSourcedEntity->identifier());
        } catch (EntityDoesNotExist $EntityDoesNotExist) {
            // version has already been set to zero, continue
        }

        // If the event sourced entity version doesn't match that of our database,
        //  then it is out of sync.
        if ($version !== 0 && $version !== $eventSourcedEntity->version()) {
            throw new OutOfSync();
        }

        foreach ($this->eventSourcedEntity->stagedEvents() as $event) {
            $this->connection->insert(
                $this->tableName,
                [
                    'date_time' => new \DateTime('now', new \DateTimeZone('UTC')),
                    'entity_identifier' => $eventSourcedEntity->identifier(),
                    'entity_version' => ++$version,
                    'event_class' => get_class($event),
                    'event' => base64_encode(serialize($event))
                ]
            );
        }
    }

    /**
     * @param string $entityIdentifier
     * @return array
     */
    public function fetchEntityEvents($entityIdentifier)
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $queryBuilder->select('*');
        $queryBuilder->from($this->tableName);
        $queryBuilder->where('entity_identifier', '=', $entityIdentifier);
        $queryBuilder->orderBy('version', 'ASC');

        $statement = $queryBuilder->execute();

        if (0 === $statement->rowCount()) {
            throw new EntityDoesNotExist();
        }

        while ($row = $statement->fetch()) {
            $events[] = unserialize(base64_decode($row['event']));
        }

        return $events;
    }

    /**
     * @param string $entityIdentifier
     * @throws EntityDoesNotExist
     * @return int
     */
    private function fetchLatestSerialNumber($entityIdentifier)
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $queryBuilder->select('version');
        $queryBuilder->from($this->tableName);
        $queryBuilder->where(
            $queryBuilder->expr()->eq('entity_identifier', ':entity_identifier')
        );
        $queryBuilder->orderBy('version', 'DESC');
        $queryBuilder->setMaxResults(1);

        $queryBuilder->setParameter('entity_identifier', $entityIdentifier);

        $statement = $queryBuilder->execute();

        if ($statement->rowCount() === 0) {
            throw new EntityDoesNotExist();
        }

        return $statement->fetchColumn(0);
    }
}
