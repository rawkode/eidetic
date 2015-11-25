<?php

namespace Example;

use Rawkode\Eidetic\EventSourcing\EventSourcedEntity;
use Rawkode\Eidetic\EventSourcing\EventSourcedEntityMixin;

final class User implements EventSourcedEntity
{
    use EventSourcedEntityMixin;

    /** @var string */
    private $username;

    /**
     * Usually best to make this private and force construction through statics
     */
    private function __construct()
    {
        $this->identifier = uniqid('user-');
    }

    /**
     * @param  string $username
     * @return User
     */
    public static function createWithUsername($username)
    {
        $user = new self;
        $user->applyEvent(new UserCreatedWithUsername($username));

        return $user;
    }

    /**
     * @param  UserCreatedWithUsername $userCreatedWithUsername
     */
    private function applyUserCreatedWithUsername(UserCreatedWithUsername $userCreatedWithUsername)
    {
        $this->username = $userCreatedWithUsername->username();
    }

    /**
     * @return string
     */
    public function identifier()
    {
        return $this->identifier;
    }

    /**
     * @return string
     */
    public function username()
    {
        return $this->username;
    }
}
