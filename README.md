# Eidetic

[![Software License](https://img.shields.io/github/license/rawkode/eidetic.svg?style=flat-square)](LICENSE)
[![Latest Version](https://img.shields.io/packagist/v/rawkode/eidetic.svg?style=flat-square)](https://packagist.org/packages/rawkode/eidetic)
[![Build Status](https://img.shields.io/travis/rawkode/eidetic/master.svg?style=flat-square)](https://travis-ci.org/rawkode/eidetic)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/rawkode/eidetic.svg?style=flat-square)](https://scrutinizer-ci.com/g/rawkode/eidetic)
[![Quality Score](https://img.shields.io/scrutinizer/g/rawkode/eidetic.svg?style=flat-square)](https://scrutinizer-ci.com/g/rawkode/eidetic)
[![Total Downloads](https://img.shields.io/packagist/dt/rawkode/eidetic.svg?style=flat-square)](https://packagist.org/packages/rawkode/eidetic)

---
Eidetic is a CQRS and EventSourcing library for php >= 5.4

## Status
Eidetic is currently under initial development, aiming for 1.0 in December, 2015. The aim is to provide helpers that allow you to implement CQRS and EventSourcing in your application.

- CQRS
  - Read model repositories
    - Elasticsearch
    - Doctrine ORM
    - Eloquent
  - Write model repositories
    - Event Store
    - Doctrine ORM
    - Eloquent


- Event Stores
  - InMemory
  - Doctrine DBAL


- Event Publishers
  - MessageBus
  - Tactician


## Examples
Examples can be found inside [`usr/share/doc/examples`](usr/share/doc/examples)

## Database Setup
Using the DBAL Event Store?

```
CREATE TABLE 'table_name'
(
    entity_identifier   VARCHAR(255),
    recorded_at         DATETIME,
    event_class         VARCHAR(255),
    event               TEXT,

    PRIMARY KEY (entity_identifier, recorded_at)
)
```

## Tests

~~~
bin/phpunit
bin/phpspec run --format=pretty
~~~

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.
