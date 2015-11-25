<?php

require_once '../../../../var/vendor/autoload.php';
require_once 'User.php';
require_once 'UserRepository.php';
require_once 'UserCreatedWithUsername.php';

use Rawkode\Eidetic\EventStore\DBALEventStore\DBALEventStore;
use Rawkode\Eidetic\EventStore\NoEventsFoundForKeyException;
use Example\User;
use Example\UserRepository;

// We need an EventStore. We'll use the DBAL with in-memory sqlite
$eventStore = DBALEventStore::createWithOptions('events', [
    'driver' => 'pdo_sqlite',
    'memory' => true
]);

// Create the table we need
$eventStore->createTable();

// Initialise a repository with this event store
$userRepository = new UserRepository($eventStore);

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
