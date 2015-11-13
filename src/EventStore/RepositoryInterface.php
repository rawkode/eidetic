<?php
namespace Rawkode\Eidetic\EventStore;

use Rawkode\Eidetic\EventSourcing\AggregateInterface;

interface RepositoryInterface
{
    /**
     * @param $identifier
     * @return AggregateInterface
     * @throws AggregateDoesNotExistException
     */
    public function load($identifier);

    /**
     * @param AggregateInterface $aggregateInterface
     * @return
     */
    public function save(AggregateInterface $aggregateInterface);
}
