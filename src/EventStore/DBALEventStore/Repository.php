<?php

namespace Rawkode\Eidetic\EventStore\DBALEventStore;

use Rawkode\Eidetic\EventSourcing\EventSourcedEntity;
use Rawkode\Eidetic\EventStore\EventStore;

/**
 * Class Repository
 * @package Rawkode\Eidetic\EventStore\DBALEventStore
 */
final class Repository
{
    /** @var EventSourcedEntity $entityClass */
    private $entityClass;

    /** @var EventStore $eventStore */
    private $eventStore;

    /**
     * @param EventSourcedEntity $class
     * @param EventStore $eventStore
     */
    private function __construct(EventSourcedEntity $class, EventStore $eventStore)
    {
        $this->entityClass = $class;
        $this->eventStore = $eventStore;
    }

    /**
     * @param $class
     * @param EventStore $eventStore
     * @return Repository
     */
    public static function createForType($class, EventStore $eventStore)
    {
        $entity = $class::getClass();
        return new self($entity, $eventStore);
    }

    /**
     * @param $identifier
     * @return mixed
     */
    public function load($identifier)
    {
        $entity = $this->entityClass;
        $this->enforceTypeConstraint($entity);
        $events = $this->eventStore->retrieve($identifier);

        return $entity::initialise($events);
    }

    /**
     * @param EventSourcedEntity $class
     * @throws IncorrectEntityException
     */
    public function save(EventSourcedEntity $class)
    {
        $this->enforceTypeConstraint($class);
        $this->eventStore->store($class->identifier(), $class->stagedEvents());
    }

    /**
     * @param EventSourcedEntity $entity
     * @throws IncorrectEntityException
     */
    private function enforceTypeConstraint(EventSourcedEntity $entity)
    {
        if (get_class($entity) !== get_class($this->entityClass)) {
            throw new IncorrectEntityException(get_class($entity) . " is not the same as " . get_class($this->entityClass));
        }
    }
}
