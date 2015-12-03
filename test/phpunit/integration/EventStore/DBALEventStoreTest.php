<?php

namespace Rawkode\Eidetic\Tests;

use Rawkode\Eidetic\EventStore\DBALEventStore\DBALEventStore;

final class DBALEventStoreTest extends EventStoreTest
{
    /**
     */
    public function setUp()
    {
        parent::setUp();

        $this->eventStore = DBALEventStore::createWithOptions('events', [
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ]);

        $this->eventStore->createTable();
    }

    /**
     */
    public function tearDown()
    {
        $this->eventStore->dropTable();
    }
}
