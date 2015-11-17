<?php

namespace Rawkode\Eidetic\CQRS;

interface ReadModelRepository
{
    public function index($object);
    public function fetch($identifier);
}
