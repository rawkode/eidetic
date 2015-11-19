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
    const VALID_VERSION = 1;
    const INVALID_VERSION = 2;

    private $dbalConnection;
    private $tableName;
    private $queryBuilder;
    private $statement;

    private $eventSourcedEntityIdentifier = 'my-identifier-seed';
    private $eventSourcedEntity;

    private $eventSourcedEntityInvalidEventIdentifier = 'my-identifier-invalid';
    private $eventSourcedEntityInvalidEvent;

    private $storedEvents;

    public function let(Connection $dbalConnection, EventSourcedEntity $eventSourcedEntity, EventSourcedEntity $eventSourcedEntityInvalidEvent, QueryBuilder $queryBuilder, Statement $statement)
    {
        $this->dbalConnection = $dbalConnection;
        $this->tableName = 'events';

        $this->queryBuilder = $queryBuilder;
        $this->statement = $statement;

        $this->eventSourcedEntity = $eventSourcedEntity;

        $this->eventSourcedEntity->identifier()->willReturn($this->eventSourcedEntityIdentifier);
        $this->eventSourcedEntity->version()->willReturn(self::VALID_VERSION);
        $this->eventSourcedEntity->stagedEvents()->willReturn([new \stdClass(), new \stdClass()]);

        $this->eventSourcedEntityInvalidEvent = $eventSourcedEntityInvalidEvent;

        $this->eventSourcedEntityInvalidEvent->identifier()->willReturn($this->eventSourcedEntityInvalidEventIdentifier);
        $this->eventSourcedEntityInvalidEvent->version()->willReturn(self::VALID_VERSION);
        $this->eventSourcedEntityInvalidEvent->stagedEvents()->willReturn([0]);

        $this->storedEvents = [
            [
                'event' => base64_encode(serialize(new \stdClass())),
            ],
        ];

        $this->beConstructedWith($dbalConnection, $this->tableName);
    }

    private function prepare_for_version_check($entityIdentifier, $version)
    {
        $this->dbalConnection->createQueryBuilder()->willReturn($this->queryBuilder);
        $this->dbalConnection->createQueryBuilder()->shouldBeCalled();

        // Test shouldn't fail if we modify what to pull back, but we need event
        $this->queryBuilder->select('COUNT(*)')->shouldBeCalled();

        $this->queryBuilder->from($this->tableName)->shouldBeCalled();
        $this->queryBuilder->where('entity_identifier', '=', ':entity_identifier')->shouldBeCalled();
        $this->queryBuilder->orderBy('recorded_at', 'DESC')->shouldBeCalled();
        $this->queryBuilder->setParameter('entity_identifier', $entityIdentifier)->shouldBeCalled();
        $this->queryBuilder->setMaxResults(1)->shouldBeCalled();

        $this->queryBuilder->execute()->shouldBeCalled();

        $this->statement->rowCount()->willReturn(1);
        $this->statement->fetchColumn(0)->willReturn($version);

        $this->queryBuilder->execute()->willReturn($this->statement);
    }

    public function it_implements_event_store()
    {
        $this->shouldHaveType('Rawkode\Eidetic\EventSourcing\EventStore\EventStore');
    }

    public function it_uses_transactions_on_save()
    {
        $this->dbalConnection->beginTransaction()->shouldBeCalled();
        $this->dbalConnection->commit()->shouldBeCalled();

        $this->prepare_for_version_check($this->eventSourcedEntityIdentifier, self::VALID_VERSION);

        $this->save($this->eventSourcedEntity);
    }

    public function it_can_rollback_the_transaction_when_a_save_fails()
    {
        $this->dbalConnection->beginTransaction()->shouldBeCalled();
        $this->dbalConnection->rollBack()->shouldBeCalled();

        $this->prepare_for_version_check($this->eventSourcedEntityInvalidEventIdentifier, self::VALID_VERSION);

        $this->shouldThrow('Rawkode\Eidetic\EventSourcing\InvalidEventException')->during('save', [$this->eventSourcedEntityInvalidEvent]);
    }

    public function it_verifies_the_entity_version_on_save()
    {
        $this->prepare_for_version_check($this->eventSourcedEntityIdentifier, self::INVALID_VERSION);

        $this->shouldThrow('Rawkode\Eidetic\EventSourcing\EventStore\VersionMismatchException')->during('save', [$this->eventSourcedEntity]);
    }

    public function it_can_save_an_entities_staged_events()
    {
        $this->prepare_for_version_check($this->eventSourcedEntityInvalidEventIdentifier, self::VALID_VERSION);

        $this->shouldNotThrow('Rawkode\Eidetic\EventSourcing\InvalidEventException')->during('save', [$this->eventSourcedEntityInvalidEvent]);
    }

    public function it_can_fetch_an_entities_events()
    {
        $this->dbalConnection->createQueryBuilder()->willReturn($this->queryBuilder);
        $this->dbalConnection->createQueryBuilder()->shouldBeCalled();

        // Test shouldn't fail if we modify what to pull back, but we need event
        $this->queryBuilder->select(Argument::containingString('event'))->shouldBeCalled();

        $this->queryBuilder->from($this->tableName)->shouldBeCalled();
        $this->queryBuilder->where('entity_identifier', '=', ':entity_identifier')->shouldBeCalled();
        $this->queryBuilder->orderBy('recorded_at', 'ASC')->shouldBeCalled();
        $this->queryBuilder->setParameter('entity_identifier', $this->eventSourcedEntityIdentifier)->shouldBeCalled();

        $this->queryBuilder->execute()->shouldBeCalled();

        $this->statement->fetchAll()->willReturn($this->storedEvents);

        $this->queryBuilder->execute()->willReturn($this->statement);

        $this->fetchEntityEvents($this->eventSourcedEntityIdentifier)->shouldBeLike([new \stdClass()]);
    }
}
