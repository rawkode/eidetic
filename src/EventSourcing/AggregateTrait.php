<?php

namespace Rawkode\Eidetic\EventSourcing;

use Rawkode\Eidetic\EventStore\PersistenceTransactionMismatchException;
use Rawkode\Eidetic\SharedKernel\ArrayDomainEventStream;
use Rawkode\Eidetic\SharedKernel\DomainEventHandlerDoesNotExist;
use Rawkode\Eidetic\SharedKernel\DomainEventInterface;
use Rawkode\Eidetic\SharedKernel\DomainEventStreamInterface;
use Rawkode\Eidetic\SharedKernel\IdentifierIsNullException;

/**
 * Class AggregateTrait.
 */
trait AggregateTrait
{
    /**
 * @var  string
*/
    protected $identifier;

    /**
 * @var  int|null
*/
    protected $serialNumber = 0;

    /**
 * @var array
*/
    protected $stagedEvents = [];

    /**
     * @return string
     * @throws IdentifierIsNullException
     */
    public function identifier()
    {
        if (is_null($this->identifier)) {
            throw new IdentifierIsNullException();
        }

        return $this->identifier;
    }

    /**
     * @return int|null
     */
    public function serialNumber()
    {
        return $this->serialNumber;
    }

    /**
     * @param DomainEventStreamInterface $domainEventStream
     * @throws DomainEventHandlerDoesNotExist
     */
    public function initialise(DomainEventStreamInterface $domainEventStream)
    {
        /**
 * @var DomainEventInterface $domainEvent
*/
        foreach ($domainEventStream as $domainEvent) {
            $this->applyDomainEvent($domainEvent);
        }

        // Commit events and update serial number when initialising
        $this->commit(count($this->stagedEvents));
    }

    /**
     * @return ArrayDomainEventStream
     */
    public function stagedEvents()
    {
        return new ArrayDomainEventStream($this->stagedEvents);
    }

    /**
     * @param int $proposedSerialNumber
     * @throws PersistenceTransactionMismatchException
     */
    public function commit($proposedSerialNumber)
    {
        // Sync mismatch
        if ($this->serialNumber += count($this->stagedEvents) !== $proposedSerialNumber) {
            throw new PersistenceTransactionMismatchException();
        }

        $this->serialNumber += count($this->stagedEvents);
        $this->stagedEvents = [];
    }

    /**
     * @param DomainEventInterface $domainEvent
     */
    private function applyDomainEvent(DomainEventInterface $domainEvent)
    {
        $applyMethod = $this->findDomainEventHandler($domainEvent);

        array_push($this->stagedEvents, $domainEvent);

        $this->$applyMethod($domainEvent);
    }

    /**
     * @param DomainEventInterface $domainEvent
     * @return string
     * @throws DomainEventHandlerDoesNotExist
     */
    private function findDomainEventHandler(DomainEventInterface $domainEvent)
    {
        $class = get_class($domainEvent);
        $explode = explode('\\', $class);
        $applyMethod = 'apply' . end($explode);

        if (!method_exists($this, $applyMethod)) {
            throw new DomainEventHandlerDoesNotExist("Couldn't find handler: " . $applyMethod);
        }

        return $applyMethod;
    }
}
