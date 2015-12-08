## 0.1.4
 * Repositories now verify that the EventSourcedEntity that is to be saved
    is the same version that is available in the event store.
 * Added ability to register subscribers to the event store to monitor
    various event hooks: Transaction started and completed, Event pre and post stored
 * Comprehensive test suite that runs against php 5.5, 5.6, 7.0 and MySQL & PostgreSQL

## 0.1.2
 * Added an example entity using EventSourcedEntityMixin

## 0.1.1
 * Initial release (Need to begin somewhere!)
