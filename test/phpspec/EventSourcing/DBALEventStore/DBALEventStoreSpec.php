<?php

namespace phpspec\Rawkode\Eidetic\EventSourcing\DBALEventStore;

use Doctrine\DBAL\Connection;
use PhpSpec\ObjectBehavior;
use Rawkode\Eidetic\EventSourcing\EventSourcedEntity;

final class DBALEventStoreSpec extends ObjectBehavior
{
    private $dbalConnection;
    private $eventSourcedEntity;
    private $eventSourcedEntityInvalidEvent;

    public function let(Connection $dbalConnection, EventSourcedEntity $eventSourcedEntity, EventSourcedEntity $eventSourcedEntityInvalidEvent)
    {
        $this->dbalConnection = $dbalConnection;

        $this->eventSourcedEntity = $eventSourcedEntity;

        $this->eventSourcedEntity->identifier()->willReturn('my-identifier-seed');
        $this->eventSourcedEntity->version()->willReturn(0);
        $this->eventSourcedEntity->stagedEvents()->willReturn([new \stdClass(), new \stdClass()]);

        $this->eventSourcedEntityInvalidEvent = $eventSourcedEntityInvalidEvent;

        $this->eventSourcedEntityInvalidEvent->identifier()->willReturn('my-identifier-seed');
        $this->eventSourcedEntityInvalidEvent->version()->willReturn(0);
        $this->eventSourcedEntityInvalidEvent->stagedEvents()->willReturn([0]);

        $this->beConstructedWith($dbalConnection);
    }

    public function it_implements_event_store()
    {
        $this->shouldHaveType('Rawkode\Eidetic\EventSourcing\EventStore\EventStore');
    }

    public function it_uses_transactions_on_save()
    {
        $this->dbalConnection->beginTransaction()->shouldBeCalled();
        $this->dbalConnection->commit()->shouldBeCalled();

        $this->save($this->eventSourcedEntity);
    }

    public function it_can_rollback_the_transaction_when_a_save_fails()
    {
        $this->dbalConnection->beginTransaction()->shouldBeCalled();
        $this->dbalConnection->rollBack()->shouldBeCalled();

        $this->shouldThrow('Rawkode\Eidetic\EventSourcing\InvalidEventException')->during('save', [$this->eventSourcedEntityInvalidEvent]);
    }
}
