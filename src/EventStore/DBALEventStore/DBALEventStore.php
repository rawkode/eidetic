<?php

namespace Rawkode\Eidetic\EventStore\DBALEventStore;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Table;
use Rawkode\Eidetic\EventStore\InvalidEventException;
use Rawkode\Eidetic\EventStore\EventStore;
use Rawkode\Eidetic\EventStore\NoEventsFoundForKeyException;
use Rawkode\Eidetic\EventStore\VerifyEventIsAClassTrait;

final class DBALEventStore implements EventStore
{
    use VerifyEventIsAClassTrait;

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
     * @param string $key
     * @param array  $events
     */
    public function store($key, array $events)
    {
        try {
            $this->startTransaction();

            foreach ($events as $event) {
                $this->persistEvent($key, $event);
            }
        } catch (InvalidEventException $invalidEventException) {
            $this->abortTransaction();

            throw $invalidEventException;
        }

        $this->completeTransaction();
    }

    /**
     * @param string $key
     *
     * @return array
     */
    public function retrieve($key)
    {
        $results = $this->eventLogs($key);

        return array_map(function ($eventLog) {
            return unserialize(base64_decode($eventLog['event']));
        }, $results);
    }

    /**
     * @param string $key
     *
     * @return array
     */
    public function retrieveLogs($key)
    {
        return $this->eventLogs($key);
    }

    /**
     * @param string $key
     *
     * @return array
     */
    private function eventLogs($key)
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $queryBuilder->select('*');
        $queryBuilder->from($this->tableName);
        $queryBuilder->where('key = :key');
        $queryBuilder->orderBy('serial_number', 'ASC');

        $queryBuilder->setParameter('key', $key);

        $statement = $queryBuilder->execute();

        $results = $statement->fetchAll();

        if (count($results) === 0) {
            throw new NoEventsFoundForKeyException();
        }

        return array_map(function ($eventLog) {
            if (true === array_key_exists('recorded_at', $eventLog)) {
                $eventLog['recorded_at'] = new \DateTime($eventLog['recorded_at']);
            }

            return $eventLog;
        }, $results);
    }

    /**
     */
    private function startTransaction()
    {
        $this->connection->beginTransaction();
    }

    /**
     */
    private function abortTransaction()
    {
        $this->connection->rollBack();
    }

    /**
     */
    private function completeTransaction()
    {
        $this->connection->commit();
    }

    /**
     * @param string $key
     * @param object $event
     */
    private function persistEvent($key, $event)
    {
        $this->verifyEventIsAClass($event);

        $this->connection->insert($this->tableName, [
            'key' => $key,
            'recorded_at' => new \DateTime('now', new \DateTimeZone('UTC')),
            'event_class' => get_class($event),
            'event' => base64_encode(serialize($event)),
        ], [
            \PDO::PARAM_STR,
            'datetime',
            \PDO::PARAM_STR,
            \PDO::PARAM_STR,
        ]);
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

        $serialNumberColumn = $table->addColumn('serial_number', 'integer');
        $serialNumberColumn->setAutoincrement(true);
        $table->setPrimaryKey(['serial_number']);

        $table->addColumn('key', 'string', ['length' => 255]);
        $table->addColumn('recorded_at', 'datetime');
        $table->addColumn('event_class', 'string', ['length' => 255]);
        $table->addColumn('event', 'text');

        $table->addIndex(['key']);
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
}
