<?php

namespace Rawkode\Eidetic\Tests\Integration\EventStore\DBALEventStore;

use Rawkode\Eidetic\Tests\Integration\EventStore\EventStoreTest;
use Rawkode\Eidetic\EventStore\DBALEventStore\DBALEventStore;
use Doctrine\DBAL\DriverManager;

final class DBALEventStoreTest extends EventStoreTest
{
    public function setUp()
    {
        parent::setUp();

        $this->eventStore = DBALEventStore::createWithOptions('events', [
            'driver' => 'pdo_sqlite',
            'memory' => true
        ]);

        $this->eventStore->createTable();
    }

    public function tearDown()
    {
        $this->eventStore->dropTable();
    }
}
