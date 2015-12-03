<?php

namespace Rawkode\Eidetic\EventStore;

interface Serializer
{
    public function serialize($object);
    public function unserialize($object);
}
