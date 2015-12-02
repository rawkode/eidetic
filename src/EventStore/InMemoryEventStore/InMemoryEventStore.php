<?php

namespace Rawkode\Eidetic\EventStore\InMemoryEventStore;

use Rawkode\Eidetic\EventStore\InvalidEventException;
use Rawkode\Eidetic\EventStore\EventStore;
use Rawkode\Eidetic\EventStore\EventPublisherMixin;
use Rawkode\Eidetic\EventStore\NoEventsFoundForKeyException;
use Rawkode\Eidetic\EventStore\Subscriber;
use Rawkode\Eidetic\EventStore\VerifyEventIsAClassTrait;
use Rawkode\Eidetic\EventStore\InMemoryEventStore\TransactionAlreadyInProgressException;
use Rawkode\Eidetic\EventStore\EventPublisher;
use Doctrine\Common\EventSubscriber;

final class InMemoryEventStore implements EventStore
{
    use EventPublisherMixin;
    use VerifyEventIsAClassTrait;

    /**
     * @var int
     */
    private $serialNumber = 0;

    /**
     * @var array
     */
    private $events = [];

    /**
     * @var array
     */
    private $transactionBackup = [];

    /**
     * @var array
     */
    private $stagedEvents = [];

    /**
     * @param string $key
     *
     * @return array
     */
    public function retrieve($key)
    {
        $eventLogs = $this->eventLogs($key);

        return array_map(function ($eventLog) {
            return $eventLog['event'];
        }, $eventLogs);
    }

    /**
     * @param string $key
     *
     * @return array
     */
    public function retrieveLogs($key)
    {
        return $this->eventLogs($key);
    }

    /**
     * @param string $key
     *
     * @throws NoEventsFoundForKeyException
     *
     * @return array
     */
    private function eventLogs($key)
    {
        $this->verifyEventExistsForKey($key);

        return $this->events[$key];
    }

    /**
     * @param string $key
     * @param array $events
     *
     * @throws TransactionAlreadyInProgressException
     * @throws InvalidEventException
     */
    public function store($key, array $events)
    {
        try {
            $this->startTransaction();

            foreach ($events as $event) {
                $this->persistEvent($key, $event);
            }
        } catch (InvalidEventException $invalidEventException) {
            $this->abortTransaction();

            throw $invalidEventException;
        }

        $this->completeTransaction();
    }

    /**
     * @throws TransactionAlreadyInProgressException
     */
    private function startTransaction()
    {
        $this->transactionBackup = $this->events;
        $this->stagedEvents = [];
    }

    /**
     */
    private function abortTransaction()
    {
        $this->events = $this->transactionBackup;
        $this->stagedEvents = [];
    }

    /**
     */
    private function completeTransaction()
    {
        $this->transactionBackup = [];

        foreach ($this->stagedEvents as $event) {
            $this->publish(self::EVENT_STORED, $event);
        }

        $this->stagedEvents = [];
    }

    /**
     * @param string $key
     * @param  $event
     *
     * @throws InvalidEventException
     */
    private function persistEvent($key, $event)
    {
        $this->verifyEventIsAClass($event);

        $this->events[$key][] = [
            'serial_number' => ++$this->serialNumber,
            'key' => $key,
            'recorded_at' => new \DateTime('now', new \DateTimeZone('UTC')),
            'event_class' => get_class($event),
            'event' => $event,
        ];

        array_push($this->stagedEvents, $event);
    }

    /**
     * @param $key
     * @throws NoEventsFoundForKeyException
     */
    private function verifyEventExistsForKey($key)
    {
        if (false === array_key_exists($key, $this->events)) {
            throw new NoEventsFoundForKeyException();
        }
    }
}
