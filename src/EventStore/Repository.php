<?php
namespace Rawkode\Eidetic\EventStore;

use Rawkode\Eidetic\EventSourcing\AggregateInterface;

class Repository implements RepositoryInterface
{
    /**
 * @var  EventStoreInterface
*/
    private $eventStore;

    /**
     * @param AggregateInterface $aggregate
     */
    public function save(AggregateInterface $aggregate)
    {
        $this->eventStore->logDomainEventStream($aggregate->identifier(), $aggregate->serialNumber(), $aggregate->stagedEvents());
    }

    /**
     * @param $identifier
     * @return AggregateInterface
     * @throws AggregateDoesNotExistException
     */
    public function load($identifier)
    {
    }
}
