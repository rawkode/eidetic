<?php

namespace phpspec\Rawkode\Eidetic\EventSourcing;

use Example\User;
use Example\Event;
use Example\UserCreatedWithUsername;
use PhpSpec\ObjectBehavior;
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

        $this->user = User::createWithUsername('Rawkode');

        $this->beConstructedThrough('createForWrites', [get_class($this->user), $this->eventStore]);
    }

    public function it_can_save_an_event_sourced_entity()
    {
        $this->eventStore
            ->store($this->user)
            ->shouldBeCalled();

        $this->save($this->user);
    }

    public function it_can_load_an_entity_by_identifier()
    {
        $this->eventStore
            ->entityClass($this->user->identifier())
            ->willReturn('Example\User');

        $this->eventStore
            ->retrieve($this->user->identifier())
            ->willReturn([new UserCreatedWithUserName('test')]);

        $this->load($this->user->identifier())->shouldBeAnInstanceOf('Example\User');
    }

    public function it_cannot_save_entity_of_incorrect_type()
    {
        $this->shouldThrow('Rawkode\Eidetic\EventSourcing\IncorrectEntityClassException')->during('save', [Event::createWithName('DockerGlasgow')]);
    }
}
