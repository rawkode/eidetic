<?php

namespace Example;

use Rawkode\Eidetic\EventSourcing\EventSourcedEntity;
use Rawkode\Eidetic\EventSourcing\EventSourcedEntityMixin;

final class Event implements EventSourcedEntity
{
    use EventSourcedEntityMixin;

    /** @var string */
    private $name;

    /**
     * Usually best to make this private and force construction through statics.
     */
    private function __construct()
    {
        $this->identifier = uniqid('event-');
    }

    /**
     * @param string $username
     *
     * @return User
     */
    public static function createWithName($name)
    {
        $event = new static();
        $event->applyEvent(new EventCreatedWithName($name));

        return $event;
    }

    /**
     * @param UserCreatedWithUsername $userCreatedWithUsername
     */
    private function applyEventCreatedWithName(EventCreatedWithName $eventCreatedWithName)
    {
        $this->name = $eventCreatedWithName->name();
    }

    /**
     * @return string
     */
    public function identifier()
    {
        return $this->identifier;
    }

    /**
     * @return string
     */
    public function name()
    {
        return $this->name;
    }
}
