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
    public function it_throws_no_events_for_key_exception_when_no_events_exist()
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
    public function it_saves_the_correct_event_log_meta_data()
    {
        $now = new \DateTime("now", new \DateTimeZone("UTC"));

        $this->eventStore->saveEvents('uuid-1', $this->validEvents);

        $eventLogs = $this->eventStore->fetchEventLogs('uuid-1');

        // Ensure that the time inserted into the event store is within a minute of UTC now
        $this->assertEquals('0', $now->diff($eventLogs[0]['recorded_at'])->format('%i'));

        // Ensure event_class is saved correctly
        $this->assertEquals('stdClass', $eventLogs[0]['event_class']);

    }

    /**
     *
     */
    public function it_loads_events_in_the_correct_order()
    {
        $this->eventStore->saveEvents('uuid-1', $this->validEvents);

        $eventLogs = $this->eventStore->fetchEventLogs('uuid-1');

        $counter = 0;
        
        array_map(function ($eventLog) {
            $this->assertEquals(++$counter, $eventLog['serial_number']);
        }, $eventLogs);
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
