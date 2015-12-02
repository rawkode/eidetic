<?php

namespace Rawkode\Eidetic\EventSourcing;

use Rawkode\Eidetic\EventStore\EventStore;

/**
 * Class Repository
 * @package Rawkode\Eidetic\EventSourcing
 */
final class Repository
{
    /** @var $entityClass */
    private $entityClass;

    /** @var EventStore $eventStore */
    private $eventStore;

    /**
     * @param string $class
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
     * @param EventSourcedEntity $eventSourcedEntity
     * @throws IncorrectEntityClassException
     */
    public function save(EventSourcedEntity $eventSourcedEntity)
    {
        $this->enforceTypeConstraint(get_class($eventSourcedEntity));
        $this->eventStore->store($eventSourcedEntity->identifier(), $eventSourcedEntity->stagedEvents());
    }

    /**
     * @param $class
     * @throws IncorrectEntityClassException
     */
    private function enforceTypeConstraint($class)
    {
        if ($class !== $this->entityClass) {
            throw new IncorrectEntityClassException($class . ' is not same as ' . $this->entityClass);
        }
    }
}
