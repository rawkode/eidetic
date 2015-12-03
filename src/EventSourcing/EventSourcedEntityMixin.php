<?php

namespace Rawkode\Eidetic\EventSourcing;

trait EventSourcedEntityMixin
{
    use VerifyEventIsAClassTrait;

    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var int
     */
    protected $version = 0;

    /**
     * @var array
     */
    protected $stagedEvents = [];

    /**
     * @return string
     */
    public function identifier()
    {
        return $this->identifier;
    }

    /**
     * @return int
     */
    public function version()
    {
        return $this->version;
    }

    /**
     * @param array $eventStream
     */
    public static function initialise(array $eventStream)
    {
        $entity = new static();

        foreach ($eventStream as $event) {
            $entity->applyEvent($event);
        }

        $entity->commit();

        return $entity;
    }

    /**
     * @return array
     */
    public function stagedEvents()
    {
        return $this->stagedEvents;
    }

    /**
     */
    public function commit()
    {
        $this->version += count($this->stagedEvents);
        $this->stagedEvents = [];
    }

    /**
     * @param object $event
     *
     * @throws InvalidEventException
     */
    private function applyEvent($event)
    {
        $this->verifyEventIsAClass($event);

        $applyMethod = $this->findEventHandler($event);

        array_push($this->stagedEvents, $event);

        $this->$applyMethod($event);
    }

    /**
     * @param object $event
     *
     * @throws EventHandlerDoesNotExist
     *
     * @return string
     */
    private function findEventHandler($event)
    {
        $class = get_class($event);
        $explode = explode('\\', $class);
        $applyMethod = 'apply'.end($explode);

        if (!method_exists($this, $applyMethod)) {
            throw new EventHandlerDoesNotExistException("Couldn't find event handler for '{$applyMethod}'");
        }

        return $applyMethod;
    }
}
