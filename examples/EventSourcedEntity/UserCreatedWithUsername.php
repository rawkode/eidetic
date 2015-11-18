<?php

final class UserCreatedWithUsername
{
    /** @var string */
    private $username;

    /**
     * @param string $username
     */
    public function __construct($username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function username()
    {
        return $this->username;
    }
}
