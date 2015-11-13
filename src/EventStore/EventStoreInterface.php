<?php

namespace Rawkode\Eidetic\EventStore;

use Rawkode\Eidetic\SharedKernel\DomainEventInterface;
use Rawkode\Eidetic\SharedKernel\DomainEventStreamInterface;

interface EventStoreInterface
{
    /**
     * @param $aggregateIdentifier
     * @param $serialNumber
     * @param DomainEventStreamInterface $domainEvents
     * @return int
     * @throws SerialNumberIntegrityException
     */
    public function logDomainEventStream($aggregateIdentifier, $serialNumber, DomainEventStreamInterface $domainEvents);

    /**
     * @param $aggregateIdentifier
     * @param int                  $serialNumber
     * @param DomainEventInterface $domainEvent
     * @return
     */
    public function logDomainEvent($aggregateIdentifier, $serialNumber, DomainEventInterface $domainEvent);

    /**
     * @param string $aggregateIdentifier
     *
     * @return DomainEventStreamInterface
     *
     * @throws AggregateDoesNotExistException
     */
    public function fetchDomainEventStream($aggregateIdentifier);

    public function startTransaction();

    public function abortTransaction();

    public function completeTransaction();
}
