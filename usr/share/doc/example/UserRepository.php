<?php

namespace Example;

use Rawkode\Eidetic\EventStore\EventStore;
use Rawkode\Eidetic\EventStore\InvalidEventException;

final class UserRepository
{
    /** @var EventStore */
    private $eventStore;

    public function __construct(EventStore $eventStore)
    {
        $this->eventStore = $eventStore;
    }

    public function load($identifier)
    {
        $events = $this->eventStore->retrieve($identifier);

        return User::initialise($events);
    }

    public function save(User $user)
    {
        $this->eventStore->store($user->identifier(), $user->stagedEvents());
    }
}
