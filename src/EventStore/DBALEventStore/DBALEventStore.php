<?php

namespace Rawkode\Eidetic\EventStore\DBALEventStore;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Rawkode\Eidetic\EventStore\EventStore;
use Rawkode\Eidetic\EventStore\NoEventsFoundForKeyException;
use Rawkode\Eidetic\EventSourcing\EventSourcedEntity;

final class DBALEventStore extends EventStore
{
    /**
     * @var string
     */
    private $tableName;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param string     $tableName
     * @param Connection $connection
     */
    private function __construct($tableName, Connection $connection)
    {
        $this->tableName = $tableName;
        $this->connection = $connection;
    }

    /**
     * @param string $tableName
     * @param array  $options
     *
     * @return self
     */
    public static function createWithOptions($tableName, array $options)
    {
        $connection = DriverManager::getConnection($options);

        return new self($tableName, $connection);
    }

    /**
     * @param EventSourcedEntity $eventSourcedEntity
     */
    protected function persist(EventSourcedEntity $eventSourcedEntity)
    {
        $eventCount = $this->countEntityEvents($eventSourcedEntity->identifier());

        array_map(function ($event) use ($eventSourcedEntity, &$eventCount) {
            $this->connection->insert($this->tableName, [
                'entity_identifier' => $eventSourcedEntity->identifier(),
                'serial_number' => ++$eventCount,
                'entity_class' => get_class($eventSourcedEntity),
                'recorded_at' => new \DateTime('now', new \DateTimeZone('UTC')),
                'event_class' => get_class($event),
                'event' => $this->serialize($event),
            ], [
                \PDO::PARAM_STR,
                \PDO::PARAM_INT,
                \PDO::PARAM_STR,
                'datetime',
                \PDO::PARAM_STR,
                \PDO::PARAM_STR,
            ]);

            array_push($this->stagedEvents, $event);
        }, $eventSourcedEntity->stagedEvents());
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
        if (0 === $this->countEntityEvents($entityIdentifier)) {
            throw new NoEventsFoundForKeyException();
        }

        $statement = $this->eventLogQuery($entityIdentifier)->execute();

        $eventLog = $statement->fetchAll();

        return array_map(function ($eventLogEntry) {
            $eventLogEntry['event'] = $this->unserialize($eventLogEntry['event']);
            $eventLogEntry['recorded_at'] = new \DateTime($eventLogEntry['recorded_at']);

            return $eventLogEntry;
        }, $eventLog);
    }

    /**
     */
    protected function startTransaction()
    {
        $this->connection->beginTransaction();
        $this->stagedEvents = [];
    }

    /**
     */
    protected function abortTransaction()
    {
        $this->connection->rollBack();
        $this->stagedEvents = [];
    }

    /**
     */
    protected function completeTransaction()
    {
        $this->connection->commit();

        array_map(function ($event) {
            $this->publish(self::EVENT_STORED, $event);
        }, $this->stagedEvents);

        $this->stagedEvents = [];
    }

    /**
     */
    public function createTable()
    {
        $schemaManager = $this->connection->getSchemaManager();
        $schema = $schemaManager->createSchema();

        if ($schema->hasTable($this->tableName)) {
            return;
        }

        $table = $schema->createTable($this->tableName);

        $table->addColumn('entity_identifier', 'string', ['length' => 255]);
        $table->addColumn('serial_number', 'integer');

        $table->setPrimaryKey(['entity_identifier', 'serial_number']);

        $table->addColumn('entity_class', 'string', ['length' => 255]);
        $table->addColumn('recorded_at', 'datetime');
        $table->addColumn('event_class', 'string', ['length' => 255]);
        $table->addColumn('event', 'text');

        $table->addIndex(['entity_class']);
        $table->addIndex(['recorded_at']);
        $table->addIndex(['event_class']);

        $schemaManager->createTable($table);
    }

    /**
     */
    public function dropTable()
    {
        $this->connection->getSchemaManager()->dropTable($this->tableName);
    }

    /**
     * @param string $entityIdentifier
     *
     * @return int
     */
    private function countEntityEvents($entityIdentifier)
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $queryBuilder->select('COUNT(entity_identifier)');
        $queryBuilder->from($this->tableName);
        $queryBuilder->where('entity_identifier = :entity_identifier');

        $queryBuilder->setParameter('entity_identifier', $entityIdentifier);

        return (int) $queryBuilder->execute()->fetchColumn(0);
    }

    /**
     * @param string $entityIdentifier
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    protected function eventLogQuery($entityIdentifier)
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $queryBuilder->select('*');
        $queryBuilder->from($this->tableName);
        $queryBuilder->where('entity_identifier = :entity_identifier');
        $queryBuilder->orderBy('serial_number', 'ASC');

        $queryBuilder->setParameter('entity_identifier', $entityIdentifier);

        return $queryBuilder;
    }

    /**
     * @param string $entityIdentifier
     *
     * @return array
     */
    protected function singleLogForKey($entityIdentifier)
    {
        $queryBuilder = $this->eventLogQuery($entityIdentifier);

        $queryBuilder->setMaxResults(1);

        $statement = $queryBuilder->execute();

        $results = $statement->fetchAll();

        return $results;
    }
}
