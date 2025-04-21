KikwikDoctrineEntityLoggerBundle
================================

Subscriber for gedmo/doctrine-extension Blameable that set the actual command name in the createdBy and updatedBy fields


Installation
------------

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Open a command console, enter your project directory and execute:

```console
$ composer require kikwik/doctrine-entity-logger-bundle
```

Update the database to create the logger table (kw_entity_log):

```console
$ php bin/console make:migration
$ php bin/console doctrine:migrations:migrate
```


