.. index::
   pair: Doctrine; Migrations

How to use Doctrine Migrations
==============================

The database migrations feature is an extension of the database abstraction
layer and offers you the ability to programmatically deploy new versions of
your database schema in a safe and standardized way.

.. tip::

    You can read more about the Doctrine Database Migrations on the projects
    `documentation`_.

Installation
------------

Doctrine migrations are maintained in the `DoctrineMigrationsBundle`_.
Make sure you have the ``doctrine-migrations`` and ``DoctrineMigrationsBundle``
libraries configured in your project.  Follow these steps to install the
libraries in the Symfony Standard distribution.

Add the following to ``bin/deps``.  This will register the Migrations Bundle
and the doctrine-migrations library as dependencies in your application:

.. code-block:: text

    /                       doctrine-migrations       https://github.com/doctrine/migrations.git
    /bundles/Symfony/Bundle DoctrineMigrationsBundle  https://github.com/symfony/DoctrineMigrationsBundle.git

Update the vendor libraries:

.. code-block:: bash

    $ php bin/vendors.php

Now ensure your libraries will be autoloaded in ``autoload.php`` and
your bundle enabled in ``AppKernel.php`` by including the following:

.. code-block:: php

    // app/autoload.php
    $loader->registerNamespaces(array(
        //...
        'Symfony'                    => array(__DIR__.'/../vendor/symfony/src', __DIR__.'/../vendor/bundles'),
        'Doctrine\\DBAL\\Migrations' => __DIR__.'/../vendor/doctrine-migrations/lib',
        'Doctrine\\DBAL'             => __DIR__.'/../vendor/doctrine-dbal/lib',
    ));

.. code-block:: php

    // app/AppKernel.php
    public function registerBundles()
    {
        $bundles = array(
            //...
            new Symfony\Bundle\DoctrineMigrationsBundle\DoctrineMigrationsBundle(),
        );
    }

Usage
-----

All of the migrations functionality is contained in a few console commands:

.. code-block:: bash

    doctrine:migrations
      :diff     Generate a migration by comparing your current database to your mapping information.
      :execute  Execute a single migration version up or down manually.
      :generate Generate a blank migration class.
      :migrate  Execute a migration to a specified version or the latest available version.
      :status   View the status of a set of migrations.
      :version  Manually add and delete migration versions from the version table.

Every bundle manages its own migrations so when working with the above commands
you must specify the bundle you want to work with. For example to see the
status of a bundle migrations you can run the ``status`` command:

.. code-block:: bash

    $ php app/console doctrine:migrations:status

     == Configuration

        >> Name:                                               HelloBundle Migrations
        >> Configuration Source:                               manually configured
        >> Version Table Name:                                 hello_bundle_migration_versions
        >> Migrations Namespace:                               Application\Migrations
        >> Migrations Directory:                               /path/to/symfony-sandbox/app/DoctrineMigrations
        >> Current Version:                                    0
        >> Latest Version:                                     0
        >> Executed Migrations:                                0
        >> Available Migrations:                               0
        >> New Migrations:                                     0

Now, we can start working with migrations by generating a new blank migration
class:

.. code-block:: bash

    $ php app/console doctrine:migrations:generate
    Generated new migration class to "/path/to/project/app/DoctrineMigrations/Version20100621140655.php"

.. tip::

    You may need to create the folder ``/path/to/project/app/DoctrineMigrations``
    before running the ``doctrine:migrations:generate`` command.

Have a look at the newly generated migration class and you will see something
like the following::

    namespace Application\Migrations;

    use Doctrine\DBAL\Migrations\AbstractMigration,
        Doctrine\DBAL\Schema\Schema;

    class Version20100621140655 extends AbstractMigration
    {
        public function up(Schema $schema)
        {

        }

        public function down(Schema $schema)
        {

        }
    }

If you were to run the ``status`` command it will show that you have one new
migration to execute:

.. code-block:: bash

    $ php app/console doctrine:migrations:status

     == Configuration

       >> Name:                                               HelloBundle Migrations
       >> Configuration Source:                               manually configured
       >> Version Table Name:                                 hello_bundle_migration_versions
       >> Migrations Namespace:                               Application\Migrations
       >> Migrations Directory:                               /path/to/symfony-sandbox/app/DoctrineMigrations
       >> Current Version:                                    0
       >> Latest Version:                                     2010-06-21 14:06:55 (20100621140655)
       >> Executed Migrations:                                0
       >> Available Migrations:                               1
       >> New Migrations:                                     1

    == Migration Versions

       >> 2010-06-21 14:06:55 (20100621140655)                not migrated

Now you can add some migration code to the ``up()`` and ``down()`` methods and
migrate:

.. code-block:: bash

    $ php app/console doctrine:migrations:migrate

.. _documentation: http://www.doctrine-project.org/docs/migrations/2.0/en
.. _DoctrineMigrationsBundle: https://github.com/symfony/DoctrineMigrationsBundle