<?php

namespace Rawkode\Eidetic\EventStore\DBAL;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Rawkode\Eidetic\EventStore\AggregateDoesNotExistException;
use Rawkode\Eidetic\EventStore\EventStoreInterface;
use Rawkode\Eidetic\EventStore\SerialNumberIntegrityException;
use Rawkode\Eidetic\SharedKernel\ArrayDomainEventStream;
use Rawkode\Eidetic\SharedKernel\DomainEventInterface;
use Rawkode\Eidetic\SharedKernel\DomainEventStreamInterface;

class EventStore implements EventStoreInterface
{
    /**
 * @var  Connection
*/
    private $connection;

    /**
     * EventStore constructor.
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;

        $this->configureSchema();
    }

    /**
     * @param $aggregateIdentifier
     * @param $serialNumber
     * @param DomainEventStreamInterface $domainEvents
     * @return int
     * @throws SerialNumberIntegrityException
     */
    public function logDomainEventStream($aggregateIdentifier, $serialNumber, DomainEventStreamInterface $domainEvents)
    {
        foreach ($domainEvents as $domainEvent) {
            $this->logDomainEvent($aggregateIdentifier, $serialNumber, $domainEvent);
        }
    }

    /**
     * @param $aggregateIdentifier
     * @param int                  $serialNumber
     * @param DomainEventInterface $domainEvent
     * @return int
     * @throws AggregateDoesNotExistException
     * @throws SerialNumberIntegrityException
     */
    public function logDomainEvent($aggregateIdentifier, $serialNumber, DomainEventInterface $domainEvent)
    {
        try {
            $databaseSerialNumber = $this->fetchLatestSerialNumber($aggregateIdentifier);
        } catch (AggregateDoesNotExistException $aggregateDoesNotExistException) {
            $databaseSerialNumber = 0;
        }

        // If the database serial number is 0, it's the first entry and we can continue
        // If the database serial number is not 0 and it doesn't match what we have, the client is out of sync
        if ($databaseSerialNumber !== 0 && $databaseSerialNumber !== $serialNumber) {
            throw new SerialNumberIntegrityException();
        }

        // Serial number in the database matches what has been passed in as the current value for the new event
        //   - so new event serial number can be incremented
        ++$serialNumber;

        $this->connection->insert(
            'events',
            [
                'serial_number' => $serialNumber,
                'aggregate_identifier' => $aggregateIdentifier,
                'domain_event_class' => get_class($domainEvent),
                'domain_event' => base64_encode(serialize($domainEvent))
            ]
        );

        return $serialNumber;
    }

    /**
     * @param string $aggregateIdentifier
     *
     * @return DomainEventStreamInterface
     *
     * @throws AggregateDoesNotExistException
     */
    public function fetchDomainEventStream($aggregateIdentifier)
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $queryBuilder->select('*');
        $queryBuilder->from('events');
        $queryBuilder->where('aggregate_identifier', '=', $aggregateIdentifier);
        $queryBuilder->orderBy('serial_number', 'ASC');

        $statement = $queryBuilder->execute();

        $events = [];
        while ($row = $statement->fetch()) {
            $events[] = unserialize(base64_decode($row['domain_event']));
        }

        return new ArrayDomainEventStream($events);
    }

    /**
     * @param string $aggregateIdentifier
     *
     * @return bool|string
     * @throws AggregateDoesNotExistException
     */
    private function fetchLatestSerialNumber($aggregateIdentifier)
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $queryBuilder->select('serial_number');
        $queryBuilder->from('events');
        $queryBuilder->where(
            $queryBuilder->expr()->eq('aggregate_identifier', ':aggregate_identifier')
        );
        $queryBuilder->orderBy('serial_number', 'DESC');
        $queryBuilder->setMaxResults(1);

        $queryBuilder->setParameter('aggregate_identifier', $aggregateIdentifier);

        $statement = $queryBuilder->execute();

        if ($statement->rowCount() === 0) {
            throw new AggregateDoesNotExistException();
        }

        return $statement->fetchColumn(0);
    }

    public function startTransaction()
    {
    }

    public function abortTransaction()
    {
    }

    public function completeTransaction()
    {
    }

    /**
     * @return \Doctrine\DBAL\Schema\Table|null
     */
    public function configureSchema()
    {
        $schema = $this->connection->getSchemaManager()->createSchema();

        if ($schema->hasTable('events')) {
            return null;
        }

        $table = $this->configureTable();
        $this->connection->getSchemaManager()->createTable($table);
    }

    public function configureTable()
    {
        $schema = new Schema();

        $table = $schema->createTable('events');

        $table->addColumn('serial_number', 'integer');
        $table->addColumn('aggregate_identifier', 'string', ['length' => 256]);
        $table->addColumn('domain_event_class', 'string', [ 'length' => 256 ]);
        $table->addColumn('domain_event', 'text');

        $table->addUniqueIndex(array('serial_number', 'aggregate_identifier'));

        return $table;
    }
}
