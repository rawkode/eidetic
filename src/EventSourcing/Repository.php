<?php

namespace Rawkode\Eidetic\EventSourcing;

use Rawkode\Eidetic\CQRS\WriteModelRepository;
use Rawkode\Eidetic\EventStore\EventStore;
use Rawkode\Eidetic\EventStore\VersionMismatchException;

/**
 * Class Repository.
 */
final class Repository implements WriteModelRepository
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
        $this->enforceTypeConstraint($this->eventStore->entityClass($entityIdentifier));

        $events = $this->eventStore->retrieve($entityIdentifier);

        return call_user_func(array($this->entityClass, 'initialise'), $events);
    }

    /**
     * @param EventSourcedEntity $eventSourcedEntity
     *
     * @throws \Exception
     */
    public function save(EventSourcedEntity $eventSourcedEntity)
    {
        $this->enforceTypeConstraint(get_class($eventSourcedEntity));

        $this->enforceVersionMismatchConstraint($eventSourcedEntity);

        $this->eventStore->store($eventSourcedEntity);
    }

    /**
     * @param string $class
     *
     * @throws IncorrectEntityClassException
     */
    private function enforceTypeConstraint($class)
    {
        if ($this->entityClass !== $class) {
            throw new IncorrectEntityClassException();
        }
    }

    /**
     * @param EventSourcedEntity $eventSourcedEntity
     *
     * @throws VersionMismatchException
     */
    private function enforceVersionMismatchConstraint(EventSourcedEntity $eventSourcedEntity)
    {
        /** @var EventSourcedEntity $databaseVersion */
        $databaseVersion = $this->load($eventSourcedEntity->identifier());

        if ($databaseVersion->version() !== $eventSourcedEntity->version()) {
            throw new VersionMismatchException('Local entity is at version '
                .$eventSourcedEntity->version().' and database is at '.$databaseVersion->version());
        }
    }
}
