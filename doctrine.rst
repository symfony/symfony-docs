.. index::
   single: Doctrine

Databases and the Doctrine ORM
==============================

One of the most common and challenging tasks for any application
involves persisting and reading information to and from a database. Although
the Symfony Framework doesn't integrate any component to work with databases,
it provides tight integration with a third-party library called `Doctrine`_.
Doctrine's sole goal is to give you powerful tools to make database interactions
easy and flexible.

In this chapter, you'll learn how to start leveraging Doctrine in your Symfony projects
to give you rich database interactions.

.. note::

    Doctrine is totally decoupled from Symfony and using it is optional.
    This chapter is all about the Doctrine ORM, which aims to let you map
    objects to a relational database (such as *MySQL*, *PostgreSQL* or
    *Microsoft SQL*). If you prefer to use raw database queries, this is
    easy, and explained in the ":doc:`/doctrine/dbal`" article.

    You can also persist data to `MongoDB`_ using Doctrine ODM library. For
    more information, read the "`DoctrineMongoDBBundle`_"
    documentation.

A Simple Example: A Product
---------------------------

The easiest way to understand how Doctrine works is to see it in action.
In this section, you'll configure your database, create a ``Product`` object,
persist it to the database and fetch it back out.

Configuring the Database
~~~~~~~~~~~~~~~~~~~~~~~~

Before you really begin, you'll need to configure your database connection
information. By convention, this information is usually configured in an
``app/config/parameters.yml`` file:

.. code-block:: yaml

    # app/config/parameters.yml
    parameters:
        database_host:     localhost
        database_name:     test_project
        database_user:     root
        database_password: password

    # ...

.. note::

    Defining the configuration via ``parameters.yml`` is just a convention.
    The parameters defined in that file are referenced by the main configuration
    file when setting up Doctrine:

    .. configuration-block::

        .. code-block:: yaml

            # app/config/config.yml
            doctrine:
                dbal:
                    driver:   pdo_mysql
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
                        driver="pdo_mysql"
                        host="%database_host%"
                        dbname="%database_name%"
                        user="%database_user%"
                        password="%database_password%" />
                </doctrine:config>
            </container>

        .. code-block:: php

            // app/config/config.php
            $container->loadFromExtension('doctrine', array(
                'dbal' => array(
                    'driver'   => 'pdo_mysql',
                    'host'     => '%database_host%',
                    'dbname'   => '%database_name%',
                    'user'     => '%database_user%',
                    'password' => '%database_password%',
                ),
            ));

    By separating the database information into a separate file, you can
    easily keep different versions of the file on each server. You can also
    easily store database configuration (or any sensitive information) outside
    of your project, like inside your Apache configuration, for example. For
    more information, see :doc:`/configuration/external_parameters`.

Now that Doctrine can connect to your database, the following command
can automatically generate an empty ``test_project`` database for you:

.. code-block:: terminal

    $ php bin/console doctrine:database:create

.. sidebar:: Setting up the Database to be UTF8

    One mistake even seasoned developers make when starting a Symfony project
    is forgetting to set up default charset and collation on their database,
    ending up with latin type collations, which are default for most databases.
    They might even remember to do it the very first time, but forget that
    it's all gone after running a relatively common command during development:

    .. code-block:: terminal

        $ php bin/console doctrine:database:drop --force
        $ php bin/console doctrine:database:create

    Setting UTF8 defaults for MySQL is as simple as adding a few lines to
    your configuration file  (typically ``my.cnf``):

    .. code-block:: ini

        [mysqld]
        # Version 5.5.3 introduced "utf8mb4", which is recommended
        collation-server     = utf8mb4_unicode_ci # Replaces utf8_unicode_ci
        character-set-server = utf8mb4            # Replaces utf8

    You can also change the defaults for Doctrine so that the generated SQL
    uses the correct character set.

    .. configuration-block::

        .. code-block:: yaml

            # app/config/config.yml
            doctrine:
                dbal:
                    charset: utf8mb4
                    default_table_options:
                        charset: utf8mb4
                        collate: utf8mb4_unicode_ci

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
                        charset="utf8mb4">
                            <doctrine:default-table-option name="charset">utf8mb4</doctrine:default-table-option>
                            <doctrine:default-table-option name="collate">utf8mb4_unicode_ci</doctrine:default-table-option>
                    </doctrine:dbal>
                </doctrine:config>
            </container>

        .. code-block:: php

            // app/config/config.php
            $configuration->loadFromExtension('doctrine', array(
                'dbal' => array(
                    'charset' => 'utf8mb4',
                    'default_table_options' => array(
                        'charset' => 'utf8mb4'
                        'collate' => 'utf8mb4_unicode_ci'
                    )
                ),
            ));

    We recommend against MySQL's ``utf8`` character set, since it does not
    support 4-byte unicode characters, and strings containing them will be
    truncated. This is fixed by the `newer utf8mb4 character set`_.

.. note::

    If you want to use SQLite as your database, you need to set the path
    where your database file should be stored:

    .. configuration-block::

        .. code-block:: yaml

            # app/config/config.yml
            doctrine:
                dbal:
                    driver: pdo_sqlite
                    path: '%kernel.project_dir%/app/sqlite.db'
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
                        path="%kernel.project_dir%/app/sqlite.db"
                        charset="UTF-8" />
                </doctrine:config>
            </container>

        .. code-block:: php

            // app/config/config.php
            $container->loadFromExtension('doctrine', array(
                'dbal' => array(
                    'driver'  => 'pdo_sqlite',
                    'path'    => '%kernel.project_dir%/app/sqlite.db',
                    'charset' => 'UTF-8',
                ),
            ));

Creating an Entity Class
~~~~~~~~~~~~~~~~~~~~~~~~

Suppose you're building an application where products need to be displayed.
Without even thinking about Doctrine or databases, you already know that
you need a ``Product`` object to represent those products. Create this class
inside the ``Entity`` directory of your AppBundle::

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
    simple entity classes for you. This will ask you interactive questions
    to help you build any entity:

    .. code-block:: terminal

        $ php bin/console doctrine:generate:entity

.. index::
    single: Doctrine; Adding mapping metadata

.. _doctrine-adding-mapping:

Add Mapping Information
~~~~~~~~~~~~~~~~~~~~~~~

Doctrine allows you to work with databases in a much more interesting way
than just fetching rows of scalar data into an array. Instead, Doctrine
allows you to fetch entire *objects* out of the database, and to persist
entire objects to the database. For Doctrine to be able to do this, you
must *map* your database tables to specific PHP classes, and the columns
on those tables must be mapped to specific properties on their corresponding
PHP classes.

.. image:: /_images/doctrine/mapping_single_entity.png
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

.. note::

    A bundle can accept only one metadata definition format. For example, it's
    not possible to mix YAML metadata definitions with annotated PHP entity
    class definitions.

.. tip::

    The table name is optional and if omitted, will be determined automatically
    based on the name of the entity class.

Doctrine allows you to choose from a wide variety of different field types,
each with their own options. For information on the available field types,
see the :ref:`doctrine-field-types` section.

.. seealso::

    You can also check out Doctrine's `Basic Mapping Documentation`_ for
    all details about mapping information. If you use annotations, you'll
    need to prepend all annotations with ``ORM\`` (e.g. ``ORM\Column(...)``),
    which is not shown in Doctrine's documentation. You'll also need to include
    the ``use Doctrine\ORM\Mapping as ORM;`` statement, which *imports* the
    ``ORM`` annotations prefix.

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

.. tip::

    After creating your entities you should validate the mappings with the
    following command:

    .. code-block:: terminal

        $ php bin/console doctrine:schema:validate

.. _doctrine-generating-getters-and-setters:

Generating Getters and Setters
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Even though Doctrine now knows how to persist a ``Product`` object to the
database, the class itself isn't really useful yet. Since ``Product`` is just
a regular PHP class with ``private`` properties, you need to create ``public``
getter and setter methods (e.g. ``getName()``, ``setName($name)``) in order
to access its properties in the rest of your application's code. Fortunately,
the following command can generate these boilerplate methods automatically:

.. code-block:: terminal

    $ php bin/console doctrine:generate:entities AppBundle/Entity/Product

This command makes sure that all the getters and setters are generated
for the ``Product`` class. This is a safe command - you can run it over and
over again: it only generates getters and setters that don't exist (i.e. it
doesn't replace your existing methods).

.. caution::

    Keep in mind that Doctrine's entity generator produces simple getters/setters.
    You should review the generated methods and add any logic, if necessary,
    to suit the needs of your application.

.. sidebar:: More about ``doctrine:generate:entities``

    With the ``doctrine:generate:entities`` command you can:

    * generate getter and setter methods in entity classes;

    * generate repository classes on behalf of entities configured with the
      ``@ORM\Entity(repositoryClass="...")`` annotation;

    * generate the appropriate constructor for 1:n and n:m relations.

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

.. code-block:: terminal

    # generates all entities in the AppBundle
    $ php bin/console doctrine:generate:entities AppBundle

    # generates all entities of bundles in the Acme namespace
    $ php bin/console doctrine:generate:entities Acme

.. _doctrine-creating-the-database-tables-schema:

Creating the Database Tables/Schema
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You now have a usable ``Product`` class with mapping information so that
Doctrine knows exactly how to persist it. Of course, you don't yet have the
corresponding ``product`` table in your database. Fortunately, Doctrine can
automatically create all the database tables needed for every known entity
in your application. To do this, run:

.. code-block:: terminal

    $ php bin/console doctrine:schema:update --force

.. tip::

    Actually, this command is incredibly powerful. It compares what
    your database *should* look like (based on the mapping information of
    your entities) with how it *actually* looks, and executes the SQL statements
    needed to *update* the database schema to where it should be. In other
    words, if you add a new property with mapping metadata to ``Product``
    and run this command, it will execute the "ALTER TABLE" statement needed
    to add that new column to the existing ``product`` table.

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
    use Doctrine\ORM\EntityManagerInterface;
    use Doctrine\Common\Persistence\ManagerRegistry;

    public function createAction(EntityManagerInterface $em)
    {
        // or fetch the em via the container
        // $em = $this->get('doctrine')->getManager();

        $product = new Product();
        $product->setName('Keyboard');
        $product->setPrice(19.99);
        $product->setDescription('Ergonomic and stylish!');

        // tells Doctrine you want to (eventually) save the Product (no queries yet)
        $em->persist($product);

        // actually executes the queries (i.e. the INSERT query)
        $em->flush();

        return new Response('Saved new product with id '.$product->getId());
    }

    // if you have multiple entity managers, use the registry to fetch them
    public function editAction(ManagerRegistry $doctrine)
    {
        $em = $doctrine->getManager();
        $em2 = $doctrine->getManager('other_connection')
    }

.. note::

    If you're following along with this example, you'll need to create a
    route that points to this action to see it work.

Take a look at the previous example in more detail:

.. _doctrine-entity-manager:

* **line 10** The ``EntityManagerInterface`` type-hint tells Symfony to pass you Doctrine's
  *entity manager* object, which is the most important object in Doctrine. It's
  responsible for saving objects to, and fetching objects from, the database.

* **lines 15-18** In this section, you instantiate and work with the ``$product``
  object like any other normal PHP object.

* **line 21** The ``persist($product)`` call tells Doctrine to "manage" the
  ``$product`` object. This does **not** cause a query to be made to the database.

* **line 24** When the ``flush()`` method is called, Doctrine looks through
  all of the objects that it's managing to see if they need to be persisted
  to the database. In this example, the ``$product`` object's data doesn't
  exist in the database, so the entity manager executes an ``INSERT`` query,
  creating a new row in the ``product`` table.

.. note::

    In fact, since Doctrine is aware of all your managed entities, when you call
    the ``flush()`` method, it calculates an overall changeset and executes
    the queries in the correct order. It utilizes cached prepared statement to
    slightly improve the performance. For example, if you persist a total of 100
    ``Product`` objects and then subsequently call ``flush()``, Doctrine will
    execute 100 ``INSERT`` queries using a single prepared statement object.

.. note::

    If the ``flush()`` call fails, a ``Doctrine\ORM\ORMException`` exception
    is thrown. See `Transactions and Concurrency`_.

Whether creating or updating objects, the workflow is always the same. In
the next section, you'll see how Doctrine is smart enough to automatically
issue an ``UPDATE`` query if the entity already exists in the database.

.. tip::

    Doctrine provides a library that allows you to programmatically load testing
    data into your project (i.e. "fixture data"). For information, see
    the "`DoctrineFixturesBundle`_" documentation.

Fetching Objects from the Database
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Fetching an object back out of the database is even easier. For example,
suppose you've configured a route to display a specific ``Product`` based
on its ``id`` value::

    use Doctrine\ORM\EntityManagerInterface;

    public function showAction($productId, EntityManagerInterface $em)
    {
        $product = $em->getRepository('AppBundle:Product')
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

    $repository = $em->getRepository('AppBundle:Product');

.. note::

    The ``AppBundle:Product`` string is a shortcut you can use anywhere
    in Doctrine instead of the full class name of the entity (i.e. ``AppBundle\Entity\Product``).
    As long as your entity lives under the ``Entity`` namespace of your bundle,
    this will work.

Once you have a repository object, you can access all sorts of helpful methods::

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

.. note::

    Of course, you can also issue complex queries, which you'll learn more
    about in the :ref:`doctrine-queries` section.

You can also take advantage of the useful ``findBy()`` and ``findOneBy()`` methods
to easily fetch objects based on multiple conditions::

    $repository = $em->getRepository('AppBundle:Product');

    // query for a single product matching the given name and price
    $product = $repository->findOneBy(
        array('name' => 'Keyboard', 'price' => 19.99)
    );

    // query for multiple products matching the given name, ordered by price
    $products = $repository->findBy(
        array('name' => 'Keyboard'),
        array('price' => 'ASC')
    );

.. tip::

    When rendering a page requires to make some database calls, the web debug
    toolbar at the bottom of the page displays the number of queries and the
    time it took to execute them:

    .. image:: /_images/doctrine/doctrine_web_debug_toolbar.png
       :align: center
       :class: with-browser

    If the number of database queries is too high, the icon will turn yellow to
    indicate that something may not be correct. Click on the icon to open the
    Symfony Profiler and see the exact queries that were executed.

Updating an Object
~~~~~~~~~~~~~~~~~~

Once you've fetched an object from Doctrine, updating it is easy. Suppose
you have a route that maps a product id to an update action in a controller::

    use Doctrine\ORM\EntityManagerInterface;

    public function updateAction($productId, EntityManagerInterface $em)
    {
        $product = $em->getRepository('AppBundle:Product')->find($productId);

        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '.$productId
            );
        }

        $product->setName('New product name!');
        $em->flush();

        return $this->redirectToRoute('homepage');
    }

Updating an object involves just three steps:

#. fetching the object from Doctrine;
#. modifying the object;
#. calling ``flush()`` on the entity manager.

Notice that calling ``$em->persist($product)`` isn't necessary. Recall that
this method simply tells Doctrine to manage or "watch" the ``$product`` object.
In this case, since you fetched the ``$product`` object from Doctrine, it's
already managed.

Deleting an Object
~~~~~~~~~~~~~~~~~~

Deleting an object is very similar, but requires a call to the ``remove()``
method of the entity manager::

    $em->remove($product);
    $em->flush();

As you might expect, the ``remove()`` method notifies Doctrine that you'd
like to remove the given object from the database. The actual ``DELETE`` query,
however, isn't actually executed until the ``flush()`` method is called.

.. _doctrine-queries:

Querying for Objects
--------------------

You've already seen how the repository object allows you to run basic queries
without any work::

    $repository = $em->getRepository('AppBundle:Product');

    $product = $repository->find($productId);
    $product = $repository->findOneByName('Keyboard');

Of course, Doctrine also allows you to write more complex queries using the
Doctrine Query Language (DQL). DQL is similar to SQL except that you should
imagine that you're querying for one or more objects of an entity class (e.g. ``Product``)
instead of querying for rows on a table (e.g. ``product``).

When querying in Doctrine, you have two main options: writing pure DQL queries
or using Doctrine's Query Builder.

Querying for Objects with DQL
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Imagine that you want to query for products that cost more than ``19.99``,
ordered from least to most expensive. You can use DQL, Doctrine's native
SQL-like language, to construct a query for this scenario::

    $query = $em->createQuery(
        'SELECT p
        FROM AppBundle:Product p
        WHERE p.price > :price
        ORDER BY p.price ASC'
    )->setParameter('price', 19.99);

    $products = $query->getResult();

If you're comfortable with SQL, then DQL should feel very natural. The biggest
difference is that you need to think in terms of selecting PHP objects,
instead of rows in a database. For this reason, you select *from* the
``AppBundle:Product`` *entity* (an optional shortcut for the
``AppBundle\Entity\Product`` class) and then alias it as ``p``.

.. tip::

    Take note of the ``setParameter()`` method. When working with Doctrine,
    it's always a good idea to set any external values as "placeholders"
    (``:price`` in the example above) as it prevents SQL injection attacks.

The ``getResult()`` method returns an array of results. To get only one
result, you can use ``getOneOrNullResult()``::

    $product = $query->setMaxResults(1)->getOneOrNullResult();

The DQL syntax is incredibly powerful, allowing you to easily join between
entities (the topic of :doc:`relations </doctrine/associations>` will be
covered later), group, etc. For more information, see the official
`Doctrine Query Language`_ documentation.

Querying for Objects Using Doctrine's Query Builder
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Instead of writing a DQL string, you can use a helpful object called the
``QueryBuilder`` to build that string for you. This is useful when the actual query
depends on dynamic conditions, as your code soon becomes hard to read with
DQL as you start to concatenate strings::

    $repository = $em->getRepository('AppBundle:Product');

    // createQueryBuilder() automatically selects FROM AppBundle:Product
    // and aliases it to "p"
    $query = $repository->createQueryBuilder('p')
        ->where('p.price > :price')
        ->setParameter('price', '19.99')
        ->orderBy('p.price', 'ASC')
        ->getQuery();

    $products = $query->getResult();
    // to get just one result:
    // $product = $query->setMaxResults(1)->getOneOrNullResult();

The ``QueryBuilder`` object contains every method necessary to build your
query. By calling the ``getQuery()`` method, the query builder returns a
normal ``Query`` object, which can be used to get the result of the query.

For more information on Doctrine's Query Builder, consult Doctrine's
`Query Builder`_ documentation.

Organizing Custom Queries into Repository Classes
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

All the queries in the previous sections were written directly in your controller.
But for organization, Doctrine provides special repository classes that allow you
to keep all your query logic in one, central place.

see :doc:`/doctrine/repository` for info.

Configuration
-------------

Doctrine is highly configurable, though you probably won't ever need to worry
about most of its options. To find out more about configuring Doctrine, see
the Doctrine section of the :doc:`config reference </reference/configuration/doctrine>`.

.. _doctrine-field-types:

Doctrine Field Types Reference
------------------------------

Doctrine comes with numerous field types available. Each of these
maps a PHP data type to a specific column type in whatever database you're
using. For each field type, the ``Column`` can be configured further, setting
the ``length``, ``nullable`` behavior, ``name`` and other options. To see a
list of all available types and more information, see Doctrine's
`Mapping Types documentation`_.

Relationships and Associations
------------------------------

Doctrine provides all the functionality you need to manage database relationships
(also known as associations). For info, see :doc:`/doctrine/associations`.

Final Thoughts
--------------

With Doctrine, you can focus on your *objects* and how they're used in your
application and worry about database persistence second. This is because
Doctrine allows you to use any PHP object to hold your data and relies on
mapping metadata information to map an object's data to a particular database
table.

Doctrine has a lot more complex features to learn, like relationships, complex queries,
and event listeners.

Learn more
----------

.. toctree::
    :maxdepth: 1
    :glob:

    doctrine/*

* `DoctrineFixturesBundle`_
* `DoctrineMongoDBBundle`_

.. _`Doctrine`: http://www.doctrine-project.org/
.. _`MongoDB`: https://www.mongodb.org/
.. _`Basic Mapping Documentation`: http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/basic-mapping.html
.. _`Query Builder`: http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/query-builder.html
.. _`Doctrine Query Language`: http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/dql-doctrine-query-language.html
.. _`Mapping Types Documentation`: http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/basic-mapping.html#property-mapping
.. _`Property Mapping`: http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/basic-mapping.html#property-mapping
.. _`Reserved SQL keywords documentation`: http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/basic-mapping.html#quoting-reserved-words
.. _`Creating Classes for the Database`: http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/basic-mapping.html#creating-classes-for-the-database
.. _`DoctrineMongoDBBundle`: https://symfony.com/doc/current/bundles/DoctrineMongoDBBundle/index.html
.. _`migrations`: https://symfony.com/doc/current/bundles/DoctrineMigrationsBundle/index.html
.. _`DoctrineFixturesBundle`: https://symfony.com/doc/current/bundles/DoctrineFixturesBundle/index.html
.. _`FrameworkExtraBundle documentation`: https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/converters.html
.. _`newer utf8mb4 character set`: https://dev.mysql.com/doc/refman/5.5/en/charset-unicode-utf8mb4.html
.. _`Transactions and Concurrency`: http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/transactions-and-concurrency.html
