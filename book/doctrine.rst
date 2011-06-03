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

    php app/console doctrine:database:create --mapping-type=yml

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
        protected $name;

        protected $price;

        protected $description;
    }

The class - often called an "entity", meaning *a basic class that holds data*
- is simple and helps fulfill the business requirement of needing products
in your application. This class can't be persisted to a database yet - it's
just a simple PHP class.

.. tip::

    Once you learn the concepts behind Doctrine, you can have Doctrine create
    this entity class for you:
    
    .. code-block:: bash
    
        php app/console doctrine:generate:entity AcmeStoreBundle:Product

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
            protected $name;

            /**
             * @ORM\Column(type="decimal", scale="2")
             */
            protected $price;

            /**
             * @ORM\Column(type="text")
             */
            protected $description;
        }

.. tip::

    The table option is optional and if omitted, will be determined automatically
    based on the name of the entity class.

Doctrine allows you to choose from a wide variety of different field types,
each with their own options. For information on the available field types,
see the :ref:`book-doctrine-field-types` section.

.. seealso::

    You can also check out Doctrine's `Basic Mapping Documentation`_ for
    all details about mapping information. Keep in mind that when you use
    Doctrine inside Symfony, you'll need to prepend all annotations with
    ``ORM\`` (e.g. ``ORM\Column(..)``), which is not shown in Doctrine's
    documentation.

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

.. tip::

    Doctrine doesn't care whether your properties are ``public``, ``protected``
    or ``private``, or whether or not you have a getter or setter function
    for a property. The getters and setters are generated here only because
    you'll need them to interact with your PHP object.

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

.. tip::

    When you render any page, you can see how many queries were made in the
    bottom right corner of the web debug toolbar.

    .. image:: /images/book/doctrine_web_debug_toolbar.png
       :align: center

    If you click the icon, the profiler will open, showing you the exact
    queries that were made.

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

        # src/Acme/StoreBundle/Resources/config/doctrine/Product.orm.yml
        Acme\StoreBundle\Entity\Product:
            type: entity
            repositoryClass: Acme\StoreBundle\Repository\ProductRepository
            #...

    .. code-block:: xml

        <!-- src/Acme/StoreBundle/Resources/config/doctrine/Product.orm.xml -->
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

Entity Relationships/Associations
---------------------------------

Suppose that the products in your application all belong to exactly on "category".
In this case, you'll need a ``Category`` object and a way to relate a ``Product``
object to a ``Category`` object. Start by creating the ``Category`` entity.
Since you know that you'll eventually need to persist the class through Doctrine,
you can let Doctrine create the class for you:

.. code-block:: bash

    php app/console doctrine:generate:entity AcmeStoreBundle:Category "name:string(255)" --mapping-type=yml

This task generates the ``Category`` entity for you, with an ``id`` field,
a ``name`` field and the associated getter and setter functions.

Relationship Mapping Metadata
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

To relate the ``Category`` and ``Product`` entities, start by creating a
``products`` property on the ``Category`` class::

    // src/Acme/StoreBundle/Entity/Category.php
    // ...
    
    class Category
    {
        // ...
        
        /**
         * @ORM\OneToMany(targetEntity="Product", mappedBy="category")
         */
        protected $products;

        public function __construct()
        {
            $this->products = new Doctrine\Common\Collections\ArrayCollection();
        }
    }

First, since a ``Category`` object will relate to many ``Product`` objects,
a ``products`` array property is added to hold those ``Product`` objects.
The code in the ``__construct()`` method is important because Doctrine requires
the ``$products`` property to be an ``ArrayCollection`` object. This object
looks and acts just like an array, but has some added flexibility.

Next, since each ``Product`` class can relate to exactly one ``Category``
object, you'll want to add a ``$category`` property to the ``Product`` class::

    // src/Acme/StoreBundle/Entity/Product.php
    // ...

    class Product
    {
        // ...
    
        /**
         * @ORM\ManyToOne(targetEntity="Category", inversedBy="products")
         * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
         */
        protected $category;
    }

Finally, now that you've added a new property to both the ``Category`` and
``Product`` classes, tell Doctrine to generate the missing getter and setter
methods for you:

.. code-block:: bash

    php app/console doctrine:generate:entities Acme

If you ignore the Doctrine metadata, you now have two classes - ``Category``
and ``Product`` with a natural one-to-many relationship. The ``Category``
class holds an array of ``Product`` objects and the ``Product`` object can
hold one ``Category`` object.

Now, look at the metadata above the ``$category`` property on the ``Product``
class. The information here tells doctrine that the related class is ``Category``
and that it should store the ``id`` of the category record on a ``category_id``
field that lives on the ``product`` table. In other words, the related ``Category``
object will be stored on the ``$category`` property, but behind the scenes,
Doctrine will persist this relationship by storing the category's id value
on a ``category_id`` column in the database.

    DIAGRAM here of Product with related category on left, and on the right,
    a diagram showing how the two tables are related.

Before you continue, be sure tell Doctrine to add the new ``category`` table
and ``product.category_id`` column:

.. code-block:: bash

    php app/console doctrine:schema:update --force

Saving Related Entities
~~~~~~~~~~~~~~~~~~~~~~~

Now, let's see the code in action. Imagine you're inside a controller::

    // ...
    use Acme\StoreBundle\Entity\Category;
    use Acme\StoreBundle\Entity\Product;
    // ...

    class DefaultController extends Controller
    {
        public function createProductAction()
        {
            $category = new Category();
            $category->setName('Main Products');
            
            $product = new Product();
            $product->setName('Foo');
            $product->setPrice(19.99);
            $product->setCategory();
            
            $em = $this->get('doctrine')->getEntityManager();
            $em->persist($category);
            $em->persist($product);
            $em->flush();
        }
    }

Now, a single row is added to both the ``category`` and ``product`` tables.
The ``product.category_id`` column for the new product is set to whatever
the ``id`` is of the new category. Doctrine managers the persistence of this
relationship for you.

.. note::

    This task should only be really used during development. For a more robust
    method of systematically updating your production database, read about
    :doc:`Doctrine migrations</cookbook/doctrine/migrations>`.

Fetching Related Objects
~~~~~~~~~~~~~~~~~~~~~~~~

When you need to fetch associated objects, your workflow looks just like it
did before. First, fetch a ``$product`` object and then access its related
``Category``::

    public function showAction($id)
    {
        $product = $this->get('doctrine')
            ->getEntityManager()
            ->getRepository('AcmeStoreBundle:Product')
            ->find($id);

        $category = $product->getCategory();
        
        // ...
    }

In this example, you first query for a ``Product`` object based on the product's
``id``. This issues a query for *just* the single ``Product`` object and
prepares the ``$product`` object. Later, when you call ``$product->getCategory()``,
Doctrine silently makes a second query to find the ``Category`` that's related
this ``Product``. It prepares the ``Category`` object and returns it to you.

    DIAGRAM of querying for the Product object (on left) getting it from
    db (on the right). You should then see the category_id of that Product.
    Finally, we call ->getCategory() (on left) and it fetches the Category
    from the DB (on the right).

What's important is the fact that you have easy access to the product's related
category, but the category data isn't actually retrieved until you ask for
the category (i.e. it's "lazily loaded").

You can also query in the other direction::

    public function showProductAction($id)
    {
        $category = $this->get('doctrine')
            ->getEntityManager()
            ->getRepository('AcmeStoreBundle:Category')
            ->find($id);

        $products = $category->getProducts();
    
        // ...
    }

In this case, the same things occurs: you first query out for a single ``Category``
object, and then Doctrine makes a second query to retrieve the related ``Product``
objects only when you ask for them (e.g. when you call ``->getProducts()``).
The ``$products`` variable is an array of ``Product`` objects that all relate
to the given ``Category`` object via their ``category_id`` column.

Joining to Related Records
~~~~~~~~~~~~~~~~~~~~~~~~~~

In the above examples, two queries were made - one for the original object
(e.g. a ``Category``) and one for the related object(s) (e.g. the ``Product``
objects).

.. tip::

    Remember that you can see all of the queries made during a request via
    the web debug toolbar.

Of course, if you *know* that you'll need to access both objects, you can
avoid the second query by joining in the original query. Add the following
method to the ``ProductRepository`` class::

    // src/Acme/StoreBundle/Repository/ProductRepository.php
    
    public function findOneByIdJoinedToCategory($id)
    {
        $query = $this->getEntityManager()
            ->createQuery('
                SELECT p, c FROM AcmeStoreBundle:Product p
                JOIN p.category c
                WHERE p.id = :id'
            )->setParameter('id', $id)
            ->setMaxResults(1);

        $results = $query->getResult();
        if (count($results) > 0) {
            return array_shift($results);
        }

        return null;
    }

Now, you can use this method in your controller to query for a ``Product``
object and its related ``Category`` all with just one query::

    public function showAction($id)
    {
        $product = $this->get('doctrine')
            ->getEntityManager()
            ->getRepository('AcmeStoreBundle:Product')
            ->findOneByIdJoinedToCategory($id);

        $category = $product->getCategory();
    
        // ...
    }    

More Information on Associations
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

This section has been an introduction to one common type of entity relationship,
the one-to-many relationship. For more advanced details and examples of how
to use other types of relations (e.g. ``one-to-one``, ``many-to-many``), see
Doctrine's `Association Mapping Documentation`_. Keep in mind that when using
Doctrine in Symfony, you'll need to prepend all annotations with ``ORM\``
(e.g. ``ORM\OneToMany``), which is not reflected in Doctrine's documentation.

.. book-doctrine-field-types:

Doctrine Field Types Reference
------------------------------

.. _`Doctrine`: http://www.doctrine-project.org/
.. _`Basic Mapping Documentation`: http://www.doctrine-project.org/docs/orm/2.0/en/reference/basic-mapping.html
.. _`Query Builder`: http://www.doctrine-project.org/docs/orm/2.0/en/reference/query-builder.html
.. _`Doctrine Query Language`: http://www.doctrine-project.org/docs/orm/2.0/en/reference/dql-doctrine-query-language.html
.. _`Association Mapping Documentation`: http://www.doctrine-project.org/docs/orm/2.0/en/reference/association-mapping.html