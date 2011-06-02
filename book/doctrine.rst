.. index::
   single: Forms

Databases and Doctrine ("The Model")
====================================

Let's face it, one of the most common and challenging tasks for any application
involves persisting and reading information to and from a database. Fortunately,
Symfony comes integrated with `Doctrine`_, a library whose sole goal is to
help you deal with data and databases. In this chapter, you'll learn the
basic philosophy behind Doctrine and how easy working with a database can
be.

A Simple Example: A Product
---------------------------

The easiest way to understand how Doctrine works is to see it in action.

.. note::

    If you want to follow along with the example in this chapter, create
    the ``AcmeStoreBundle`` via:
    
    .. code-block:: bash
    
        php app/console init:bundle "Acme\StoreBundle" src/

    Next, be sure that the new bundle is enabled in the kernel::
    
        // app/AppKernel.php
        
        public function registerBundles()
        {
            $bundles = array(
                // ...
                new Acme\StoreBundle\AcmeStoreBundle(),
            );
        }

Configuring the Database
~~~~~~~~~~~~~~~~~~~~~~~~

Before you really being, you'll need to configure your database connection
information. By convention, this information is usually configured in a
``app/config/parameters.ini`` file:

.. code-block:: ini

    ;app/config/parameters.ini
    [parameters]
        database_driver   = pdo_mysql
        database_host     = localhost
        database_name     = test_project
        database_user     = root
        database_password = password

.. note::

    Defining the configuration via ``parameters.ini`` is just a convention.
    The parameters defined in that file are referenced by the main config
    file to configure Doctrine:
    
    .. code-block:: yaml
    
        doctrine:
            dbal:
                driver:   %database_driver%
                host:     %database_host%
                dbname:   %database_name%
                user:     %database_user%
                password: %database_password%
    
    By separating the database information into a separate file, you can
    easily keep different version of the file on each server.

Now that Doctrine knows about your database, you can have Doctrine create
the database for you:

.. code-block:: bash

    php app/console doctrine:database:create

Creating an Entity Class
~~~~~~~~~~~~~~~~~~~~~~~~

Suppose you're building an application where products need to be displayed.
Without even thinking about Doctrine or databases, you already know that
you need a ``Product`` object to represent your products. Create this class
inside the ``Entity`` directory of your ``AcmeStoreBundle``::

    // src/Acme/StoreBundle/Entity/Product.php    
    namespace Acme\StoreBundle\Entity;

    class Product
    {
        private $name;

        private $price;

        private $description;
    }

The class - often called an "entity", meaning *a basic class that holds data*
- is simple and helps fulfill the business requirement of needing products
in your application. This class can't be persisted to a database yet - it's
just a simple PHP class.

2) Add Mapping Information
~~~~~~~~~~~~~~~~~~~~~~~~~~

Doctrine allows you to work with databases in a much more interesting way
than just fetching rows of column-based data into an array. Instead, Doctrine
allows you to persist entire *objects* to the database and fetch entire objects
out of the database. This works by mapping a database table to a PHP class
and the columns of that table to the properties of the PHP class:

    DIAGRAM here of the Product class on the left (looking like an object
    with visible name, price, description properties) and a "product" table
    on the right, with name, price and description columns. In the middle
    is Doctrine, which is handling a two-way street, transforming data in
    both directions.

For Doctrine to be able to do this, you just have to create "metadata", or
configuration that tells Doctrine exactly how the ``Product`` class and its
properties should be *mapped* to the database. This metadata can be specified
in a number of different formats including YAML, XML, PHP or right inside
the ``Product`` class via annotations:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Acme/StoreBundle/Entity/Product.php
        namespace Acme\StoreBundle\Entity;

        use Doctrine\ORM\Mapping as ORM;

        /**
         * @ORM\Entity
         * @ORM\Table(name="product")
         */
        class Product
        {
            /**
             * @ORM\Id
             * @ORM\Column(type="integer")
             * @ORM\GeneratedValue(strategy="AUTO")
             */
            protected $id;

            /**
             * @ORM\Column(type="string", length="100")
             */
            private $name;

            /**
             * @ORM\Column(type="decimal", scale="2")
             */
            private $price;

            /**
             * @ORM\Column(type="text")
             */
            private $description;
        }

.. tip::

    The table option is optional and if omitted, will be determined automatically
    based on the name of the entity class.

Doctrine allows you to choose from a wide variety of different field types,
each with their own options. For information on the available field types,
see the :ref:`book-doctrine-field-types` section.

Generating Getters and Setters
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Even though Doctrine knows how to persist a ``Product`` object to the database,
the class itself isn't really useful yet. Since ``Product`` is just a regular
PHP class, you need to create getter and setter methods (e.g. ``getName()``,
``setName()``) in order to access its properties. Fortunately, Doctrine can
do this for you by running:

.. code-block:: bash

    php app/console doctrine:generate:entities Acme

This task will look for every known entity (any PHP class with mapping Doctrine
mapping information) and make sure that all of its getters and setters are
generated. This is a safe task - you can run it over and over again: it
only generates getters and setters that don't exist (i.e. it doesn't replace
your existing methods).

Creating the Database Tables/Schema
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You now have a usable ``Product`` class with mapping information so that
Doctrine knows exactly how to persist it. Of course, you don't yet have the
``product`` table in your database. Fortunately, Doctrine can automatically
create all the database tables needed for every known entity in your application.
To do this, run:

.. code-block:: bash

    php app/console doctrine:schema:update --force

.. tip::

    Actually, this console task is incredibly powerful. It compares what
    your database *should* look like (based on the mapping information of
    your entities) with how it actually looks, and generates SQL statements
    that *update* it to where it needs to be. In other words, if you added
    a new field and mapping metadata to ``Product`` and then ran this task
    again, it would generate the statement needed to add *just* the new column.
    An even better way to take advantage of this functionality is via
    :doc:`migrations</cookbook/doctrine/migrations>`, which allows you to
    generate these SQL statements and store them into migration classes that
    can be run systematically on your production server in order to migrate
    your database schema. 

Your database now has a fully-functional ``product`` table with columns that
match the metadata you've specified.

Persisting Objects to the Database
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Now that you have a mapped ``Product`` entity and corresponding ``product``
table, you're ready to persist data to the database. From inside a controller,
this is pretty easy. Add the following method to the ``DefaultController``
of the bundle:

.. code-block:: php
    :linenos:

    // src/Acme/StoreBundle/Controller/DefaultController.php
    
    public function createAction()
    {
        $product = new Product();
        $product->setName('A Foo Bar');
        $product->setPrice('19.99');
        $product->setDescription('Lorem ipsum dolor');

        $em = $this->get('doctrine')->getEntityManager();
        $em->persist($product);
        $em->flush();

        // ...
    }

.. note::

    If you're following along with this example, you'll need to create a
    route that points to this action to see it in action.

Let's walk through this example:

* *lines 5-8* In this section, you instantiate and work with the ``$product``
  object line any other, normal PHP object;

* *line 10* This line fetches Doctrine's *entity manager* object, which is
  responsibly for handling the process of persisting and fetching objects
  from the database;

* *line 11* The ``persist()`` method tells Doctrine to "manage" the ``$product``
  object. This does not actually cause a query to be made to the database (yet).

* *line 12* When the ``flush()`` method is called, Doctrine looks through
  all of the objects that it's managing to see if they need to be persisted
  to the database. In this example, the ``$product`` object has not been
  persisted yet, so the entity manager executes an ``INSERT`` query and a
  row is created in the ``product`` table.

When creating or updating objects, the workflow is always the same. In the
next section, you'll see how Doctrine is smart enough to automatically issue
an ``UPDATE`` query if the record already exists in the database.

.. tip::

    Symfony provides a bundle that allows you to programmatically load testing
    data into your project (i.e. "fixture data"). For information, see
    :doc:`/cookbook/doctrine/doctrine_fixtures`.

Fetching Objects from the Database
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Fetching an object back out of the database is even easier. For example,
suppose you've configured a route to display a specific ``Product`` based
on its ``id`` value::

    public function showAction($id)
    {
        $product = $this->get('doctrine')
            ->getEntityManager()
            ->getRepository('AcmeStoreBundle:Product')
            ->find($id);
        
        if (!$product) {
            throw $this->createNotFoundException('No product found for id '.$id);
        }

        // do something, like pass the $product object into a template
    }

When you query for a particular type of object, you always use what's known
as its "repository". You can think of a repository as a PHP class whose only
job is to help you fetch entities of a certain class. You can access the
repository object for an entity type via::

    $repository = $this->get('doctrine')
        ->getEntityManager()
        ->getRepository('AcmeStoreBundle:Product');

.. note::

    The ``AcmeStoreBundle:Product`` string is a shortcut you can use anywhere
    in Doctrine instead of the full class name of the entity (i.e. ``Acme\StoreBundle\Entity\Product``).
    As long as your entity lives under the ``Entity`` namespace of your bundle,
    this will work.

Once you have your repository, you have access to all sorts of helpful methods::

    // query by the primary key (usually "id")
    $product = $repository->find($id);

    // dynamic method names to find based on a column value
    $product = $repository->findOneById($id);
    $product = $repository->findOneByName('foo');

    // find *all* products
    $products = $repository->findAll();

    // find a group of products based on an abitrary column value
    $products = $repository->findByPrice(19.99);

.. tip::

    Of course, you can also issue complex queries, which you'll learn more
    about in the :ref:`book-doctrine-queries` section.

Updating an Object
~~~~~~~~~~~~~~~~~~

Once you've fetched an object from Doctrine, updating it is easy::

    $em = $this->get('doctrine')->getEntityManager();
    $product = $em->getRepository('AcmeStoreBundle:Product')
        ->find($id);

    if (!$product) {
        throw $this->createNotFoundException('No product found for id '.$id);
    }

    $product->setName('New product name!');
    $em->flush();

Updating an object involves just three steps:

1. fetching the object from Doctrine;
2. modifying the object;
3. calling ``flush()`` on the entity manager

Notice that calling ``$em->persist($product)`` isn't necessary. Recall that
this method simply tells Doctrine to manage or "watch" the ``$product`` object.
In this case, since you fetched the ``$product`` object from Doctrine, it's
already managed.

Deleting an Object
~~~~~~~~~~~~~~~~~~

Deleting an object is very similar, but requires a call to the ``remove()``
method of the entity manager:

    $em->remove($product);
    $em->flush();

As you might expect, the ``remove()`` method notifies Doctrine that you'd
like to remove the given entity from the database. The actual ``DELETE`` query,
however, isn't actually executed until the ``flush()`` method is called.

Querying for Objects
--------------------

You've already seen how the repository object allows you to run basic queries
without any work::

    $repository->find($id);
    
    $repository->findOneByName('Foo');

Of course, Doctrine also allows you to write more complex queries using the
Doctrine Query Language (DQL). DQL is similar to SQL except that you should
imagine that you're querying for one or more objects of an entity class (e.g. ``Product``)
instead of querying for rows on a table (e.g. ``product``).

When querying in Doctrine, you have two options: writing pure Doctrine queries
or using Doctrine's Query Builder.

Querying for Objects with DQL
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Imaging that you want to query for products, but only return products that
cost more than ``19.99``, ordered from cheapest to most expensive. From inside
a controller, do the following::

    $em = $this->get('doctrine')->getEntityManager();
    $query = $em->createQuery(
        'SELECT p FROM AcmeStoreBundle:Product p WHERE p.price > :price ORDER BY p.price ASC'
    )->setParameter('price', '19.99');
    
    $products = $query->getResult();

If you're comfortable, with SQL, then DQL should feel very natural. The biggest
difference is that you need to think in terms of "objects" instead of rows
in a database. For this reason, you select *from* ``AcmeStoreBundle:Product``.

The ``getResult()`` method returns an array of results. If you're querying
for just one object, you can use the ``getSingleResult()`` method instead::

    $product = $query->getSingleResult();

The DQL syntax is incredibly powerful, allowing you to easily join between
entities (the topic of :ref:`relations<book-doctrine-relations>` will be
covered later), add limits, group, etc. For more information, see the official
Doctrine `Doctrine Query Language`_ documentation.

.. sidebar:: Setting Parameters

    Also notice the ``setParameter`` method. When working with Doctrine, it's
    always a good idea to set any external values as "placeholders", which
    has been done here in the query:

        ... WHERE p.price > :price ...

    You can then set the value of the ``price`` placeholder by calling the ``setParameter``
    method:

        ->setParameter('price', '19.99')

    This is done to prevent SQL injection attacks and should always be used.
    If you're using multiple parameters, you can also set their values at
    once using the ``setParameters`` method:

        ->setParameters(array(
            'price' => '19.99',
            'name'  => 'Foo',
        ))

Using Doctrine's Query Builder
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Instead of writing the queries directly, you can alternatively use Doctrine's
query to do the same job using a nice, object-oriented interface. From inside
a controller:

    $repository = $this->get('doctrine')
        ->getEntityManager()
        ->getRepository('AcmeStoreBundle:Product');

    $query = $repository->createQueryBuilder('p')
        ->where('p.price > :price')
        ->setParameter('price', '19.99')
        ->orderBy('p.price', 'ASC')
        ->getQuery();
    
    $products = $query->getResult();

The ``QueryBuilder`` object contains every method necessary to build your
query. By calling the ``getQuery()`` method, the query builder returns a
normal ``Query`` object, which is the same Query object you build directly
on the previous section.

For more information on Doctrine's Query Builder, consult the official
`Query Builder`_ documentation.

Custom Repository Classes
~~~~~~~~~~~~~~~~~~~~~~~~~

In the previous sections, you began constructing and using more complex queries
from inside a controller. In order to isolate, test and reuse these queries,
it's a good idea to create a custom repository class for your entity and
add methods with your query logic there.

To do this, add the name of the repository class to your mapping definition.

.. configuration-block::

    .. code-block:: php-annotations

        // src/Acme/StoreBundle/Entity/Product.php
        namespace Acme\StoreBundle\Entity;

        use Doctrine\ORM\Mapping as ORM;

        /**
         * @ORM\Entity(repositoryClass="Acme\StoreBundle\Repository\ProductRepository")
         */
        class Product
        {
            //...
        }

    .. code-block:: yaml

        # src/Acme/StoreBundle/Resources/config/doctrine/Acme.StoreBundle.Entity.Product.orm.yml
        Acme\StoreBundle\Entity\Product:
            type: entity
            repositoryClass: Acme\StoreBundle\Repository\ProductRepository
            #...

    .. code-block:: xml

        <!-- src/Acme/StoreBundle/Resources/config/doctrine/Acme.StoreBundle.Entity.Product.orm.xml -->
        <doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping"
              xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
              xsi:schemaLocation="http://doctrine-project.org/schemas/orm/doctrine-mapping
                            http://doctrine-project.org/schemas/orm/doctrine-mapping.xsd">

            <entity name="Acme\StoreBundle\Entity\Product"
                    repository-class="Acme\StoreBundle\Repository\ProductRepository">
                    <!-- ... -->
            </entity>
        </doctrine-mapping>

Doctrine can generate the repository class for you by running the same command
used earlier to generate missing getter and setter methods:

    $ php app/console doctrine:generate:entities Acme

The following code shows the new sample class with a new method - ``findAllOrderedByName()`` -
that queries for all of the ``Product`` entities, ordered alphabetically.

.. code-block:: php

    // src/Acme/StoreBundle/Repository/ProductRepository.php
    namespace Acme\StoreBundle\Repository;

    use Doctrine\ORM\EntityRepository;

    class ProductRepository extends EntityRepository
    {
        public function findAllOrderedByName()
        {
            return $this->getEntityManager()
                        ->createQuery('SELECT p FROM AcmeStoreBundle:Product p 
                                        ORDER BY p.name ASC')
                        ->getResult();
        }
    }

.. tip::

    The entity manager can be accessed via ``$this->getEntityManager()``
    from inside the repository.

The usage of this new method is the same as with the default finder methods.

.. code-block:: php

    $em = $this->get('doctrine')->getEntityManager();
    $products = $em->getRepository('AcmeStoreBundle:Product')
                ->findAllOrderedByName();

.. note::

    When using a custom repository class, you still have access to the default
    finder methods such as ``find()`` and ``findAll()``.

.. book-doctrine-field-types:

Doctrine Field Types
--------------------

.. _`Doctrine`: http://www.doctrine-project.org/
.. _`Query Builder`: http://www.doctrine-project.org/docs/orm/2.0/en/reference/query-builder.html
.. _`Doctrine Query Language`: Doctrine Query Language