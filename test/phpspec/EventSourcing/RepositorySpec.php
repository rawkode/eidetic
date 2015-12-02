<?php

namespace phpspec\Rawkode\Eidetic\EventSourcing;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Rawkode\Eidetic\EventSourcing\EventSourcedEntity;
use Rawkode\Eidetic\EventSourcing\EventSourcedEntityMixin;
use Rawkode\Eidetic\EventStore\EventStore;

class RepositorySpec extends ObjectBehavior
{
    /** @var EventStore */
    private $eventStore;

    /** @var User */
    private $user;

    public function let(EventStore $eventStore)
    {
        $this->eventStore = $eventStore;

        $this->user = User::createWithUsername("Rawkode");

        $this->beConstructedThrough('createForType', [get_class($this->user), $this->eventStore]);
    }

    public function it_can_save_an_event_sourced_entity()
    {
        $this->eventStore->store(
            $this->user->identifier(),
            $this->user->stagedEvents()
        )->shouldBeCalled();

        $this->save($this->user);
    }

    public function it_cannot_save_wrong_entity_type()
    {
        $this->shouldThrow('Rawkode\Eidetic\EventSourcing\IncorrectEntityClassException')->during('save', [new Test]);
    }

    public function it_can_load_an_entity()
    {
        $this->eventStore->getClassForKey(
            $this->user->identifier()
        )->willReturn('phpspec\Rawkode\Eidetic\EventSourcing\User');

        $this->eventStore->retrieve(
            $this->user->identifier()
        )->willReturn([
            new UserCreatedWithUserName('test')
        ]);

        $this->load($this->user->identifier())->shouldBeAnInstanceOf('phpspec\Rawkode\Eidetic\EventSourcing\User');
    }
}

class User implements EventSourcedEntity
{
    use EventSourcedEntityMixin;

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
}

class UserCreatedWithUserName
{
    private $username;

    public function __construct($username)
    {
        $this->identifier = uniqid('user-');
        $this->username = $username;
    }

    public function username()
    {
        return $this->username;
    }
}

class Test extends User
{

}
