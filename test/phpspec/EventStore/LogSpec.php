<?php

namespace phpspec\Rawkode\Eidetic\EventStore;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Rawkode\Eidetic\SharedKernel\DomainEventInterface;

/**
 * Class LogSpec
 * @package phpspec\Rawkode\NineteenEightyFour\EventStore
 */
class LogSpec extends ObjectBehavior
{
    function let(DomainEventInterface $domainEvent)
    {
        $this->beConstructedWith("identifier", $domainEvent);
    }

    function it_is_created_with_the_current_time_in_utc()
    {
        $now = new \DateTime("now", new \DateTimeZone("UTC"));

        $this->recordedAt()->getTimezone()->getName()->shouldBe("UTC");
        $this->recordedAt()->diff($now)->format("%i minutes")->shouldBe("0 minutes");
    }
}
