<?php

namespace Rawkode\Eidetic\Tests;

use Rawkode\Eidetic\EventStore\InMemoryEventStore\InMemoryEventStore;

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
