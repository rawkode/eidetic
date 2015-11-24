<?php

namespace Rawkode\Eidetic\EventStore;

use Rawkode\Eidetic\EventSourcing\EventSourcedEntity;

/**
 *
 */
final class EventPublishingEventStore implements EventStore
{
    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @var EventPublisher
     */
    private $eventPublisher;

    /**
     * @param EventStore     $eventStore
     * @param EventPublisher $eventPublisher
     */
    public function __construct(EventStore $eventStore, EventPublisher $eventPublisher)
    {
        $this->eventStore = $eventStore;
        $this->eventPublisher = $eventPublisher;
    }

    /**
     * @param EventSourcedEntity $eventSourcedEntity
     */
    public function save(EventSourcedEntity $eventSourcedEntity)
    {
        $stagedEvents = $eventSourcedEntity->stagedEvents();

        $this->eventStore->store($eventSourcedEntity->identifier(), $eventSourcedEntity->stagedEvents());

        $this->publish($stagedEvents);
    }

    /**
     * @param string $identifier
     *
     * @return array
     */
    public function fetchEntityEvents($identifier)
    {
        return $this->eventStore->retrieve($identifier);
    }

    /**
     * @param array $events
     */
    private function publish(array $events)
    {
        foreach ($events as $event) {
            $this->eventPublisher->publish($event);
        }
    }
}
