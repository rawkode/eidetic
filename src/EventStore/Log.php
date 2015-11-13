<?php

namespace Rawkode\Eidetic\EventStore;

use Rawkode\Eidetic\SharedKernel\DomainEventInterface;

/**
 * Class Log.
 */
final class Log
{
    /**
 * @var  int|null
*/
    protected $serialNumber = null;

    /**
 * @var  string
*/
    protected $aggregateIdentifier;

    /**
 * @var  \DateTime
*/
    protected $recordedAt;

    /**
 * @var  string
*/
    protected $domainEventClass;

    /**
 * @var DomainEventInterface
*/
    protected $domainEvent;

    /**
     * @param string               $aggregateIdentifier
     * @param DomainEventInterface $domainEvent
     *
     * @throws \Exception
     */
    public function __construct($aggregateIdentifier, DomainEventInterface $domainEvent)
    {
        $this->aggregateIdentifier = $aggregateIdentifier;
        $this->recordedAt = new \DateTime('now', new \DateTimeZone('UTC'));

        $this->domainEventClass = strtr(get_class($domainEvent), '\\', '.');
        $this->domainEvent = $domainEvent;
    }

    /**
     * @return string
     */
    public function aggregateIdentifier()
    {
        return $this->aggregateIdentifier;
    }

    /**
     * @return int
     */
    public function serialNumber()
    {
        return $this->serialNumber;
    }

    /**
     * @return \DateTime
     */
    public function recordedAt()
    {
        return $this->recordedAt;
    }
}
