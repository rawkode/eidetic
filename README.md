# Eidetic

**Warning: Unlikely to be updated anytime soon due to time restraints**

[![Software License](https://img.shields.io/github/license/rawkode/eidetic.svg?style=flat-square)](LICENSE)
[![Latest Version](https://img.shields.io/packagist/v/rawkode/eidetic.svg?style=flat-square)](https://packagist.org/packages/rawkode/eidetic)
[![Build Status](https://img.shields.io/travis/rawkode/eidetic/master.svg?style=flat-square)](https://travis-ci.org/rawkode/eidetic)
[![Quality Score](https://img.shields.io/scrutinizer/g/rawkode/eidetic.svg?style=flat-square)](https://scrutinizer-ci.com/g/rawkode/eidetic)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/16900797-b872-44bf-8a20-b5e13080e9f0/small.png)](https://insight.sensiolabs.com/projects/16900797-b872-44bf-8a20-b5e13080e9f0)

---
Eidetic is a CQRS and EventSourcing library for php >= 5.5

#### Extremely Alpha
**Please do not use this library for anything important - it's purely for fun**

## Why not Broadway?
Yes - I've seen Broadway and it's a fantastic package, but it wasn't for me.

  * I should be able to use an EventStore / EventSourcing without committing to DDD (Not all projects suit!)
    * Even if it's just avoiding the vocabulary
    * I don't always want to use the Aggregate pattern
  * I prefer composition over inheritance:
    * I don't really want to use inheritance for my entities
    * I **really** don't want to use inheritance for my events

This package should allow people to dip their toe in the waters and allow them to consider if using reactive / event based systems will work for them; even if that's simply setting up an EventStore to provide a basic audit trail for a legacy application. Take it slow, get your feet wet - then dive right in! :)

## Status
Eidetic is currently under initial development. The aim is to provide helpers that allow you to implement CQRS and EventSourcing in your application.

- CQRS
  - Write model repositories
    - Event Store


- Event Stores
  - InMemory
  - Doctrine DBAL

- Event Subscribers
  - Symfony2 Event Dispatcher


## Examples
Examples can be found inside [`usr/share/doc/example`](usr/share/doc/example)

## Installation
```composer require rawkode/eidetic```

Sorry! As this is extremely experimental at the moment, please use ```dev-master```.

## Tests

### Testing with local version of php
~~~
bin/phpunit
bin/phpspec run --format=pretty
~~~

### Testing with Docker
~~~
docker-compose up testing-php-5.5
docker-compose up testing-php-5.6
docker-compose up testing-php-7.0
~~~

### Extra Testing?
~~~
docker-compose up testing-database-mysql
docker-compose up testing-database-postgres
~~~

If you're having problems with these tests, it's because we can't tell Docker Compose that we need the database servers up and running before running our test application and you might be subject to the race condition. Until Docker Compose has a solution for this, simply boot the database first:
~~~
docker-compose up -d mysql
docker-compose up -d postgres
~~~

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.
