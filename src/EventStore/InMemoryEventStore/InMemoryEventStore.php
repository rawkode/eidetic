<?php

namespace Rawkode\Eidetic\EventStore\InMemoryEventStore;

use Rawkode\Eidetic\EventStore\InvalidEventException;
use Rawkode\Eidetic\EventStore\EventStore;
use Rawkode\Eidetic\EventStore\NoEventsFoundForKeyException;
use Rawkode\Eidetic\EventStore\TransactionAlreadyInProgressException;

final class InMemoryEventStore implements EventStore
{
    /**
     * @var integer
     */
    private $serialNumber = 0;

    /**
     * @var array
     */
    private $events = [];

    /**
     * @var bool
     */
    private $transactionInProgress = false;

    /**
     * @var array
     */
    private $transactionBackup = [];

    /**
     * @param string $identifier
     *
     * @throws NoEventsForIdentifierException
     *
     * @return array
     */
    public function fetchEvents($key)
    {
        $eventLogs = $this->getEventLogs($key);

        return array_map(function ($eventLog) {
            return $eventLog['event'];
        }, $eventLogs);
    }

    /**
     * @param  string $key
     * @return array
     */
    public function fetchEventLogs($key)
    {
        return $this->getEventLogs($key);
    }

    /**
     * @param  string $key
     * @return array
     */
    private function getEventLogs($key)
    {
        if (false === array_key_exists($key, $this->events)) {
            throw new NoEventsFoundForKeyException();
        }

        return $this->events[$key];
    }

    /**
     * @param string $key
     * @param array  $events
     *
     * @throws OutOfSyncException
     * @throws InvalidEventException
     */
    public function saveEvents($key, array $events)
    {
        try {
            $this->startTransaction();

            foreach ($events as $event) {
                $this->persistEvent($key, $event);
            }
        } catch (TransactionAlreadyInProgressException $transactionAlreadyInProgressExeception) {
            throw $transactionAlreadyInProgressExeception;
        } catch (InvalidEventException $invalidEventException) {
            $this->abortTransaction();

            throw $invalidEventException;
        }

        $this->completeTransaction();
    }

    /**
     */
    private function startTransaction()
    {
        if (true === $this->transactionInProgress) {
            throw new TransactionAlreadyInProgressException();
        }

        $this->transactionBackup = $this->events;
        $this->transactionInProgress = true;
    }

    /**
     */
    private function abortTransaction()
    {
        $this->events = $this->transactionBackup;
        $this->transactionInProgress = false;
    }

    /**
     */
    private function completeTransaction()
    {
        $this->transactionBackup = [];
        $this->transactionInProgress = false;
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
    }

    /**
     * @param object $event
     *
     * @throws InvalidArgumentException
     */
    private function verifyEventIsAClass($event)
    {
        try {
            if (false === get_class($event)) {
                throw new InvalidEventException();
            }
        } catch (\Exception $exception) {
            throw new InvalidEventException();
        }
    }
}
