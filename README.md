# Eidetic

[![Software License](https://img.shields.io/github/license/rawkode/eidetic.svg?style=flat-square)](LICENSE)
[![Build Status](https://img.shields.io/travis/rawkode/eidetic/master.svg?style=flat-square)](https://travis-ci.org/rawkode/eidetic)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/rawkode/eidetic.svg?style=flat-square)](https://scrutinizer-ci.com/g/rawkode/eidetic)
[![Quality Score](https://img.shields.io/scrutinizer/g/rawkode/eidetic.svg?style=flat-square)](https://scrutinizer-ci.com/g/rawkode/eidetic)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/16900797-b872-44bf-8a20-b5e13080e9f0/small.png)](https://insight.sensiolabs.com/projects/16900797-b872-44bf-8a20-b5e13080e9f0)

[![Latest Version](https://img.shields.io/packagist/v/rawkode/eidetic.svg?style=flat-square)](https://packagist.org/packages/rawkode/eidetic)
[![Total Downloads](https://img.shields.io/packagist/dt/rawkode/eidetic.svg?style=flat-square)](https://packagist.org/packages/rawkode/eidetic)

---
Eidetic is a CQRS and EventSourcing library for php >= 5.5

#### Extremely Alpha
**Please do not use this library for anything important - it's API is likely to change over the coming weeks**

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
Eidetic is currently under initial development, aiming for 1.0 in December, 2015. The aim is to provide helpers that allow you to implement CQRS and EventSourcing in your application.

- CQRS
  - Read model repositories
    - Elasticsearch **(Pending)**
    - PDO PostgreSQL: jsonb :) **(Pending)**
    - DynamoDb **(Pending)**
  - Write model repositories
    - Event Store


- Event Stores
  - InMemory
  - Doctrine DBAL
  - PDO **(Pending)**
  - DynamoDb **(Pending)**
  - Mongo **(Pending)**


- Event Subscribers
  - Amazon Kinesis **(Pending)**
  - Symfony2 Event Dispatcher


## Examples
Examples can be found inside [`usr/share/doc/example`](usr/share/doc/example)

## Installation
```composer require rawkode/eidetic```

Sorry! As this is extremely experimental at the moment, please use ```dev-master```.

## Tests

~~~
bin/phpunit
bin/phpspec run --format=pretty
~~~

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.
