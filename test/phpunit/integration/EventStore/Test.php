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

        $this->eventStore = DBALEventStore::createWithOptions('events', [
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ]);

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
