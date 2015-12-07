<?php

namespace Rawkode\Eidetic\Tests;

use Rawkode\Eidetic\EventStore\DBALEventStore\DBALEventStore;

final class Test extends EventStoreTest
{
    /**
     */
    public function setUp()
    {
        parent::setUp();

        $databaseOptions = [];
        $databaseOptions['driver'] = getenv('DATABASE_DRIVER');

        switch ($databaseOptions['driver']) {
            case 'pdo_mysql':
            case 'pdo_pgsql':
                $databaseOptions['host'] = getenv('DATABASE_HOST');
                $databaseOptions['port'] = getenv('DATABASE_PORT');
                $databaseOptions['user'] = getenv('DATABASE_USER');
                $databaseOptions['password'] = getenv('DATABASE_PASS');
                $databaseOptions['dbname'] = getenv('DATABASE_NAME');
                break;

            default:
                $databaseOptions['driver'] = 'pdo_sqlite';
                $databaseOptions['memory'] = true;
                break;
        }

        $this->eventStore = DBALEventStore::createWithOptions('events', $databaseOptions);

        $this->eventStore->createTable();
    }

    /**
     * @test
     */
    public function it_will_not_create_table_if_it_exists()
    {
        $this->setExpectedException('Rawkode\Eidetic\EventStore\DBALEventStore\TableAlreadyExistsException');

        $this->eventStore->createTable();
    }

    /**
     */
    public function tearDown()
    {
        $this->eventStore->dropTable();
    }
}
