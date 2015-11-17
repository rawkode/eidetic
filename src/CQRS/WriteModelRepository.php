<?php

namespace Rawkode\Eidetic\CQRS;

interface WriteModelRepository
{
    public function save($object);
    public function load($identifier);
}
