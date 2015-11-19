<?php
namespace Rawkode\Eidetic\EventSourcing;

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

         $this->assertInstanceOf('Rawkode\Eidetic\EventSourcing\TestEvent', $stagedEvents[0]);
         $this->assertInstanceOf('Rawkode\Eidetic\EventSourcing\TestEventTwo', $stagedEvents[1]);
         $this->assertInstanceOf('Rawkode\Eidetic\EventSourcing\TestEvent', $stagedEvents[2]);

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
