.. index::
   single: Doctrine

Databases and the Doctrine ORM
==============================

.. admonition:: Screencast
    :class: screencast

    Do you prefer video tutorials? Check out the `Doctrine screencast series`_.

Symfony doesn't provide a component to work with the database, but it *does* provide
tight integration with a third-party library called `Doctrine`_.

.. note::

    This article is all about using the Doctrine ORM. If you prefer to use raw
    database queries, see the ":doc:`/doctrine/dbal`" article instead.

    You can also persist data to `MongoDB`_ using Doctrine ODM library. See the
    "`DoctrineMongoDBBundle`_" documentation.

Installing Doctrine
-------------------

First, install Doctrine support via the ORM pack, as well as the MakerBundle,
which will help generate some code:

.. code-block:: terminal

    $ composer require symfony/orm-pack
    $ composer require --dev symfony/maker-bundle

Configuring the Database
~~~~~~~~~~~~~~~~~~~~~~~~

The database connection information is stored as an environment variable called
``DATABASE_URL``. For development, you can find and customize this inside ``.env``:

.. code-block:: text

    # .env (or override DATABASE_URL in .env.local to avoid committing your changes)

    # customize this line!
    DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name"

    # to use sqlite:
    # DATABASE_URL="sqlite:///%kernel.project_dir%/var/app.db"

.. caution::

    If the username, password, host or database name contain any character considered
    special in a URI (such as ``+``, ``@``, ``$``, ``#``, ``/``, ``:``, ``*``, ``!``),
    you must encode them. See `RFC 3986`_ for the full list of reserved characters or
    use the :phpfunction:`urlencode` function to encode them. In this case you need to
    remove the ``resolve:`` prefix in ``config/packages/doctrine.yaml`` to avoid errors:
    ``url: '%env(resolve:DATABASE_URL)%'``

Now that your connection parameters are setup, Doctrine can create the ``db_name``
database for you:

.. code-block:: terminal

    $ php bin/console doctrine:database:create

There are more options in ``config/packages/doctrine.yaml`` that you can configure,
including your ``server_version`` (e.g. 5.7 if you're using MySQL 5.7), which may
affect how Doctrine functions.

.. tip::

    There are many other Doctrine commands. Run ``php bin/console list doctrine``
    to see a full list.

Creating an Entity Class
------------------------

Suppose you're building an application where products need to be displayed.
Without even thinking about Doctrine or databases, you already know that
you need a ``Product`` object to represent those products.

.. _doctrine-adding-mapping:

You can use the ``make:entity`` command to create this class and any fields you
need. The command will ask you some questions - answer them like done below:

.. code-block:: terminal

    $ php bin/console make:entity

    Class name of the entity to create or update:
    > Product

    New property name (press <return> to stop adding fields):
    > name

    Field type (enter ? to see all types) [string]:
    > string

    Field length [255]:
    > 255

    Can this field be null in the database (nullable) (yes/no) [no]:
    > no

    New property name (press <return> to stop adding fields):
    > price

    Field type (enter ? to see all types) [string]:
    > integer

    Can this field be null in the database (nullable) (yes/no) [no]:
    > no

    New property name (press <return> to stop adding fields):
    >
    (press enter again to finish)

.. versionadded:: 1.3

    The interactive behavior of the ``make:entity`` command was introduced
    in MakerBundle 1.3.

Woh! You now have a new ``src/Entity/Product.php`` file::

    // src/Entity/Product.php
    namespace App\Entity;

    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity(repositoryClass="App\Repository\ProductRepository")
     */
    class Product
    {
        /**
         * @ORM\Id
         * @ORM\GeneratedValue
         * @ORM\Column(type="integer")
         */
        private $id;

        /**
         * @ORM\Column(type="string", length=255)
         */
        private $name;

        /**
         * @ORM\Column(type="integer")
         */
        private $price;

        public function getId()
        {
            return $this->id;
        }

        // ... getter and setter methods
    }

.. note::

    Confused why the price is an integer? Don't worry: this is just an example.
    But, storing prices as integers (e.g. 100 = $1 USD) can avoid rounding issues.

.. caution::

    There is a `limit of 767 bytes for the index key prefix`_ when using
    InnoDB tables in MySQL 5.6 and earlier versions. String columns with 255
    character length and ``utf8mb4`` encoding surpass that limit. This means
    that any column of type ``string`` and ``unique=true`` must set its
    maximum ``length`` to ``190``. Otherwise, you'll see this error:
    *"[PDOException] SQLSTATE[42000]: Syntax error or access violation:
    1071 Specified key was too long; max key length is 767 bytes"*.

This class is called an "entity". And soon, you'll be able to save and query Product
objects to a ``product`` table in your database. Each property in the ``Product``
entity can be mapped to a column in that table. This is usually done with annotations:
the ``@ORM\...`` comments that you see above each property:

.. image:: /_images/doctrine/mapping_single_entity.png
   :align: center

The ``make:entity`` command is a tool to make life easier. But this is *your* code:
add/remove fields, add/remove methods or update configuration.

Doctrine supports a wide variety of field types, each with their own options.
To see a full list, check out `Doctrine's Mapping Types documentation`_.
If you want to use XML instead of annotations, add ``type: xml`` and
``dir: '%kernel.project_dir%/config/doctrine'`` to the entity mappings in your
``config/packages/doctrine.yaml`` file.

.. caution::

    Be careful not to use reserved SQL keywords as your table or column names
    (e.g. ``GROUP`` or ``USER``). See Doctrine's `Reserved SQL keywords documentation`_
    for details on how to escape these. Or, change the table name with
    ``@ORM\Table(name="groups")`` above the class or configure the column name with
    the ``name="group_name"`` option.

.. _doctrine-creating-the-database-tables-schema:

Migrations: Creating the Database Tables/Schema
-----------------------------------------------

The ``Product`` class is fully-configured and ready to save to a ``product`` table.
If you just defined this class, your database doesn't actually have the ``product``
table yet. To add it, you can leverage the `DoctrineMigrationsBundle`_, which is
already installed:

.. code-block:: terminal

    $ php bin/console make:migration

If everything worked, you should see something like this:

    SUCCESS!

    Next: Review the new migration "src/Migrations/Version20180207231217.php"
    Then: Run the migration with php bin/console doctrine:migrations:migrate

If you open this file, it contains the SQL needed to update your database! To run
that SQL, execute your migrations:

.. code-block:: terminal

    $ php bin/console doctrine:migrations:migrate

This command executes all migration files that have not already been run against
your database. You should run this command on production when you deploy to keep
your production database up-to-date.

.. _doctrine-add-more-fields:

Migrations & Adding more Fields
-------------------------------

But what if you need to add a new field property to ``Product``, like a ``description``?
You can edit the class to add the new property. But, you can also use ``make:entity``
again:

.. code-block:: terminal

    $ php bin/console make:entity

    Class name of the entity to create or update
    > Product

    New property name (press <return> to stop adding fields):
    > description

    Field type (enter ? to see all types) [string]:
    > text

    Can this field be null in the database (nullable) (yes/no) [no]:
    > no

    New property name (press <return> to stop adding fields):
    >
    (press enter again to finish)

This adds the new ``description`` property and ``getDescription()`` and ``setDescription()``
methods:

.. code-block:: diff

    // src/Entity/Product.php
    // ...

    class Product
    {
        // ...

    +     /**
    +      * @ORM\Column(type="text")
    +      */
    +     private $description;

        // getDescription() & setDescription() were also added
    }

The new property is mapped, but it doesn't exist yet in the ``product`` table. No
problem! Generate a new migration:

.. code-block:: terminal

    $ php bin/console make:migration

This time, the SQL in the generated file will look like this:

.. code-block:: sql

    ALTER TABLE product ADD description LONGTEXT NOT NULL

The migration system is *smart*. It compares all of your entities with the current
state of the database and generates the SQL needed to synchronize them! Like
before, execute your migrations:

.. code-block:: terminal

    $ php bin/console doctrine:migrations:migrate

This will only execute the *one* new migration file, because DoctrineMigrationsBundle
knows that the first migration was already executed earlier. Behind the scenes, it
manages a ``migration_versions`` table to track this.

Each time you make a change to your schema, run these two commands to generate the
migration and then execute it. Be sure to commit the migration files and execute
them when you deploy.

.. _doctrine-generating-getters-and-setters:

.. tip::

    If you prefer to add new properties manually, the ``make:entity`` command can
    generate the getter & setter methods for you:

    .. code-block:: terminal

        $ php bin/console make:entity --regenerate

    If you make some changes and want to regenerate *all* getter/setter methods,
    also pass ``--overwrite``.

Persisting Objects to the Database
----------------------------------

It's time to save a ``Product`` object to the database! Let's create a new controller
to experiment:

.. code-block:: terminal

    $ php bin/console make:controller ProductController

Inside the controller, you can create a new ``Product`` object, set data on it,
and save it::

    // src/Controller/ProductController.php
    namespace App\Controller;

    // ...
    use Symfony\Component\HttpFoundation\Response;

    use App\Entity\Product;

    class ProductController extends AbstractController
    {
        /**
         * @Route("/product", name="product")
         */
        public function index()
        {
            // you can fetch the EntityManager via $this->getDoctrine()
            // or you can add an argument to your action: index(EntityManagerInterface $entityManager)
            $entityManager = $this->getDoctrine()->getManager();

            $product = new Product();
            $product->setName('Keyboard');
            $product->setPrice(1999);
            $product->setDescription('Ergonomic and stylish!');

            // tell Doctrine you want to (eventually) save the Product (no queries yet)
            $entityManager->persist($product);

            // actually executes the queries (i.e. the INSERT query)
            $entityManager->flush();

            return new Response('Saved new product with id '.$product->getId());
        }
    }

Try it out!

    http://localhost:8000/product

Congratulations! You just created your first row in the ``product`` table. To prove it,
you can query the database directly:

.. code-block:: terminal

    $ php bin/console doctrine:query:sql 'SELECT * FROM product'

    # on Windows systems not using Powershell, run this command instead:
    # php bin/console doctrine:query:sql "SELECT * FROM product"

Take a look at the previous example in more detail:

.. _doctrine-entity-manager:

* **line 18** The ``$this->getDoctrine()->getManager()`` method gets Doctrine's
  *entity manager* object, which is the most important object in Doctrine. It's
  responsible for saving objects to, and fetching objects from, the database.

* **lines 20-23** In this section, you instantiate and work with the ``$product``
  object like any other normal PHP object.

* **line 26** The ``persist($product)`` call tells Doctrine to "manage" the
  ``$product`` object. This does **not** cause a query to be made to the database.

* **line 29** When the ``flush()`` method is called, Doctrine looks through
  all of the objects that it's managing to see if they need to be persisted
  to the database. In this example, the ``$product`` object's data doesn't
  exist in the database, so the entity manager executes an ``INSERT`` query,
  creating a new row in the ``product`` table.

.. note::

    If the ``flush()`` call fails, a ``Doctrine\ORM\ORMException`` exception
    is thrown. See `Transactions and Concurrency`_.

Whether you're creating or updating objects, the workflow is always the same: Doctrine
is smart enough to know if it should INSERT or UPDATE your entity.

Fetching Objects from the Database
----------------------------------

Fetching an object back out of the database is even easier. Suppose you want to
be able to go to ``/product/1`` to see your new product::

    // src/Controller/ProductController.php
    // ...

    /**
     * @Route("/product/{id}", name="product_show")
     */
    public function show($id)
    {
        $product = $this->getDoctrine()
            ->getRepository(Product::class)
            ->find($id);

        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }

        return new Response('Check out this great product: '.$product->getName());

        // or render a template
        // in the template, print things with {{ product.name }}
        // return $this->render('product/show.html.twig', ['product' => $product]);
    }

Try it out!

    http://localhost:8000/product/1

When you query for a particular type of object, you always use what's known
as its "repository". You can think of a repository as a PHP class whose only
job is to help you fetch entities of a certain class.

Once you have a repository object, you have many helper methods::

    $repository = $this->getDoctrine()->getRepository(Product::class);

    // look for a single Product by its primary key (usually "id")
    $product = $repository->find($id);

    // look for a single Product by name
    $product = $repository->findOneBy(['name' => 'Keyboard']);
    // or find by name and price
    $product = $repository->findOneBy([
        'name' => 'Keyboard',
        'price' => 1999,
    ]);

    // look for multiple Product objects matching the name, ordered by price
    $products = $repository->findBy(
        ['name' => 'Keyboard'],
        ['price' => 'ASC']
    );

    // look for *all* Product objects
    $products = $repository->findAll();

You can also add *custom* methods for more complex queries! More on that later in
the :ref:`doctrine-queries` section.

.. tip::

    When rendering an HTML page, the web debug toolbar at the bottom of the page
    will display the number of queries and the time it took to execute them:

    .. image:: /_images/doctrine/doctrine_web_debug_toolbar.png
       :align: center
       :class: with-browser

    If the number of database queries is too high, the icon will turn yellow to
    indicate that something may not be correct. Click on the icon to open the
    Symfony Profiler and see the exact queries that were executed. If you don't
    see the web debug toolbar, try running ``composer require --dev symfony/profiler-pack``
    to install it.

Automatically Fetching Objects (ParamConverter)
-----------------------------------------------

In many cases, you can use the `SensioFrameworkExtraBundle`_ to do the query
for you automatically! First, install the bundle in case you don't have it:

.. code-block:: terminal

    $ composer require sensio/framework-extra-bundle

Now, simplify your controller::

    // src/Controller/ProductController.php

    use App\Entity\Product;
    // ...

    /**
     * @Route("/product/{id}", name="product_show")
     */
    public function show(Product $product)
    {
        // use the Product!
        // ...
    }

That's it! The bundle uses the ``{id}`` from the route to query for the ``Product``
by the ``id`` column. If it's not found, a 404 page is generated.

There are many more options you can use. Read more about the `ParamConverter`_.

Updating an Object
------------------

Once you've fetched an object from Doctrine, you interact with it the same as
with any PHP model::

    /**
     * @Route("/product/edit/{id}")
     */
    public function update($id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $product = $entityManager->getRepository(Product::class)->find($id);

        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }

        $product->setName('New product name!');
        $entityManager->flush();

        return $this->redirectToRoute('product_show', [
            'id' => $product->getId()
        ]);
    }

Using Doctrine to edit an existing product consists of three steps:

#. fetching the object from Doctrine;
#. modifying the object;
#. calling ``flush()`` on the entity manager.

You *can* call ``$entityManager->persist($product)``, but it isn't necessary:
Doctrine is already "watching" your object for changes.

Deleting an Object
------------------

Deleting an object is very similar, but requires a call to the ``remove()``
method of the entity manager::

    $entityManager->remove($product);
    $entityManager->flush();

As you might expect, the ``remove()`` method notifies Doctrine that you'd
like to remove the given object from the database. The ``DELETE`` query isn't
actually executed until the ``flush()`` method is called.

.. _doctrine-queries:

Querying for Objects: The Repository
------------------------------------

You've already seen how the repository object allows you to run basic queries
without any work::

    // from inside a controller
    $repository = $this->getDoctrine()->getRepository(Product::class);

    $product = $repository->find($id);

But what if you need a more complex query? When you generated your entity with
``make:entity``, the command *also* generated a ``ProductRepository`` class::

    // src/Repository/ProductRepository.php
    namespace App\Repository;

    use App\Entity\Product;
    use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
    use Symfony\Bridge\Doctrine\RegistryInterface;

    class ProductRepository extends ServiceEntityRepository
    {
        public function __construct(RegistryInterface $registry)
        {
            parent::__construct($registry, Product::class);
        }
    }

When you fetch your repository (i.e. ``->getRepository(Product::class)``), it is
*actually* an instance of *this* object! This is because of the ``repositoryClass``
config that was generated at the top of your ``Product`` entity class.

Suppose you want to query for all Product objects greater than a certain price. Add
a new method for this to your repository::

    // src/Repository/ProductRepository.php

    // ...
    class ProductRepository extends ServiceEntityRepository
    {
        public function __construct(RegistryInterface $registry)
        {
            parent::__construct($registry, Product::class);
        }

        /**
         * @param $price
         * @return Product[]
         */
        public function findAllGreaterThanPrice($price): array
        {
            // automatically knows to select Products
            // the "p" is an alias you'll use in the rest of the query
            $qb = $this->createQueryBuilder('p')
                ->andWhere('p.price > :price')
                ->setParameter('price', $price)
                ->orderBy('p.price', 'ASC')
                ->getQuery();

            return $qb->execute();

            // to get just one result:
            // $product = $qb->setMaxResults(1)->getOneOrNullResult();
        }
    }

This uses Doctrine's `Query Builder`_: a very powerful and user-friendly way to
write custom queries. Now, you can call this method on the repository::

    // from inside a controller
    $minPrice = 1000;

    $products = $this->getDoctrine()
        ->getRepository(Product::class)
        ->findAllGreaterThanPrice($minPrice);

    // ...

If you're in a :ref:`services-constructor-injection`, you can type-hint the
``ProductRepository`` class and inject it like normal.

For more details, see the `Query Builder`_ Documentation from Doctrine.

Querying with DQL or SQL
------------------------

In addition to the query builder, you can also query with `Doctrine Query Language`_::

    // src/Repository/ProductRepository.php
    // ...

    public function findAllGreaterThanPrice($price): array
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT p
            FROM App\Entity\Product p
            WHERE p.price > :price
            ORDER BY p.price ASC'
        )->setParameter('price', $price);

        // returns an array of Product objects
        return $query->execute();
    }

Or directly with SQL if you need to::

    // src/Repository/ProductRepository.php
    // ...

    public function findAllGreaterThanPrice($price): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT * FROM product p
            WHERE p.price > :price
            ORDER BY p.price ASC
            ';
        $stmt = $conn->prepare($sql);
        $stmt->execute(['price' => $price]);

        // returns an array of arrays (i.e. a raw data set)
        return $stmt->fetchAll();
    }

With SQL, you will get back raw data, not objects (unless you use the `NativeQuery`_
functionality).

Configuration
-------------

See the :doc:`Doctrine config reference </reference/configuration/doctrine>`.

Relationships and Associations
------------------------------

Doctrine provides all the functionality you need to manage database relationships
(also known as associations), including ManyToOne, OneToMany, OneToOne and ManyToMany
relationships.

For info, see :doc:`/doctrine/associations`.

.. _doctrine-fixtures:

Dummy Data Fixtures
-------------------

Doctrine provides a library that allows you to programmatically load testing
data into your project (i.e. "fixture data"). Install it with:

.. code-block:: terminal

    $ composer require --dev doctrine/doctrine-fixtures-bundle

Then, use the ``make:fixtures`` command to generate an empty fixture class:

.. code-block:: terminal

    $ php bin/console make:fixtures

    The class name of the fixtures to create (e.g. AppFixtures):
    > ProductFixture

Customize the new class to load ``Product`` objects into Doctrine::

    // src/DataFixtures/ProductFixture.php
    namespace App\DataFixtures;

    use Doctrine\Bundle\FixturesBundle\Fixture;
    use Doctrine\Common\Persistence\ObjectManager;

    class ProductFixture extends Fixture
    {
        public function load(ObjectManager $manager)
        {
            $product = new Product();
            $product->setName('Priceless widget!');
            $product->setPrice(14.50);
            $product->setDescription('Ok, I guess it *does* have a price');
            $manager->persist($product);

            // add more products

            $manager->flush();
        }
    }

Empty the database and reload *all* the fixture classes with:

.. code-block:: terminal

    $ php bin/console doctrine:fixtures:load

For information, see the "`DoctrineFixturesBundle`_" documentation.

Learn more
----------

.. toctree::
    :maxdepth: 1

    doctrine/associations
    doctrine/common_extensions
    doctrine/lifecycle_callbacks
    doctrine/event_listeners_subscribers
    doctrine/registration_form
    doctrine/custom_dql_functions
    doctrine/dbal
    doctrine/multiple_entity_managers
    doctrine/pdo_session_storage
    doctrine/mongodb_session_storage
    doctrine/resolve_target_entity
    doctrine/reverse_engineering

* `DoctrineFixturesBundle`_

.. _`Doctrine`: http://www.doctrine-project.org/
.. _`RFC 3986`: https://www.ietf.org/rfc/rfc3986.txt
.. _`MongoDB`: https://www.mongodb.org/
.. _`Doctrine's Mapping Types documentation`: http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/basic-mapping.html
.. _`Query Builder`: http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/query-builder.html
.. _`Doctrine Query Language`: http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/dql-doctrine-query-language.html
.. _`Mapping Types Documentation`: http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/basic-mapping.html#property-mapping
.. _`Reserved SQL keywords documentation`: http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/basic-mapping.html#quoting-reserved-words
.. _`DoctrineMongoDBBundle`: https://symfony.com/doc/current/bundles/DoctrineMongoDBBundle/index.html
.. _`DoctrineFixturesBundle`: https://symfony.com/doc/current/bundles/DoctrineFixturesBundle/index.html
.. _`Transactions and Concurrency`: http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/transactions-and-concurrency.html
.. _`DoctrineMigrationsBundle`: https://github.com/doctrine/DoctrineMigrationsBundle
.. _`NativeQuery`: http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/native-sql.html
.. _`SensioFrameworkExtraBundle`: http://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/index.html
.. _`ParamConverter`: http://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/converters.html
.. _`limit of 767 bytes for the index key prefix`: https://dev.mysql.com/doc/refman/5.6/en/innodb-restrictions.html
.. _`Doctrine screencast series`: https://symfonycasts.com/screencast/symfony-doctrine
