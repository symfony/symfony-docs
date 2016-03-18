.. index::
   single: Doctrine

Databases and Doctrine
======================

One of the most common and challenging tasks for any application
involves persisting and reading information to and from a database. Although
the Symfony full-stack Framework doesn't integrate any `ORM`_ by default,
the Symfony Standard Edition, which is the most widely used distribution,
comes integrated with `Doctrine`_, a library whose sole goal is to give
you powerful tools to make this easy. In this chapter, you'll learn the
basic philosophy behind Doctrine and see how easy working with a database
can be.

.. note::

    Doctrine is totally decoupled from Symfony and using it is optional.
    This chapter is all about the Doctrine ORM, which aims to let you map
    objects to a relational database (such as *MySQL*, *PostgreSQL* or
    *Microsoft SQL*). You can also persist data to document-oriented databases
    like `MongoDB`_ using Doctrine ODM library. For more information, read the
    `DoctrineMongoDBBundle`_ documentation. If you prefer to use raw database
    queries, this is easy, and explained in the cookbook article
    :doc:`/cookbook/doctrine/dbal`.

.. index::
   single: Doctrine; Database configuration

Database Configuration
----------------------

Before you really begin, you'll need to configure your database connection
information. By convention, this information is usually configured in an
``app/config/parameters.yml`` file:

.. code-block:: yaml

    # app/config/parameters.yml
    parameters:
        database_driver:    pdo_mysql
        database_host:      localhost
        database_name:      test_project
        database_user:      root
        database_password:  password

    # ...

Defining the configuration via ``parameters.yml`` is just a convention.
The parameters defined in that file are referenced by the default configuration
file when setting up Doctrine:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        doctrine:
            dbal:
                driver:   '%database_driver%'
                host:     '%database_host%'
                dbname:   '%database_name%'
                user:     '%database_user%'
                password: '%database_password%'

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:doctrine="http://symfony.com/schema/dic/doctrine"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/doctrine
                http://symfony.com/schema/dic/doctrine/doctrine-1.0.xsd">

            <doctrine:config>
                <doctrine:dbal
                    driver="%database_driver%"
                    host="%database_host%"
                    dbname="%database_name%"
                    user="%database_user%"
                    password="%database_password%" />
            </doctrine:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $configuration->loadFromExtension('doctrine', array(
            'dbal' => array(
                'driver'   => '%database_driver%',
                'host'     => '%database_host%',
                'dbname'   => '%database_name%',
                'user'     => '%database_user%',
                'password' => '%database_password%',
            ),
        ));

By separating the database information into a separate file, you can
easily keep different versions of the file on each server. You can also
easily store database configuration (or any sensitive information) outside
of your project, like inside your web server configuration, for example. For
more information, see :doc:`/cookbook/configuration/external_parameters`
cookbook article.

.. index::
   single: Doctrine; Database charset and collation

Database Charset and Collation
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

One mistake even seasoned developers make when starting a Symfony project
is forgetting to set up default charset and collation (the way strings are
sorted) on their database, ending up with latin type collations, which are
default for most databases.
They might even remember to do it the very first time, but forget that
it's all gone after running a relatively common command during development:

.. code-block:: bash

    $ php app/console doctrine:database:drop --force
    $ php app/console doctrine:database:create

There's no way to configure these defaults inside Doctrine, as it tries to be
as agnostic as possible in terms of environment configuration. One way to solve
this problem is to configure server-level defaults.

.. sidebar:: Setting up the Database to be UTF8

    The MySQL server can support multiple character sets. To list the available
    character sets, use the MySQL ``SHOW CHARACTER SET`` statement. Any given character
    set always has at least one collation. It may have several collations. To
    list the collations for a character set, use the ``SHOW COLLATION`` statement.
    There are default settings for character sets and collations at four levels:
    server, database, table, and column.

    These are MySQL default settings:

    .. code-block:: bash

        mysql> show variables like 'char%';
        +--------------------------+----------------------------+
        | Variable_name            | Value                      |
        +--------------------------+----------------------------+
        | character_set_client     | utf8                       |
        | character_set_connection | utf8                       |
        | character_set_database   | latin1                     |
        | character_set_filesystem | binary                     |
        | character_set_results    | utf8                       |
        | character_set_server     | latin1                     |
        | character_set_system     | utf8                       |
        | character_sets_dir       | /usr/share/mysql/charsets/ |
        +--------------------------+----------------------------+

        mysql> show variables like 'collation%';
        +----------------------+-------------------+
        | Variable_name        | Value             |
        +----------------------+-------------------+
        | collation_connection | utf8_general_ci   |
        | collation_database   | latin1_swedish_ci |
        | collation_server     | latin1_swedish_ci |
        +----------------------+-------------------+

    * The **server** character set and collation are the values of the
      ``character_set_server`` and ``collation_server`` system variables;
    * The character set and collation of the default **database** are the
      values of the ``character_set_database`` and ``collation_database``
      system variables.

    Additional character set and collation system variables are involved in
    handling traffic for the connection between a client and the server. Every
    client has connection-related character set and collation system variables.

    * ``character_set_client`` stores the character set in which statements are
      sent by the client;
    * Server converts statements sent by the client from ``character_set_client``
      to ``character_set_connection``;
    * ``character_set_results`` indicates the character set in which the server
      returns query results to the client.

    Setting ``utf8`` defaults for MySQL is as simple as adding a few lines to
    MySQL configuration file (typically ``my.cnf``) and restarting MySQL:

    .. code-block:: ini

        [mysqld]
        # Version 5.5.3 introduced "utf8mb4", which is recommended
        collation-server     = utf8mb4_general_ci # Replaces utf8_general_ci
        character-set-server = utf8mb4            # Replaces utf8

    These settings apply server-wide and apply as the defaults for databases
    created by any application, and for tables created in those databases:

    .. code-block:: bash

        mysql> show variables like 'char%';
        +--------------------------+----------------------------+
        | Variable_name            | Value                      |
        +--------------------------+----------------------------+
        | character_set_client     | utf8                       |
        | character_set_connection | utf8                       |
        | character_set_database   | utf8                       |
        | character_set_filesystem | binary                     |
        | character_set_results    | utf8                       |
        | character_set_server     | utf8                       |
        | character_set_system     | utf8                       |
        | character_sets_dir       | /usr/share/mysql/charsets/ |
        +--------------------------+----------------------------+

        mysql> show variables like 'collation%';
        +----------------------+--------------------+
        | Variable_name        | Value              |
        +----------------------+--------------------+
        | collation_connection | utf8_general_ci    |
        | collation_database   | utf8_general_ci    |
        | collation_server     | utf8_general_ci    |
        +----------------------+--------------------+

    However, it seems Doctrine specifically defines the encoding and collation
    when creating a new tables no matter server-wide defaults - settings on the
    database-level are overwritten by the table-specific configurations set by
    Doctrine. Therefore we need to set the charset and collation for every table
    since there is no global option and Doctrine doesn't respect the setting
    defined in MySQL.

    The related options are called "charset" and "collate" and can be set using
    all configuration formats, for example, when using annotations::

        /**
         * @ORM\Entity()
         * @ORM\Table(name="citizenship",
         *     options={"collate"="utf8_general_ci", "charset"="utf8"})
         */

    Symfony recommends against MySQL's ``utf8`` character set, since it does not
    support 4-byte unicode characters, and strings containing them will be
    truncated. This is fixed by the `newer utf8mb4 character set`_. But
    before converting your tables from ``utf8`` to ``utf8mb4`` there is
    one thing you need to consider. An index in InnoDB always has a maximum size
    of 767, bytes regardless of the number of bytes used for a single character.
    Consider a column with a maximum of 255 characters, this would result in 765
    bytes when using ``utf8`` but in 1020 bytes when using ``utf8mb4``. If you
    need an index on a ``utf8mb4`` column the maximum number of characters is 191
    instead of 255.

.. note::

    If you want to use SQLite as your database, you need to set the path
    where your database file should be stored:

    .. configuration-block::

        .. code-block:: yaml

            # app/config/config.yml
            doctrine:
                dbal:
                    driver: pdo_sqlite
                    path: '%kernel.root_dir%/sqlite.db'
                    charset: UTF8

        .. code-block:: xml

            <!-- app/config/config.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <container xmlns="http://symfony.com/schema/dic/services"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xmlns:doctrine="http://symfony.com/schema/dic/doctrine"
                xsi:schemaLocation="http://symfony.com/schema/dic/services
                    http://symfony.com/schema/dic/services/services-1.0.xsd
                    http://symfony.com/schema/dic/doctrine
                    http://symfony.com/schema/dic/doctrine/doctrine-1.0.xsd">

                <doctrine:config>
                    <doctrine:dbal
                        driver="pdo_sqlite"
                        path="%kernel.root_dir%/sqlite.db"
                        charset="UTF-8" />
                </doctrine:config>
            </container>

        .. code-block:: php

            // app/config/config.php
            $container->loadFromExtension('doctrine', array(
                'dbal' => array(
                    'driver'  => 'pdo_sqlite',
                    'path'    => '%kernel.root_dir%/sqlite.db',
                    'charset' => 'UTF-8',
                ),
            ));

.. index::
   single: Doctrine; Database creation

Database Creation
-----------------

Now that Doctrine knows about your database, you can have it create for you
using ``doctrine:database:create`` console command:

.. code-block:: bash

    $ php app/console doctrine:database:create

.. index::
    single: Doctrine; Configuration

Configuration
-------------

Doctrine is highly configurable, though you probably won't ever need to worry
about most of its options. To find out more about configuring Doctrine, see
the Doctrine section of the
:doc:`configuration reference </reference/configuration/doctrine>`.

A Simple Example: A Product
---------------------------

The easiest way to understand how Doctrine works is to see it in action.
In this section, you'll configure your database, create a ``Product`` object,
persist it to the database and fetch it back out.

Creating an Entity Class
~~~~~~~~~~~~~~~~~~~~~~~~

Suppose you're building an application where products need to be displayed.
Without even thinking about Doctrine or databases, you already know that
you need a ``Product`` object to represent those products. Create this class
inside the ``Entity/`` directory of your AppBundle::

    // src/AppBundle/Entity/Product.php
    namespace AppBundle\Entity;

    class Product
    {
        private $name;
        private $price;
        private $description;
    }

The class - often called an "entity", meaning *a basic class that holds data* -
is simple and helps fulfill the business requirement of needing products
in your application. This class can't be persisted to a database yet - it's
just a simple PHP class.

.. tip::

    Once you learn the concepts behind Doctrine, you can have Doctrine create
    simple entity classes for you. This can be done using ``doctrine:generate:entity``
    console command which asks interactive questions to help build any entity:

    .. code-block:: bash

        $ php app/console doctrine:generate:entity

.. index::
    single: Doctrine; Adding mapping metadata

.. _book-doctrine-adding-mapping:

Adding Mapping Metadata
~~~~~~~~~~~~~~~~~~~~~~~

Doctrine allows you to work with databases in a much more interesting way
than just fetching rows of scalar data into an array. Instead, Doctrine
allows you to fetch entire *objects* out of the database, and to persist
entire objects to the database. For Doctrine to be able to do this, you
must *map* your database tables to specific PHP classes, and the columns
on those tables must be mapped to specific properties on their corresponding
PHP classes.

.. image:: /images/book/doctrine_image_1.png
   :align: center

You'll provide this mapping information in the form of "metadata", a collection
of rules that tells Doctrine exactly how the ``Product`` class and its
properties should be *mapped* to a specific database table. This metadata
can be specified in a number of different formats, including YAML, XML or
directly inside the ``Product`` class via DocBlock annotations:

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Entity/Product.php
        namespace AppBundle\Entity;

        use Doctrine\ORM\Mapping as ORM;

        /**
         * @ORM\Entity
         * @ORM\Table(name="product")
         */
        class Product
        {
            /**
             * @ORM\Column(type="integer")
             * @ORM\Id
             * @ORM\GeneratedValue(strategy="AUTO")
             */
            private $id;

            /**
             * @ORM\Column(type="string", length=100)
             */
            private $name;

            /**
             * @ORM\Column(type="decimal", scale=2)
             */
            private $price;

            /**
             * @ORM\Column(type="text")
             */
            private $description;
        }

    .. code-block:: yaml

        # src/AppBundle/Resources/config/doctrine/Product.orm.yml
        AppBundle\Entity\Product:
            type: entity
            table: product
            id:
                id:
                    type: integer
                    generator: { strategy: AUTO }
            fields:
                name:
                    type: string
                    length: 100
                price:
                    type: decimal
                    scale: 2
                description:
                    type: text

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/doctrine/Product.orm.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://doctrine-project.org/schemas/orm/doctrine-mapping
                http://doctrine-project.org/schemas/orm/doctrine-mapping.xsd">

            <entity name="AppBundle\Entity\Product" table="product">
                <id name="id" type="integer">
                    <generator strategy="AUTO" />
                </id>
                <field name="name" type="string" length="100" />
                <field name="price" type="decimal" scale="2" />
                <field name="description" type="text" />
            </entity>
        </doctrine-mapping>

.. seealso::

    You can check out Doctrine's `Basic Mapping documentation`_ for all
    details about mapping information.

.. note::

    A bundle can accept only one metadata definition format. For example, it's
    not possible to mix YAML metadata definitions with annotated PHP entity
    class definitions.

If you use annotations, you'll need to prepend all annotations with ``ORM\``
(e.g. ``ORM\Column(...)``), which is not shown in Doctrine's documentation.
You'll also need to include the ``use Doctrine\ORM\Mapping as ORM;`` statement,
which *imports* the ``ORM`` annotations prefix.

Doctrine allows you to choose from a wide variety of different field types,
each with their own options. For information on the available field types,
see the :ref:`book-doctrine-field-types` section of this chapter.

.. tip::

    The table name is optional and if omitted, will be determined automatically
    based on the name of the entity class.

.. caution::

    Be careful if the names of your entity classes (or their properties)
    are also reserved SQL keywords like ``GROUP`` or ``USER``. For example,
    if your entity's class name is ``Group``, then, by default, the corresponding
    table name would be ``group``. This will cause an SQL error in some database
    engines. See Doctrine's `Reserved SQL keywords documentation`_ for details
    on how to properly escape these names. Alternatively, if you're free
    to choose your database schema, simply map to a different table name
    or column name. See Doctrine's `Creating Classes for the Database`_
    and `Property Mapping`_ documentation.

.. note::

    When using another library or program (e.g. Doxygen) that uses annotations,
    you should place the ``@IgnoreAnnotation`` annotation on the class to
    indicate which annotations Symfony should ignore.

    For example, to prevent the ``@fn`` annotation from throwing an exception,
    add the following::

        /**
         * @IgnoreAnnotation("fn")
         */
        class Product
        // ...

.. index::
    single: Doctrine; Doctrine field types reference

.. _book-doctrine-field-types:

Doctrine Field Types Reference
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Doctrine comes with numerous field types available. Each of these
maps a PHP data type to a specific column type in whatever database you're
using. For each field type, the ``Column`` can be configured further, setting
the ``length``, ``nullable`` behavior, ``name`` and other options. To see a
list of all available types and more information, see Doctrine's
`Mapping Types documentation`_.

.. index::
    single: Doctrine; Generating getters and setters

.. _book-doctrine-generating-getters-and-setters:

Generating Getters and Setters
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Even though Doctrine now knows how to persist a ``Product`` object to the
database, the class itself isn't really useful yet. Since ``Product`` is just
a regular PHP class with ``private`` properties, you need to create ``public``
getter and setter methods (e.g. ``getName()``, ``setName($name)``) in order
to access its properties in the rest of your application's code. Fortunately,
the following command can generate these boilerplate methods automatically:

.. code-block:: bash

    $ php app/console doctrine:generate:entities AppBundle/Entity/Product

This command makes sure that all the getters and setters are generated
for the ``Product`` class. This is a safe command - you can run it over and
over again: it **only generates getters and setters that don't exist** (i.e.
it doesn't replace your existing methods).

Keep in mind that Doctrine's entity generator produces simple getters/setters.
You should review the generated methods and add any logic, if necessary,
to suit the needs of your application.

The ``doctrine:generate:entities`` command saves a backup of the original
``Product.php`` named ``Product.php~``. In some cases, the presence of
this file can cause a "Cannot redeclare class" error. It can be safely
removed. You can also use the ``--no-backup`` option to prevent generating
these backup files.

Note that you don't *need* to use this command. You could also write the
necessary getters and setters by hand. This option simply exists to save
you time, since creating these methods is often a common task during
development.

You can also generate all known entities (i.e. any PHP class with Doctrine
mapping information) of a bundle or an entire namespace:

.. code-block:: bash

    # generates all entities in the AppBundle
    $ php app/console doctrine:generate:entities AppBundle

    # generates all entities of bundles in the Acme namespace
    $ php app/console doctrine:generate:entities Acme

.. sidebar:: More about ``doctrine:generate:entities``

    Besides getters and setters ``doctrine:generate:entities`` command can
    also generates:

    * the appropriate constructor for 1:n and n:m relations.

    * repository classes on behalf of entities configured with the
      ``@ORM\Entity(repositoryClass="...")`` annotation
      (see :ref:`book-doctrine-custom-repository-classes` section of this
      chapter);

.. index::
    single: Doctrine; Creating the database tables/schema
    single: Doctrine; Updating the database tables/schema

.. _book-doctrine-creating-the-database-tables-schema:

Creating (and updating) the Database Tables/Schema
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You now have a usable ``Product`` class with mapping information so that
Doctrine knows exactly how to persist it. Of course, you don't yet have the
corresponding ``product`` table in your database. Fortunately, Doctrine can
automatically create all the database tables needed for every known entity
in your application. This can be done using ``doctrine:schema:update`` console
command:

.. code-block:: bash

    $ php app/console doctrine:schema:update --force

This command is incredibly powerful. It compares what your database *should*
look like (based on the mapping information of your entities) with how it
*actually* looks, and executes the SQL statements needed to *update* the
database schema to where it should be. In other words, if you add a new property with
mapping metadata to ``Product`` and run this task, it will generate the
"ALTER TABLE" statement needed to add that new column to the existing
``product`` table.

    An even better way to take advantage of this functionality is via
    `migrations`_, which allow you to generate these SQL statements and store
    them in migration classes that can be run systematically on your production
    server in order to update and track changes to your database schema safely
    and reliably.

    Whether or not you take advantage of migrations, the ``doctrine:schema:update``
    command should only be used during development. It should not be used in
    a production environment.

Your database now has a fully-functional ``product`` table with columns that
match the metadata you've specified.

.. index::
    single: Doctrine; Persisting objects

Persisting Objects to the Database
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Now that you have mapped the ``Product`` entity to its corresponding ``product``
table, you're ready to persist ``Product`` objects to the database. From inside
a controller, this is pretty easy. Add the following method to the
``DefaultController`` of the bundle::


    // src/AppBundle/Controller/DefaultController.php

    // ...
    use AppBundle\Entity\Product;
    use Symfony\Component\HttpFoundation\Response;

    // ...
    public function createAction()
    {
        $product = new Product();
        $product->setName('Keyboard');
        $product->setPrice(19.99);
        $product->setDescription('Ergonomic and stylish!');

        $em = $this->getDoctrine()->getManager();

        // tells Doctrine you want to (eventually) save the Product (no queries yet)
        $em->persist($product);

        // actually executes the queries (i.e. the INSERT query)
        $em->flush();

        return new Response('Saved new product with id '.$product->getId());
    }

.. note::

    If you're following along with this example, you'll need to create a
    route that points to this action to see it work.

:method:`Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller::getDoctrine`
method is a method of Symfony base ``Controller`` class. This method is a shortcut to
get the ``doctrine`` service.

Take a look at the example in more detail:

* **lines 10-13**: The ``$product`` object is instantiate and worked with like
  any other, normal PHP object.

* **line 15**: This line fetches Doctrine's *entity manager* object, which is
  responsible for the process of persisting objects to, and fetching objects
  from, the database.

* **line 17** The ``persist($product)`` call tells Doctrine to "manage" the
  ``$product`` object. This does **not** cause a query to be made to the database.

* **line 18**: When the ``flush()`` method is called, Doctrine looks through
  all of the objects that it's managing to see if they need to be persisted
  to the database. In this example, the ``$product`` object's data doesn't
  exist in the database, so the entity manager executes an ``INSERT`` query,
  creating a new row in the ``product`` table.

In fact, since Doctrine is aware of all your managed entities, when you call
the ``flush()`` method, it calculates an overall changeset and executes
the queries in the correct order. It utilizes cached prepared statement to
slightly improve the performance. For example, if you persist a total of 100
``Product`` objects and then subsequently call ``flush()``, Doctrine will
execute 100 ``INSERT`` queries using a single prepared statement object.

Whether creating or updating objects, the workflow is always the same. In
the next section, you'll see how Doctrine is smart enough to automatically
issue an ``UPDATE`` query if the entity already exists in the database.

.. seealso::

    Doctrine provides a library that allows you to programmatically load testing
    data into your project (i.e. "fixture data"). For information, see
    the "`DoctrineFixturesBundle documentation`_" .

.. seealso::

    To use Doctrine outside controller classes (which extend Symfony base
    ``Controller`` class), for example in your own custom services you have
    to inject ``doctrine`` service into the service. To learn about services
    and custom services read :doc:`Service container chapter </book/service_container>`.

.. index::
    single: Doctrine; Fetching objects from the database using getRepository()
    single: Doctrine; Default finder methods

.. _database-fetching-obj-using-repo:

Fetching Objects from the Database Using ``getRepository()``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Fetching an object back out of the database is even easier. For example,
suppose you've configured a route to display a specific ``Product`` based
on its ``id`` value::

    public function showAction($productId)
    {
        $product = $this->getDoctrine()
            ->getRepository('AppBundle:Product')
            ->find($productId);

        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '.$productId
            );
        }

        // ... do something, like pass the $product object into a template
    }

.. tip::

    You can achieve the equivalent of this without writing any code by using
    the ``@ParamConverter`` shortcut. See the `FrameworkExtraBundle documentation`_
    for more details.

When you query for a particular type of object, you always use what's known
as its "repository". You can think of a repository as a PHP class whose only
job is to help you fetch entities of a certain class. You can access the
repository object for an entity class via::

    $repository = $this->getDoctrine()
        ->getRepository('AppBundle:Product');

.. note::

    The ``AppBundle:Product`` string is a shortcut you can use anywhere
    in Doctrine instead of the full class name of the entity (i.e.
    ``AppBundle\Entity\Product``). As long as your entity lives under the
    ``Entity`` namespace of your bundle, this will work.

Once you have a repository object, you can access all sorts of helpful methods::

    $em = $this->getDoctrine()->getManager();
    $repository = $em->getRepository('AppBundle:Product');

    // query for a single product by its primary key (usually "id")
    $product = $repository->find($productId);

    // dynamic method names to find a single product based on a column value
    $product = $repository->findOneById($productId);
    $product = $repository->findOneByName('Keyboard');

    // dynamic method names to find a group of products based on a column value
    $products = $repository->findByPrice(19.99);

    // find *all* products
    $products = $repository->findAll();

You can also take advantage of the useful ``findBy()`` and ``findOneBy()``
methods to easily fetch objects based on multiple conditions::

    // query for a single product matching the given name and price
    $product = $repository->findOneBy(
        array('name' => 'Keyboard', 'price' => 19.99)
    );

    // query for multiple products matching the given name, ordered by price
    $products = $repository->findBy(
        array('name' => 'Keyboard'),
        array('price' => 'ASC')
    );

Of course, you can also issue complex queries, which you'll learn more
about in the :ref:`book-doctrine-queries` section of this chapter.

.. tip::

    When you render any page, you can see how many queries were made in the
    bottom right corner of the web debug toolbar.

    .. image:: /images/book/doctrine_web_debug_toolbar.png
       :align: center
       :scale: 50
       :width: 350

    If you click the icon, the profiler will open, showing you the exact
    queries that were made.

    The icon will turn yellow if there were more than 50 queries on the
    page. This could indicate that something is not correct.

.. index::
    single: Doctrine; Updating objects

Updating an Object
~~~~~~~~~~~~~~~~~~

Once you've fetched an object from Doctrine, updating it is easy. Suppose
you have a route that maps a product id to an update action in a controller::

    public function updateAction($productId)
    {
        $em = $this->getDoctrine()->getManager();
        $product = $em->getRepository('AppBundle:Product')->find($productId);

        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '.$productId
            );
        }

        $product->setName('New product name!');
        $em->flush();

        return $this->redirect($this->generateUrl('homepage'));
    }

Updating an object involves just three steps:

#. fetching the object from Doctrine;
#. modifying the object;
#. calling ``flush()`` on the entity manager.

Notice that calling ``$em->persist($product)`` isn't necessary. Recall that
this method simply tells Doctrine to manage or "watch" the ``$product`` object.
In this case, since you fetched the ``$product`` object from Doctrine, it's
already managed.

.. index::
    single: Doctrine; Deleting objects

Deleting an Object
~~~~~~~~~~~~~~~~~~

Deleting an object is very similar, but requires a call to the ``remove()``
method of the entity manager::

    $em->remove($product);
    $em->flush();

As you might expect, the ``remove()`` method notifies Doctrine that you'd
like to remove the given object from the database. The actual ``DELETE`` query,
however, isn't actually executed until the ``flush()`` method is called.

.. index::
    single: Doctrine; Querying objects from the database

.. _book-doctrine-queries:

Querying for Objects
--------------------

You've already :ref:`seen <database-fetching-obj-using-repo>` how the
**repository object** allows you to run basic queries without any work::

    $em = $this->getDoctrine()->getManager();
    $repository = $em->getRepository('AppBundle:Product');
    $product = $repository->find($productId);
    $product = $repository->findOneByName('Keyboard');

Of course, Doctrine also allows you to write more complex queries.
You have two other options:

* writing pure Doctrine queries with DQL;
* using Doctrine's Query Builder.

Using the **Doctrine Query Language** (DQL). DQL is similar to SQL except that you should
imagine that you're **querying for one or more *objects* of an entity class**
(e.g. ``Product``) **instead of querying for rows on a table** (e.g. ``product``).

.. index::
    single: Doctrine; Doctrine Query Language (DQL) using createQuery()

DQL using createQuery()
~~~~~~~~~~~~~~~~~~~~~~~

Imagine that you want to query for products, but only return products that
cost more than ``19.99``, ordered from cheapest to most expensive. You can use
Doctrine's native SQL-like language called DQL to make a query for this::

    $em = $this->getDoctrine()->getManager();
    $query = $em->createQuery(
        'SELECT p
        FROM AppBundle:Product p
        WHERE p.price > :price
        ORDER BY p.price ASC'
    )->setParameter('price', '19.99');

    $products = $query->getResult();

The ``getResult()`` method *returns an array of results*. To get only one
result, you can use ``getOneOrNullResult()``::

    $product = $query->setMaxResults(1)->getOneOrNullResult();

``createQuery()`` method returns a normal ``Query`` object, which can be used
to get the result of the query.

If you're comfortable with SQL, then DQL should feel very natural. The biggest
difference is that you need to think in terms of "objects" instead of rows
in a database. For this reason, you select *from* the ``AppBundle:Product``
*object* (an optional shortcut for ``AppBundle\Entity\Product``) and then
alias it as ``p``.

Take note of the ``setParameter()`` method. When working with Doctrine,
it's always a good idea to set any external values as "placeholders"
(``:price`` in the example above) as it **prevents SQL injection attacks**.

The DQL syntax is incredibly powerful, allowing you to easily join between
entities (the topic of :ref:`relations <book-doctrine-relations>` will be
covered later in this chapter), group, etc. For more information, see the
official `Doctrine Query Language documentation`_.

.. index::
    single: Doctrine; Doctrine's Query Builder

Doctrine's Query Builder using createQueryBuilder()
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Instead of writing a DQL string, you can use a helpful object called the
``QueryBuilder`` to build that string for you. This is useful when the actual
query depends on dynamic conditions, as your code soon becomes hard to read with
DQL as you start to concatenate strings::

    $repository = $this->getDoctrine()
        ->getRepository('AppBundle:Product');

    // createQueryBuilder() automatically selects FROM AppBundle:Product
    // and aliases it to "p"
    $query = $repository->createQueryBuilder('p')
        ->where('p.price > :price')
        ->setParameter('price', '19.99')
        ->orderBy('p.price', 'ASC')
        ->getQuery();

    $products = $query->getResult();

The ``getResult()`` method *returns an array of results*. To get only one
result, you can use ``getOneOrNullResult()``::

    // $product = $query->setMaxResults(1)->getOneOrNullResult();

The ``QueryBuilder`` object contains every method necessary to build your
query. By calling the ``getQuery()`` method, the query builder returns a
normal ``Query`` object, which can be used to get the result of the query.

For more information on Doctrine's Query Builder, consult Doctrine's
`Query Builder documentation`_.

.. index::
    single: Doctrine; Custom repository classes
    single: Doctrine; Custom finder methods

.. _book-doctrine-custom-repository-classes:

Custom Repository Classes
~~~~~~~~~~~~~~~~~~~~~~~~~

In the previous sections, you began constructing and using more complex queries
from *inside a controller*. In order to isolate, test and reuse these queries,
it's a good practice to create a custom repository class for your entity and
add methods with your query logic there. To do this, add the name of the
repository class to your mapping definition:

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Entity/Product.php
        namespace AppBundle\Entity;

        use Doctrine\ORM\Mapping as ORM;

        /**
         * @ORM\Entity(repositoryClass="AppBundle\Entity\ProductRepository")
         */
        class Product
        {
            //...
        }

    .. code-block:: yaml

        # src/AppBundle/Resources/config/doctrine/Product.orm.yml
        AppBundle\Entity\Product:
            type: entity
            repositoryClass: AppBundle\Entity\ProductRepository
            # ...

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/doctrine/Product.orm.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://doctrine-project.org/schemas/orm/doctrine-mapping
                http://doctrine-project.org/schemas/orm/doctrine-mapping.xsd">

            <entity
                name="AppBundle\Entity\Product"
                repository-class="AppBundle\Entity\ProductRepository">

                <!-- ... -->
            </entity>
        </doctrine-mapping>

Doctrine can generate the repository class for you by running the same console
command used :ref:`earlier <book-doctrine-generating-getters-and-setters>` to
generate the missing getter and setter methods:

.. code-block:: bash

    $ php app/console doctrine:generate:entities AppBundle

Next, add a new method called ``findAllOrderedByName()`` to the newly generated
repository class. This method will query for all the ``Product`` entities,
ordered alphabetically::

    // src/AppBundle/Entity/ProductRepository.php
    namespace AppBundle\Entity;

    use Doctrine\ORM\EntityRepository;

    class ProductRepository extends EntityRepository
    {
        public function findAllOrderedByName()
        {
            return $this->getEntityManager()
                ->createQuery(
                    'SELECT p FROM AppBundle:Product p ORDER BY p.name ASC'
                )
                ->getResult();
        }
    }

Repository class extends Doctrine's ``Doctrine\ORM\EntityRepository`` class
which supplies our repository class with ``getEntityManager()`` method.
Via this method the entity manager can be accessed from inside the repository.

**You can use this new method just like the default finder methods of the
repository**::

    $em = $this->getDoctrine()->getManager();
    $products = $em->getRepository('AppBundle:Product')
        ->findAllOrderedByName();

When using a custom repository class, you still have access to the default
finder methods such as ``find()`` and ``findAll()`` talked about
:ref:`here <database-fetching-obj-using-repo>`.

.. index::
    single: Doctrine; Entity Relationships/Associations

.. _`book-doctrine-relations`:

Entity Relationships/Associations
---------------------------------

Suppose that the products in your application all belong to exactly one "category".
In this case, you'll need a ``Category`` object and a way to relate a ``Product``
object to a ``Category`` object. Start by creating the ``Category`` entity.
Since you know that you'll eventually need to persist the class through Doctrine,
you can let Doctrine create the class for you.

.. code-block:: bash

    $ php app/console doctrine:generate:entity --no-interaction \
        --entity="AppBundle:Category" \
        --fields="name:string(255)"

This task generates the ``Category`` entity for you, with an ``id`` field,
a ``name`` field and the associated getter and setter functions.

.. index::
    single: Doctrine; Relationship mapping metadata

Relationship Mapping Metadata
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

To relate the ``Category`` and ``Product`` entities, start by creating a
``products`` property on the ``Category`` class:

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Entity/Category.php

        // ...
        use Doctrine\Common\Collections\ArrayCollection;

        class Category
        {
            // ...

            /**
             * @ORM\OneToMany(targetEntity="Product", mappedBy="category")
             */
            private $products;

            public function __construct()
            {
                $this->products = new ArrayCollection();
            }
        }

    .. code-block:: yaml

        # src/AppBundle/Resources/config/doctrine/Category.orm.yml
        AppBundle\Entity\Category:
            type: entity
            # ...
            oneToMany:
                products:
                    targetEntity: Product
                    mappedBy: category
        # Don't forget to initialize the collection in
        # the __construct() method of the entity

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/doctrine/Category.orm.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://doctrine-project.org/schemas/orm/doctrine-mapping
                http://doctrine-project.org/schemas/orm/doctrine-mapping.xsd">

            <entity name="AppBundle\Entity\Category">
                <!-- ... -->
                <one-to-many
                    field="products"
                    target-entity="Product"
                    mapped-by="category" />

                <!--
                    don't forget to init the collection in
                    the __construct() method of the entity
                -->
            </entity>
        </doctrine-mapping>

First, since a ``Category`` object will relate to many ``Product`` objects,
a ``products`` array property is added to hold those ``Product`` objects.
Noticed the plural form. Again, this isn't done because Doctrine needs it,
but instead because it makes sense in the application for each ``Category``
to hold an array of ``Product`` objects.

.. note::

    The code in the ``__construct()`` method is important because Doctrine
    requires the ``$products`` property to be an ``ArrayCollection`` object.
    This object looks and acts almost *exactly* like an array, but has some
    added flexibility. If this makes you uncomfortable, don't worry. Just
    imagine that it's an ``array`` and you'll be in good shape.

.. tip::

   The ``targetEntity`` value in the decorator used above can reference any entity
   with a valid namespace, not just entities defined in the same namespace. To
   relate to an entity defined in a different class or bundle, enter a full
   namespace as the ``targetEntity``.

Next, since each ``Product`` class can relate to exactly one ``Category``
object, you'll want to add a ``$category`` property to the ``Product`` class.
Noticed the singular form.::

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Entity/Product.php

        // ...
        class Product
        {
            // ...

            /**
             * @ORM\ManyToOne(targetEntity="Category", inversedBy="products")
             * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
             */
            private $category;
        }

    .. code-block:: yaml

        # src/AppBundle/Resources/config/doctrine/Product.orm.yml
        AppBundle\Entity\Product:
            type: entity
            # ...
            manyToOne:
                category:
                    targetEntity: Category
                    inversedBy: products
                    joinColumn:
                        name: category_id
                        referencedColumnName: id

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/doctrine/Product.orm.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://doctrine-project.org/schemas/orm/doctrine-mapping
                http://doctrine-project.org/schemas/orm/doctrine-mapping.xsd">

            <entity name="AppBundle\Entity\Product">
                <!-- ... -->
                <many-to-one
                    field="category"
                    target-entity="Category"
                    inversed-by="products"
                    join-column="category">

                    <join-column name="category_id" referenced-column-name="id" />
                </many-to-one>
            </entity>
        </doctrine-mapping>

Ignore the Doctrine metadata for a moment. You now have two classes - ``Category``
and ``Product`` with a natural one-to-many relationship. The ``Category``
class holds an array of ``Product`` objects and the ``Product`` object can
hold one ``Category`` object. In other words - you've built your classes
in a way that makes sense for your needs. The fact that the data needs to
be persisted to a database is always secondary.

Now, look at the metadata above the ``$category`` property on the ``Product``
class. The information here tells Doctrine that the related class is ``Category``
and that it should store the ``id`` of the category record on a ``category_id``
field that lives on the ``product`` table. In other words, the related ``Category``
object will be stored on the ``$category`` property, but behind the scenes,
Doctrine will persist this relationship by storing the category's id value
on a ``category_id`` column of the ``product`` table.

.. image:: /images/book/doctrine_image_2.png
   :align: center

The metadata above the ``$products`` property of the ``Category`` object
is less important, and simply tells Doctrine to look at the ``Product.category``
property to figure out how the relationship is mapped.

Before you continue, be sure to tell Doctrine to add the new ``category``
table, and ``product.category_id`` column, and new foreign key:

.. code-block:: bash

    $ php app/console doctrine:schema:update --force

.. note::

    This command should only be used during development. For a more robust
    method of systematically updating your production database, read about
    `migrations`_.

Finally, now that you've added a new property to both the ``Category`` and
``Product`` classes, tell Doctrine to generate the missing getter and setter
methods for you:

.. code-block:: bash

    $ php app/console doctrine:generate:entities AppBundle

.. index::
    single: Doctrine; Saving related entities

Saving Related Entities
~~~~~~~~~~~~~~~~~~~~~~~

Now you can see this new code in action! Imagine you're inside a controller::

    // ...

    use AppBundle\Entity\Category;
    use AppBundle\Entity\Product;
    use Symfony\Component\HttpFoundation\Response;

    class DefaultController extends Controller
    {
        public function createProductAction()
        {
            $category = new Category();
            $category->setName('Computer Peripherals');

            $product = new Product();
            $product->setName('Keyboard');
            $product->setPrice(19.99);
            $product->setDescription('Ergonomic and stylish!');

            // relate this product to the category
            $product->setCategory($category);

            $em = $this->getDoctrine()->getManager();
            $em->persist($category);
            $em->persist($product);
            $em->flush();

            return new Response(
                'Saved new product with id: '.$product->getId()
                .' and new category with id: '.$category->getId()
            );
        }
    }

Now, a single row is added to both the ``category`` and ``product`` tables.
The ``product.category_id`` column for the new product is set to whatever
the ``id`` is of the new category. Doctrine manages the persistence of this
relationship for you.

Notice that ``persist()`` method is called twice. Recall that this method
tells Doctrine to manage or "watch" an object. In this case, since we haven't
fetch objects from Doctrine so that they would be already managed but created
new ones, we need to tell Doctrine to manage this new object.

.. index::
    single: Doctrine; Fetching related entities

Fetching Related Objects
~~~~~~~~~~~~~~~~~~~~~~~~

When you need to fetch associated objects, your workflow looks just like it
did before. First, fetch a ``$product`` object and then access its related
``Category`` object::

    public function showAction($productId)
    {
        $product = $this->getDoctrine()
            ->getRepository('AppBundle:Product')
            ->find($productId);

        $category = $product->getCategory();
        $categoryName = $category->getName();

        // ...
    }

In this example, you first query for a ``Product`` object based on the product's
``id``. This issues a query for *just* the product data and hydrates the
``$product`` object with that data. Later, when you call
``$product->getCategory()`` the category data isn't actually retrieved until you
ask for it with ``$category->getName()``. At this point Doctrine silently makes
a second query to find the ``Category`` that's related to this ``Product``. It
prepares the ``$category`` object and returns it to you.

.. image:: /images/book/doctrine_image_3.png
   :align: center

What's important is the fact that you have easy access to the product's related
category, but the category data isn't actually retrieved until you ask for
the category (i.e. it's "lazily loaded").

You can also query in the other direction::

    public function showProductsAction($categoryId)
    {
        $category = $this->getDoctrine()
            ->getRepository('AppBundle:Category')
            ->find($categoryId);

        $products = $category->getProducts();

        // ...
    }

In this case, the same things occur: you first query out for a single ``Category``
object, and then Doctrine makes a second query to retrieve the related ``Product``
objects, but only once/if you ask for them (i.e. when you call ``->getProducts()``).
The ``$products`` variable is an array of all ``Product`` objects that relate
to the given ``Category`` object via their ``category_id`` value.

.. index::
    single: Doctrine; Relationships, lazy loading and proxy classes

Relationships, Lazy Loading and Proxy Classes
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

This "lazy loading" is possible because, when necessary, Doctrine returns
a "proxy" object in place of the true object. Look again at the above
example::

    $product = $this->getDoctrine()
        ->getRepository('AppBundle:Product')
        ->find($productId);

    $category = $product->getCategory();

    // prints "Proxies\AppBundleEntityCategoryProxy"
    var_dump(get_class($category));

This proxy object extends the true ``Category`` object, and looks and
acts exactly like it. The difference is that, by using a proxy object,
Doctrine can delay querying for the real ``Category`` data until you
actually need that data (e.g. until you call ``$category->getName()``).

The proxy classes are generated by Doctrine and stored in the cache directory.
And though you'll probably never even notice that your ``$category``
object is actually a proxy object, it's important to keep it in mind.

In the next section, when you retrieve the product and category data
all at once (via a *join*), Doctrine will return the *true* ``Category``
object, since nothing needs to be lazily loaded.

.. index::
    single: Doctrine; Joining Related Records

Joining Related Records
~~~~~~~~~~~~~~~~~~~~~~~

In the above examples, two queries were made - one for the original object
(e.g. a ``Category``) and one for the related object(s) (e.g. the ``Product``
objects).

.. tip::

    Remember that you can see all of the queries made during a request via
    the web debug toolbar.

Of course, if you know up front that you'll need to access both objects, you
can avoid the second query by issuing a join in the original query. Add the
following method to the ``ProductRepository`` class::

    // src/AppBundle/Entity/ProductRepository.php
    public function findOneByIdJoinedToCategory($productId)
    {
        $query = $this->getEntityManager()
            ->createQuery(
                'SELECT p, c FROM AppBundle:Product p
                JOIN p.category c
                WHERE p.id = :id'
            )->setParameter('id', $productId);

        try {
            return $query->getSingleResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return null;
        }
    }

Now, you can use this method in your controller to query for a ``Product``
object and its related ``Category`` with just one query::

    public function showAction($productId)
    {
        $product = $this->getDoctrine()
            ->getRepository('AppBundle:Product')
            ->findOneByIdJoinedToCategory($productId);

        $category = $product->getCategory();

        // ...
    }

.. index::
    single: Doctrine; More information on relationships/associations

More Information on Relationships/Associations
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

This section has been an introduction to one common type of entity relationship,
the one-to-many relationship. For more advanced details and examples of how
to use other types of relations (e.g. one-to-one, many-to-many), see
Doctrine's `Association Mapping documentation`_.

.. note::

    If you're using annotations, you'll need to prepend all annotations with
    ``ORM\`` (e.g. ``ORM\OneToMany``), which is not reflected in Doctrine's
    documentation. You'll also need to include the ``use Doctrine\ORM\Mapping as ORM;``
    statement, which *imports* the ``ORM`` annotations prefix.

.. index::
    single: Doctrine; Lifecycle callbacks

Lifecycle Callbacks
-------------------

Sometimes, you need to perform an action right before or after an entity
is inserted, updated, or deleted. These types of actions are known as "lifecycle"
callbacks, as they're callback methods that you need to execute during different
stages of the lifecycle of an entity (e.g. the entity is inserted, updated,
deleted, etc).

If you're using annotations for your metadata, start by enabling the lifecycle
callbacks. This is not necessary if you're using YAML or XML for your mapping:

.. code-block:: php-annotations

    /**
     * @ORM\Entity()
     * @ORM\HasLifecycleCallbacks()
     */
    class Product
    {
        // ...
    }

Now, you can tell Doctrine to execute a method on any of the available lifecycle
events. For example, suppose you want to set a ``createdAt`` date column to
the current date, only when the entity is first persisted (i.e. inserted):

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Entity/Product.php

        /**
         * @ORM\PrePersist
         */
        public function setCreatedAtValue()
        {
            $this->createdAt = new \DateTime();
        }

    .. code-block:: yaml

        # src/AppBundle/Resources/config/doctrine/Product.orm.yml
        AppBundle\Entity\Product:
            type: entity
            # ...
            lifecycleCallbacks:
                prePersist: [setCreatedAtValue]

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/doctrine/Product.orm.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://doctrine-project.org/schemas/orm/doctrine-mapping
                http://doctrine-project.org/schemas/orm/doctrine-mapping.xsd">

            <entity name="AppBundle\Entity\Product">
                <!-- ... -->
                <lifecycle-callbacks>
                    <lifecycle-callback type="prePersist" method="setCreatedAtValue" />
                </lifecycle-callbacks>
            </entity>
        </doctrine-mapping>

.. note::

    The above example assumes that you've created and mapped a ``createdAt``
    property (not shown here).

Now, right before the entity is first persisted, Doctrine will automatically
call this method and the ``createdAt`` field will be set to the current date.

There are several other lifecycle events that you can hook into. For more
information on other lifecycle events and lifecycle callbacks in general, see
Doctrine's `Lifecycle Events documentation`_.

.. sidebar:: Lifecycle Callbacks and Event Listeners

    Notice that the ``setCreatedAtValue()`` method receives no arguments. This
    is always the case for lifecycle callbacks and is intentional: lifecycle
    callbacks should be simple methods that are concerned with internally
    transforming data in the entity (e.g. setting a created/updated field,
    generating a slug value).

    If you need to do some heavier lifting - like performing logging or sending
    an email - you should register an external class as an event listener
    or subscriber and give it access to whatever resources you need. For
    more information, see cookbook article
    :doc:`/cookbook/doctrine/event_listeners_subscribers`.

Summary
-------

With Doctrine, you can focus on your objects and how they're used in your
application and worry about database persistence second. This is because
Doctrine allows you to use any PHP object to hold your data and relies on
mapping metadata information to map an object's data to a particular database
table.

And even though Doctrine revolves around a simple concept, it's incredibly
powerful, allowing you to create complex queries and subscribe to events
that allow you to take different actions as objects go through their persistence
lifecycle.

Learn more from the Cookbook
----------------------------

* :doc:`/cookbook/doctrine/reverse_engineering`
* :doc:`/cookbook/doctrine/multiple_entity_managers`
* :doc:`/cookbook/doctrine/console`
* :doc:`/cookbook/doctrine/event_listeners_subscribers`
* :doc:`/cookbook/doctrine/file_uploads`
* :doc:`/cookbook/doctrine/common_extensions`
* :doc:`/cookbook/doctrine/registration_form`
* :doc:`/cookbook/doctrine/resolve_target_entity`
* :doc:`/cookbook/doctrine/dbal`
* :doc:`/cookbook/doctrine/pdo_session_storage`
* :doc:`/cookbook/doctrine/custom_dql_functions`
* :doc:`/cookbook/doctrine/mapping_model_classes`


.. _`ORM`: https://en.wikipedia.org/wiki/Object-relational_mapping
.. _`Doctrine`: http://www.doctrine-project.org/
.. _`MongoDB`: https://www.mongodb.org/
.. _`DoctrineMongoDBBundle`: https://symfony.com/doc/current/bundles/DoctrineMongoDBBundle/index.html
.. _`newer utf8mb4 character set`: https://dev.mysql.com/doc/refman/5.5/en/charset-unicode-utf8mb4.html
.. _`Basic Mapping documentation`: http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/basic-mapping.html
.. _`Reserved SQL keywords documentation`: http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/basic-mapping.html#quoting-reserved-words
.. _`Creating Classes for the Database`: http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/basic-mapping.html#creating-classes-for-the-database
.. _`Property Mapping`: http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/basic-mapping.html#property-mapping
.. _`Mapping Types documentation`: http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/basic-mapping.html#property-mapping
.. _`migrations`: https://symfony.com/doc/current/bundles/DoctrineMigrationsBundle/index.html
.. _`DoctrineFixturesBundle documentation`: https://symfony.com/doc/current/bundles/DoctrineFixturesBundle/index.html
.. _`FrameworkExtraBundle documentation`: https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/converters.html
.. _`Doctrine Query Language documentation`: http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/dql-doctrine-query-language.html
.. _`Query Builder documentation`: http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/query-builder.html
.. _`Association Mapping documentation`: http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/association-mapping.html
.. _`Lifecycle Events documentation`: http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/events.html#lifecycle-events