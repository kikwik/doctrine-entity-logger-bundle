KikwikDoctrineEntityLoggerBundle
================================

> ⚠️ **Note:** This bundle is a work in progress and may be subject to changes.


The **KikwikDoctrineEntityLoggerBundle** is a Symfony bundle designed to log changes made to Doctrine entities. 
With this bundle, you can easily track modifications to your database entities and maintain a historical record of changes in a dedicated table.

#### Key Features:
- Automatic logging of updates, inserts, and deletions for Doctrine entities.
- Stores change history in a database table (`kw_entity_log`) for easy access and review.
- Integrates seamlessly with Symfony projects.

This bundle enables quick and effective logging of entity changes, making it a valuable tool for projects requiring audit trails or entity history tracking.

This bundle is inspired by the [manasbala/doctrine-log-bundle](https://github.com/manasbala/doctrine-log-bundle) and the [gedmo/doctrine-extensions](https://github.com/doctrine-extensions/DoctrineExtensions/blob/main/doc/loggable.md) projects.

The tests for this bundle were developed with the help of the following article: [Symfony functional tests for standalone bundles](https://medium.com/@fico7489/symfony-functional-tests-for-standalone-bundles-9666045a2309).


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

> ⚠️ **Note:** In version v0.3.0 the entity definition has changed, be sure to make and run your migration


Usage
-----

Add the `\Kikwik\DoctrineEntityLoggerBundle\Attributes\LoggableEntity` attribute to the entities you want to log


Easy Admin
----------

If you are using easy admin you can create a ready-to-use Crud controller by extending `KikwikLogCrudController`:

```php
namespace App\Controller\Admin;

use Kikwik\DoctrineEntityLoggerBundle\EasyAdmin\KikwikLogCrudController;

class LogCrudController extends KikwikLogCrudController
{

}
```

Then add the entity log controller to your dashboard

```php
namespace App\Controller\Admin;

use Kikwik\DoctrineEntityLoggerBundle\Entity\Log;

class DashboardController extends AbstractDashboardController
{
    public function configureMenuItems(): iterable
    {
        // ....
        
        yield MenuItem::section('Log');
        yield MenuItem::linkToCrud('Log azioni', 'fas fa-history', Log::class);
    }
}
```

And add the `LogField` to your loggable controller

```php
namespace App\Controller\Admin;

use Kikwik\DoctrineEntityLoggerBundle\EasyAdmin\LogField;

class MyCrudController extends AbstractCrudController
{
    public function configureFields(string $pageName): iterable
    {
        // ...
        
        yield LogField::new('log'); // 'log' is a dummy name
    }
}
```