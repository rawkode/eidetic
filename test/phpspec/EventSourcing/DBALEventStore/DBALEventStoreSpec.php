<?php

namespace phpspec\Rawkode\Eidetic\EventSourcing\DBALEventStore;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;
use Doctrine\DBAL\Query\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Rawkode\Eidetic\EventSourcing\EventSourcedEntity;
use Prophecy\Argument;

final class DBALEventStoreSpec extends ObjectBehavior
{
    private $dbalConnection;
    private $tableName;
    private $eventSourcedEntity;
    private $eventSourcedEntityInvalidEvent;
    private $storedEvents;

    public function let(Connection $dbalConnection, EventSourcedEntity $eventSourcedEntity, EventSourcedEntity $eventSourcedEntityInvalidEvent)
    {
        $this->dbalConnection = $dbalConnection;
        $this->tableName = 'events';

        $this->eventSourcedEntity = $eventSourcedEntity;

        $this->eventSourcedEntity->identifier()->willReturn('my-identifier-seed');
        $this->eventSourcedEntity->version()->willReturn(0);
        $this->eventSourcedEntity->stagedEvents()->willReturn([new \stdClass(), new \stdClass()]);

        $this->eventSourcedEntityInvalidEvent = $eventSourcedEntityInvalidEvent;

        $this->eventSourcedEntityInvalidEvent->identifier()->willReturn('my-identifier-seed');
        $this->eventSourcedEntityInvalidEvent->version()->willReturn(0);
        $this->eventSourcedEntityInvalidEvent->stagedEvents()->willReturn([0]);

        $this->storedEvents = [
            [
                'event' => base64_encode(serialize(new \stdClass())),
            ],
        ];

        $this->beConstructedWith($dbalConnection, $this->tableName);
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

    public function it_can_fetch_an_entities_events(QueryBuilder $queryBuilder, Statement $statement)
    {
        $this->dbalConnection->createQueryBuilder()->willReturn($queryBuilder);
        $this->dbalConnection->createQueryBuilder()->shouldBeCalled();

        // Test shouldn't fail if we modify what to pull back, but we need event
        $queryBuilder->select(Argument::containingString('event'))->shouldBeCalled();

        $queryBuilder->from($this->tableName)->shouldBeCalled();
        $queryBuilder->where('entity_identifier', '=', ':entity_identifier')->shouldBeCalled();
        $queryBuilder->orderBy('recorded_at', 'ASC')->shouldBeCalled();
        $queryBuilder->setParameter('entity_identifier', 'identifier')->shouldBeCalled();

        $queryBuilder->execute()->shouldBeCalled();

        $statement->fetchAll()->willReturn($this->storedEvents);

        $queryBuilder->execute()->willReturn($statement);

        $this->fetchEntityEvents('identifier')->shouldBeLike([new \stdClass()]);
    }
}
