<?php

namespace Rawkode\Eidetic\SharedKernel;

interface DomainEventStreamInterface extends \IteratorAggregate
{
    public function count();
}
