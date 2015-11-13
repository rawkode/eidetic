<?php

namespace Rawkode\Eidetic\EventSourcing;

use Rawkode\Eidetic\SharedKernel\ArrayDomainEventStream;
use Rawkode\Eidetic\SharedKernel\DomainEventStreamInterface;

interface AggregateInterface
{
    /**
     * @return string
     */
    public function identifier();

    /**
     * @return int|null
     */
    public function serialNumber();

    /**
     * Initialise without staging the domain events
     *
     * @param DomainEventStreamInterface $domainEventStream
     */
    public function initialise(DomainEventStreamInterface $domainEventStream);

    /**
     * Return a list of staged domain events
     *
     * @return DomainEventStreamInterface
     */
    public function stagedEvents();

    /**
     * @param int $proposedSerialNumber
     * @return
     */
    public function commit($proposedSerialNumber);
}
