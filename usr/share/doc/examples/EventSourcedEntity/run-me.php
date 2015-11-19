<?php

require_once '../../var/vendor/autoload.php';
require_once 'User.php';
require_once 'UserCreatedWithUsername.php';

$user = User::createWithUsername("David");

echo "Hello, {$user->username()}!" . PHP_EOL;
