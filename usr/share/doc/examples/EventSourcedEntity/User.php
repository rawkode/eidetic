<?php

final class User implements Rawkode\Eidetic\EventSourcing\EventSourcedEntity
{
    use Rawkode\Eidetic\EventSourcing\EventSourcedEntityMixin;

    /** @var string */
    private $username;

    /**
     * Usually best to make this private and force construction through statics.
     */
    private function __construct()
    {
    }

    /**
     * @param string $username
     *
     * @return User
     */
    public static function createWithUsername($username)
    {
        $user = new self();
        $user->applyEvent(new UserCreatedWithUsername($username));

        return $user;
    }

    /**
     * @param UserCreatedWithUsername $userCreatedWithUsername
     */
    private function applyUserCreatedWithUsername(UserCreatedWithUsername $userCreatedWithUsername)
    {
        $this->username = $userCreatedWithUsername->username();
    }

    /**
     * Get this users username.
     *
     * @return string
     */
    public function username()
    {
        return $this->username;
    }
}
