<?php

namespace Rawkode\Eidetic\SharedKernel;

use ArrayIterator;

final class ArrayDomainEventStream implements DomainEventStreamInterface
{
    /** @var array<DomainEventInterface> */
    private $events;

    /**
     * @param array<DomainEventInterface> $events
     */
    public function __construct(array $events)
    {
        $this->events = $events;
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->events);
    }

    public function count()
    {
        return count($this->events);
    }
}
