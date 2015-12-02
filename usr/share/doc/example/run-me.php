<?php

require_once '../../../../var/vendor/autoload.php';
require_once 'User.php';
require_once 'UserRepository.php';
require_once 'UserCreatedWithUsername.php';

use Example\User;
use Rawkode\Eidetic\EventStore\DBALEventStore\DBALEventStore;
use Rawkode\Eidetic\EventStore\DBALEventStore\Repository;

use Rawkode\Eidetic\EventStore\EventStore;
use Rawkode\Eidetic\EventStore\NoEventsFoundForKeyException;
use Rawkode\Eidetic\EventStore\Symfony2EventDispatcherSubscriber\Symfony2EventDispatcherSubscriber;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;

// Symfony2 Event Dispatcher
$symfony2EventDispatcher = new EventDispatcher();
$symfony2EventDispatcher->addListener(EventStore::EVENT_STORED, 'thisIsMyListener');

// Event Dispatcher Listener ... ish
function thisIsMyListener(Event $event)
{
    echo "Hello, I am the Symfony2 Event Dispatcher Listener!" . PHP_EOL;
    var_dump($event->event());
}

// Now create our integration class and pass in the dispatcher
$symfony2EventDispatcherSubscriber = new symfony2EventDispatcherSubscriber($symfony2EventDispatcher);

// We need an EventStore. We'll use the DBAL with in-memory sqlite
$eventStore = DBALEventStore::createWithOptions('events', [
    'driver' => 'pdo_sqlite',
    'memory' => true
]);

// Register :D
$eventStore->registerSubscriber($symfony2EventDispatcherSubscriber);

// Create the table we need
$eventStore->createTable();

// Initialise a repository with this event store
$userRepository = Repository::createForType('Example\User', $eventStore);

// Create a user
$user = User::createWithUsername("David");

// We can output the users username and there's no sign of an event
//  event anywhere! Nifty
echo "Hello, {$user->username()}!" . PHP_EOL;

// We can even save this user, still no mention of an event
$userRepository->save($user);


// Lets backup the identifier so that we can discard and reload
$userIdentifier = $user->identifier();
unset($user);

// Load the user from the EventStore, using our repository
$user = $userRepository->load($userIdentifier);

// Viola!
echo "Hello, {$user->username()}!" . PHP_EOL;
var_dump($user);


// What about identifiers that don't exist?
try {
    $userRepository->load('random');
} catch (NoEventsFoundForKeyException $noEventsFoundForKeyException) {
    echo "Sorry, can't find any events for this entity." . PHP_EOL;
    echo "You should probably put me in your repository and throw a more domain specific exception" . PHP_EOL;
}
