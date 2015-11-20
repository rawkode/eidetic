<?php

namespace Rawkode\Eidetic\Tests\Integration\EventStore\InMemoryEventStore;

use Rawkode\Eidetic\EventStore\InMemoryEventStore\InMemoryEventStore;
use Rawkode\Eidetic\Tests\Integration\EventStore\EventStoreTest;

final class InMemoryEventStoreTest extends EventStoreTest
{
    /**
     */
    public function setUp()
    {
        parent::setUp();

        $this->eventStore = new InMemoryEventStore();
    }
}
