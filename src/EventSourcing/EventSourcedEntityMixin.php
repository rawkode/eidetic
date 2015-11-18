<?php

namespace Rawkode\Eidetic\EventSourcing;

use Rawkode\Eidetic\EventHandlerDoesNotExist;

trait EventSourcedEntityMixin
{
    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var integer
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
    public function initialise(array $eventStream)
    {
        foreach ($eventStream as $event) {
            $this->applyEvent($event);
        }

        $this->commit();
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
     */
    private function applyEvent($event)
    {
        $applyMethod = $this->findEventHandler($event);

        array_push($this->stagedEvents, $event);

        $this->$applyMethod($event);
    }

    /**
     * @param object $event
     * @return string
     * @throws EventHandlerDoesNotExist
     */
    private function findEventHandler($event)
    {
        $class = get_class($event);
        $explode = explode('\\', $class);
        $applyMethod = 'apply' . end($explode);

        if (!method_exists($this, $applyMethod)) {
            throw new EventHandlerDoesNotExistException("Couldn't find event handler for '{$applyMethod}'");
        }

        return $applyMethod;
    }
}
