<?php

namespace Rawkode\Eidetic\Tests\Integration\EventStore;

abstract class EventStoreTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EventStore
     */
    protected $eventStore;

    /**
     * @var array
     */
    protected $validEvents;

    /**
     * @var array
     */
    protected $invalidEvents;

    public function setUp()
    {
        $this->validEvents = [
            new \stdClass(),
            new \stdClass(),
            new \stdClass(),
        ];

        $this->invalidEvents = [
            new \stdClass(),
            0,
            new \stdClass(),
        ];
    }

    /**
     * @test
     */
    public function it_throws_no_events_for_identifier_exception_when_no_events_exist()
    {
        $this->setExpectedException('Rawkode\Eidetic\EventStore\NoEventsFoundForKeyException');

        $this->eventStore->fetchEvents('uuid-1');
    }

    /**
     * @test
     */
    public function it_can_save_and_fetch_events()
    {
        $this->eventStore->saveEvents('uuid-1', $this->validEvents);

        $this->assertEquals($this->validEvents, $this->eventStore->fetchEvents('uuid-1'));
    }

    /**
     * @test
     */
    public function it_throws_invalid_event_exception_when_event_is_not_an_object()
    {
        $this->setExpectedException('Rawkode\Eidetic\EventStore\InvalidEventException');

        $this->eventStore->saveEvents('uuid-1', $this->invalidEvents);
    }

    /**
     * @test
     */
    public function it_rollbacks_the_transaction_during_an_exception()
    {
        $this->eventStore->saveEvents('uuid-1', $this->validEvents);

        $this->setExpectedException('Rawkode\Eidetic\EventStore\InvalidEventException');

        $this->eventStore->saveEvents('uuid-1', $this->invalidEvents);

        $this->assertEquals($this->validEvents, $this->eventStore->fetchEvents('uuid-1'));
    }
}
