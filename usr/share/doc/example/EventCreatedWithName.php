<?php

namespace Example;

final class EventCreatedWithName
{
    /** @var string */
    private $name;

    /**
     * @param string $username
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function name()
    {
        return $this->name;
    }
}
