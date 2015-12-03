<?php

namespace Rawkode\Eidetic\EventSourcing;

use Rawkode\Eidetic\EventStore\EventStore;

/**
 * Class Repository.
 */
final class Repository
{
    /** @var $entityClass */
    private $entityClass;

    /** @var EventStore $eventStore */
    private $eventStore;

    /**
     * @param string     $class
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
     *
     * @return Repository
     */
    public static function createForWrites($class, EventStore $eventStore)
    {
        return new self($class, $eventStore);
    }

    /**
     * @param string $entityIdentifier
     *
     * @return mixed
     */
    public function load($entityIdentifier)
    {
        $events = $this->eventStore->retrieve($entityIdentifier);

        return call_user_func(array($this->entityClass, 'initialise'), $events);
    }

    /**
     * @param EventSourcedEntity $eventSourcedEntity
     *
     * @throws IncorrectEntityClassException
     */
    public function save(EventSourcedEntity $eventSourcedEntity)
    {
        $this->eventStore->store($eventSourcedEntity);
    }
}
