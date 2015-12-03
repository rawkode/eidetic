<?php

namespace Rawkode\Eidetic\Tests;

use Example\User;
use Example\UserCreatedWithUsername;
use Rawkode\Eidetic\EventStore\EventStore;

abstract class EventStoreTest extends \PHPUnit_Framework_TestCase
{
    /** @var EventStore */
    protected $eventStore;

    /** @var User */
    protected $user;

    /** @var array */
    protected $validEvents;

    /** @var array */
    protected $invalidEvents;

    /**
     */
    public function setUp()
    {
        $this->user = User::createWithUsername('Rawkode');

        $this->validEvents = [
            new UserCreatedWithUsername('Rawkode'),
        ];

        $this->invalidEvents = [
            new \stdClass(),
        ];
    }

    /**
     * @param object &$object
     * @param string $methodName
     * @param array  $parameters
     *
     * @return mixed Method return.
     */
    public function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * @param object &$object
     * @param string $propertyName
     */
    public function modifyProperty(&$object, $propertyName, $newValue)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($object, $newValue);
    }

    /** @test */
    public function it_throws_an_exception_when_loading_an_invalid_key()
    {
        $this->setExpectedException('Rawkode\Eidetic\EventStore\NoEventsFoundForKeyException');

        $this->eventStore->retrieve('this-identifier-will-not-exist');
    }

    /** @test */
    public function it_can_save_and_load_events_by_their_key()
    {
        $this->eventStore->store($this->user);

        $this->assertEquals($this->validEvents, $this->eventStore->retrieve($this->user->identifier()));
    }

    /** @test */
    public function it_saves_the_correct_event_log_meta_data()
    {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        $this->eventStore->store($this->user);

        $eventLog = $this->eventStore->retrieveLog($this->user->identifier());

        // Ensure that the time inserted into the event store is within a minute of UTC now
        $this->assertEquals('0', $now->diff($eventLog[0]['recorded_at'])->format('%i'));

        // Ensure event_class is saved correctly
        $this->assertEquals(UserCreatedWithUsername::class, $eventLog[0]['event_class']);
    }

    /** @test */
    public function it_can_publish_events_to_subscribers()
    {
        // Create Mock
        $subscriber = $this->getMockBuilder('Rawkode\Eidetic\EventStore\EventSubscriber')
            ->setMethods(array('handle'))
            ->getMock();

        $subscriber->expects($this->once())
            ->method('handle')
            ->with(EventStore::EVENT_STORED, $this->validEvents[0]);

        $this->eventStore->registerSubscriber($subscriber);

        $this->eventStore->store($this->user);
    }

    /** @test */
    public function it_loads_events_in_the_correct_order()
    {
        $this->user->drinkBeer();
        $this->eventStore->store($this->user);

        $eventLog = $this->eventStore->retrieveLog($this->user->identifier());

        $counter = 0;

        foreach ($eventLog as $eventLogEntry) {
            $this->assertEquals(++$counter, $eventLogEntry['serial_number']);
        }
    }

    /** @test */
    public function it_can_count_the_number_of_events_for_an_entity()
    {
        $this->assertEquals(0, $this->invokeMethod($this->eventStore, 'countEntityEvents', array($this->user->identifier())));

        $this->eventStore->store($this->user);

        $this->assertEquals(1, $this->invokeMethod($this->eventStore, 'countEntityEvents', array($this->user->identifier())));
    }

    /** @test */
    public function it_throws_exception_when_event_key_does_not_exist()
    {
        $this->setExpectedException('Rawkode\Eidetic\EventStore\NoEventsFoundForKeyException');
        $out = $this->invokeMethod($this->eventStore, 'entityClass', array($this->user->identifier()));
    }

    /** @test */
    public function it_can_find_entity_class()
    {
        $this->eventStore->store($this->user);
        $this->assertEquals('Example\User', $this->invokeMethod($this->eventStore, 'entityClass', array($this->user->identifier())));
    }

    /** @test */
    public function it_can_abort_transactions()
    {
        // We need one event inside the EventStore
        $this->eventStore->store($this->user);

        // Use reflection to break our model and force an abortTransaction
        $this->modifyProperty($this->user, 'stagedEvents', array_merge($this->user->stagedEvents(), ['Hello']));

        try {
            $this->eventStore->store($this->user);
        } catch (\Exception $exception) {
            // We're not testing the exception is thrown, but that the state is unchanged
        }

        // Ensure there's still only one event
        $this->assertEquals(1, $this->invokeMethod($this->eventStore, 'countEntityEvents', array($this->user->identifier())));
    }
}
