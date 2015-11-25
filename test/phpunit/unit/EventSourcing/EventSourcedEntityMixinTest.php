<?php
namespace Rawkode\Eidetic\Tests\Unit\EventSourcing;

use Rawkode\Eidetic\EventSourcing\EventSourcedEntityMixin;

class EventSourcedEntityMixinTest extends \PHPUnit_Framework_TestCase
{
    use EventSourcedEntityMixin;

    /**
     * @test
     */
    public function it_can_determine_method_name_for_event_class()
    {
        $eventHandlerMethodName = $this->findEventHandler(new TestEvent());

        $this->assertEquals($eventHandlerMethodName, 'applyTestEvent');
    }

    /**
     * @test
     */
     public function it_can_apply_an_event()
     {
         $this->assertCount(0, $this->stagedEvents());

         $this->applyEvent(new TestEvent());

         $stagedEvents = $this->stagedEvents();

         $this->assertCount(1, $stagedEvents);

         $this->applyEvent(new TestEventTwo());
         $this->applyEvent(new TestEvent());

         $stagedEvents = $this->stagedEvents();

         $this->assertCount(3, $stagedEvents);

         $this->assertInstanceOf('Rawkode\Eidetic\Tests\Unit\EventSourcing\TestEvent', $stagedEvents[0]);
         $this->assertInstanceOf('Rawkode\Eidetic\Tests\Unit\EventSourcing\TestEventTwo', $stagedEvents[1]);
         $this->assertInstanceOf('Rawkode\Eidetic\Tests\Unit\EventSourcing\TestEvent', $stagedEvents[2]);
    }

    /**
     * @test
     */
    public function it_can_commit_events()
    {
        $this->assertCount(0, $this->stagedEvents());
        $this->assertEquals(0, $this->version());

        $this->applyEvent(new TestEvent());
        $this->applyEvent(new TestEventTwo());
        $this->applyEvent(new TestEvent());

        $this->commit();

        $this->assertEquals(3, $this->version());
        $this->assertCount(0, $this->stagedEvents());
    }

    /**
     * @test
     */
    public function it_can_initialise_itself()
    {
        $entity = $this::initialise([
            new TestEvent(),
            new TestEventTwo()
        ]);

        $this->assertEquals(2, $entity->version());
        $this->assertCount(0, $entity->stagedEvents());
    }

    private function applyTestEvent()
    {

    }

    private function applyTestEventTwo()
    {

    }
}

final class TestEvent
{
}

final class TestEventTwo
{
}
