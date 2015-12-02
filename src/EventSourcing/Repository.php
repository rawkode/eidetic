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
    public static function createForWrites($class, EventStore $eventStore)
    {
        return new self($class, $eventStore);
    }

    /**
     * @param $key
     * @return mixed
     */
    public function load($key)
    {
        $events = $this->eventStore->retrieve($key);

        return call_user_func(array($this->entityClass, 'initialise'), $events);
    }

    /**
     * @param EventSourcedEntity $eventSourcedEntity
     * @throws IncorrectEntityClassException
     */
    public function save(EventSourcedEntity $eventSourcedEntity)
    {
        $this->eventStore->store($eventSourcedEntity->identifier(), $eventSourcedEntity->stagedEvents());
    }
}
