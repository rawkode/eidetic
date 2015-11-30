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
    /** @var $entityClass */
    private $entityClass;

    /** @var EventStore $eventStore */
    private $eventStore;

    /**
     * @param EventSourcedEntity $class
     * @param EventStore $eventStore
     */
    private function __construct($class, EventStore $eventStore)
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
        return new self($class, $eventStore);
    }

    /**
     * @param $key
     * @return mixed
     */
    public function load($key)
    {
        $class = $this->eventStore->getClassForKey($key);

        $this->enforceTypeConstraint($class);

        $events = $this->eventStore->retrieve($key);

        return $class::initialise($events);
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
     * @param string $class
     * @throws IncorrectEntityException
     */
    private function enforceTypeConstraint($class)
    {
        if (get_class($class) !== get_class($this->entityClass)) {
            throw new IncorrectEntityException(get_class($class) . " is not the same as " . get_class($this->entityClass));
        }
    }
}
