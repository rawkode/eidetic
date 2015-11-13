<?php

namespace Rawkode\Eidetic\EventStore\InMemory;

use Rawkode\Eidetic\EventStore\AggregateDoesNotExistException;
use Rawkode\Eidetic\EventStore\SerialNumberIntegrityException;
use Rawkode\Eidetic\EventStore\EventStoreInterface;
use Rawkode\Eidetic\SharedKernel\ArrayDomainEventStream;
use Rawkode\Eidetic\SharedKernel\DomainEventInterface;
use Rawkode\Eidetic\SharedKernel\DomainEventStreamInterface;

/**
 * Class InMemory
 *
 * @package Rawkode\NineteenEightyFour\EventStore
 *
 * @todo There must be a better name for "isEventSequential"
 * @todo There must be a better name for "SerialNumberIntegrityException"
 */
class EventStore implements EventStoreInterface
{
    /**
 * @var  array
*/
    private $transactionCopy;

    /**
 * @var  array
*/
    private $events;

    /**
     * InMemory constructor.
     */
    public function __construct()
    {
        $this->events = [];
    }

    /**
     * @param string $aggregateIdentifier
     * @return ArrayDomainEventStream
     * @throws AggregateDoesNotExistException
     */
    public function fetchDomainEventStream($aggregateIdentifier)
    {
        if (false === array_key_exists($aggregateIdentifier, $this->events)) {
            throw new AggregateDoesNotExistException();
        }

        return new ArrayDomainEventStream($this->events[$aggregateIdentifier]['events']);
    }

    /**
     * @param string                     $aggregateIdentifier
     * @param int                        $serialNumber
     * @param DomainEventStreamInterface $domainEvents
     * @return int
     * @throws SerialNumberIntegrityException
     */
    public function logDomainEventStream($aggregateIdentifier, $serialNumber, DomainEventStreamInterface $domainEvents)
    {
        foreach ($domainEvents as $domainEvent) {
            $serialNumber = $this->logDomainEvent($aggregateIdentifier, $serialNumber, $domainEvent);
        }

        return $this->events[$aggregateIdentifier]['serialNumber'];
    }

    /**
     * @param string               $aggregateIdentifier
     * @param int                  $serialNumber
     * @param DomainEventInterface $domainEvent
     * @return int
     * @throws SerialNumberIntegrityException
     */
    public function logDomainEvent($aggregateIdentifier, $serialNumber, DomainEventInterface $domainEvent)
    {
        // New aggregate
        if (false === array_key_exists($aggregateIdentifier, $this->events)) {
            $this->events[$aggregateIdentifier] = ['serialNumber' => 0, 'events' => []];
        }

        // Version modified not the same as persisted
        if ($this->events[$aggregateIdentifier]['serialNumber'] !== $serialNumber) {
            throw new SerialNumberIntegrityException();
        }

        $this->events[$aggregateIdentifier]['serialNumber'] = ++$serialNumber;
        array_push($this->events[$aggregateIdentifier]['events'], $domainEvent);

        return $serialNumber;
    }

    public function startTransaction()
    {
        $this->transactionCopy = $this->events;
    }

    public function abortTransaction()
    {
        $this->events = $this->transactionCopy;
    }

    public function completeTransaction()
    {
        $this->transactionCopy = [];
    }
}
