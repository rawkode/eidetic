<?php

namespace Rawkode\Eidetic\Tests\Integration\EventStore;
use Rawkode\Eidetic\EventStore\EventStore;

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
    public function it_throws_an_exception_when_loading_an_invalid_key()
    {
        $this->setExpectedException('Rawkode\Eidetic\EventStore\NoEventsFoundForKeyException');

        $this->eventStore->retrieve('uuid-1');
    }

    /**
     * @test
     */
    public function it_can_save_and_load_events_by_their_key()
    {
        $this->eventStore->store('uuid-1', $this->validEvents);

        $this->assertEquals($this->validEvents, $this->eventStore->retrieve('uuid-1'));
    }

    /**
     * @test
     */
    public function it_saves_the_correct_event_log_meta_data()
    {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        $this->eventStore->store('uuid-1', $this->validEvents);

        $eventLogs = $this->eventStore->retrieveLogs('uuid-1');

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
        $this->eventStore->store('uuid-1', $this->validEvents);

        $eventLogs = $this->eventStore->retrieve('uuid-1');

        $counter = 0;

        array_map(function ($eventLog) {
            $this->assertEquals(++$counter, $eventLog['serial_number']);
        }, $eventLogs);
    }

    /**
     * @test
     */
    public function it_does_not_allow_events_that_are_not_objects()
    {
        $this->setExpectedException('Rawkode\Eidetic\EventStore\InvalidEventException');

        $this->eventStore->store('uuid-1', $this->invalidEvents);
    }

    /**
     * @test
     */
    public function it_can_rollback_a_transaction_after_an_error()
    {
        $this->eventStore->store('uuid-1', $this->validEvents);

        $this->setExpectedException('Rawkode\Eidetic\EventStore\InvalidEventException');

        $this->eventStore->store('uuid-1', $this->invalidEvents);

        $this->assertEquals($this->validEvents, $this->eventStore->retrieve('uuid-1'));
    }

    /**
     * @test
     */
    public function it_can_publish_events_to_subscribers()
    {
        $event = new \stdClass;

        // Create Mock
        $subscriber = $this->getMockBuilder('Rawkode\Eidetic\EventStore\EventSubscriber')
            ->setMethods(array('handle'))
            ->getMock();

        $subscriber->expects($this->once())
            ->method('handle')
            ->with(EventStore::EVENT_STORED, $event);

        $this->eventStore->registerEventSubscriber($subscriber);

        $this->eventStore->store('uuid-1', [ $event ]);
    }
}
