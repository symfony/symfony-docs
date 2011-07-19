.. index::
   pair: Doctrine; MongoDB ODM

How to use MongoDB
==================

The `MongoDB`_ Object Document Mapper (ODM) is much like the Doctrine2 ORM
in its philosophy and how it works. In other words, like the :doc:`Doctrine2 ORM</book/doctrine>`,
with the Doctrine ODM, you deal only with plain PHP objects, which are then
persisted transparently to and from MongoDB.

.. tip::

    You can read more about the Doctrine MongoDB ODM via the project's `documentation`_.

A bundle is available that integrates the Doctrine MongoDB ODM into Symfony,
making it easy to configure and use.

.. note::

    This chapter will feel a lot like the :doc:`Doctrine2 ORM chapter</book/doctrine>`,
    which talks about how the Doctrine ORM can be used to persist data to
    relational databases (e.g. MySQL). This is on purpose - whether you persist
    to a relational database via the ORM or MongoDB via the ODM, the philosophies
    are very much the same.

Installation
------------

To use the MongoDB ODM, you'll need two libraries provided by Doctrine and
one bundle that integrates them into Symfony. If you're using the Symfony
Standard Distribution, add the following to the ``deps`` file at the root
of your project:

.. code-block:: text

    [doctrine-mongodb]
        git=http://github.com/doctrine/mongodb.git

    [doctrine-mongodb-odm]
        git=http://github.com/doctrine/mongodb-odm.git

    [DoctrineMongoDBBundle]
        git=http://github.com/symfony/DoctrineMongoDBBundle.git
        target=/bundles/Symfony/Bundle/DoctrineMongoDBBundle

Now, update the vendor libraries by running:

.. code-block:: bash

    $ php bin/vendors install

Next, add the ``Doctrine\ODM\MongoDB`` and ``Doctrine\MongoDB`` namespaces
to the ``app/autoload.php`` file so that these libraries can be autoloaded.
Be sure to add them anywhere *above* the ``Doctrine`` namespace (shown here)::

    // app/autoload.php
    $loader->registerNamespaces(array(
        // ...
        'Doctrine\\ODM\\MongoDB'    => __DIR__.'/../vendor/doctrine-mongodb-odm/lib',
        'Doctrine\\MongoDB'         => __DIR__.'/../vendor/doctrine-mongodb/lib',
        'Doctrine'                  => __DIR__.'/../vendor/doctrine/lib',
        // ...
    ));

Next, register the annotations library by adding the following to the autoloader
(below the existing ``AnnotationRegistry::registerFile`` line)::

    // app/autoload.php
    AnnotationRegistry::registerFile(
        __DIR__.'/../vendor/doctrine-mongodb-odm/lib/Doctrine/ODM/MongoDB/Mapping/Annotations/DoctrineAnnotations.php'
    );

Finally, enable the new bundle in the kernel::

    // app/AppKernel.php
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Symfony\Bundle\DoctrineMongoDBBundle\DoctrineMongoDBBundle(),
        );

        // ...
    }

Congratulations! You're ready to get to work.

Configuration
-------------

To get started, you'll need some basic configuration that sets up the document
manager. The easiest way is to enable ``auto_mapping``, which will activate
the MongoDB ODM across your application:

.. code-block:: yaml

    # app/config/config.yml
    doctrine_mongodb:
        connections:
            default:
                server: mongodb://localhost:27017
                options:
                    connect: true
        default_database: test_database
        document_managers:
            default:
                auto_mapping: true

.. note::

    Of course, you'll also need to make sure that the MongoDB server is running
    in the background. For more details, see the MongoDB `Quick Start`_ guide.

A Simple Example: A Product
---------------------------

The best way to understand the Doctrine MongoDB ODM is to see it in action.
In this section, you'll walk through each step needed to start persisting
documents to and from MongoDB.

.. sidebar:: Code along with the example

    If you want to follow along with the example in this chapter, create
    an ``AcmeStoreBundle`` via:

    .. code-block:: bash

        php app/console generate:bundle --namespace=Acme/StoreBundle

Creating a Document Class
~~~~~~~~~~~~~~~~~~~~~~~~~

Suppose you're building an application where products need to be displayed.
Without even thinking about Doctrine or MongoDB, you already know that you
need a ``Product`` object to represent those products. Create this class
inside the ``Document`` directory of your ``AcmeStoreBundle``::

    // src/Acme/StoreBundle/Document/Product.php
    namespace Acme\StoreBundle\Document;

    class Product
    {
        protected $name;

        protected $price;
    }

The class - often called a "document", meaning *a basic class that holds data* -
is simple and helps fulfill the business requirement of needing products
in your application. This class can't be persisted to Doctrine MongoDB yet -
it's just a simple PHP class.

Add Mapping Information
~~~~~~~~~~~~~~~~~~~~~~~

Doctrine allows you to work with MongoDB in a much more interesting way
than just fetching data back and forth as an array. Instead, Doctrine allows
you to persist entire *objects* to MongoDB and fetch entire objects out of
MongoDB. This works by mapping a PHP class and its properties to entries
of a MongoDB collection.

For Doctrine to be able to do this, you just have to create "metadata", or
configuration that tells Doctrine exactly how the ``Product`` class and its
properties should be *mapped* to MongoDB. This metadata can be specified
in a number of different formats including YAML, XML or directly inside the
``Product`` class via annotations:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Acme/StoreBundle/Document/Product.php
        namespace Acme\StoreBundle\Document;

        use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

        /**
         * @MongoDB\Document
         */
        class Product
        {
            /**
             * @MongoDB\Id
             */
            protected $id;

            /**
             * @MongoDB\String
             */
            protected $name;

            /**
             * @MongoDB\Float
             */
            protected $price;
        }

    .. code-block:: yaml

        # src/Acme/StoreBundle/Resources/config/doctrine/Product.mongodb.yml
        Acme\StoreBundle\Document\Product:
            fields:
                id:
                    id:  true
                name:
                    type: string
                price:
                    type: float

    .. code-block:: xml

        <!-- src/Acme/StoreBundle/Resources/config/doctrine/Product.mongodb.xml -->
        <doctrine-mongo-mapping xmlns="http://doctrine-project.org/schemas/odm/doctrine-mongo-mapping"
              xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
              xsi:schemaLocation="http://doctrine-project.org/schemas/odm/doctrine-mongo-mapping
                            http://doctrine-project.org/schemas/odm/doctrine-mongo-mapping.xsd">

            <document name="Acme\StoreBundle\Document\Product">
                <field fieldName="id" id="true" />
                <field fieldName="name" type="string" />
                <field fieldName="price" type="float" />
            </document>
        </doctrine-mongo-mapping>

Doctrine allows you to choose from a wide variety of different field types,
each with their own options. For information on the available field types,
see the :ref:`cookbook-mongodb-field-types` section.

.. seealso::

    You can also check out Doctrine's `Basic Mapping Documentation`_ for
    all details about mapping information. If you use annotations, you'll
    need to prepend all annotations with ``MongoDB\`` (e.g. ``MongoDB\String``),
    which is not shown in Doctrine's documentation. You'll also need to include
    the ``use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;`` statement,
    which *imports* the ``MongoDB`` annotations prefix.

Generating Getters and Setters
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Even though Doctrine now knows how to persist a ``Product`` object to MongoDB
the class itself isn't really useful yet. Since ``Product`` is just a regular
PHP class, you need to create getter and setter methods (e.g. ``getName()``,
``setName()``) in order to access its properties (since the properties are
``protected``). Fortunately, Doctrine can do this for you by running:

.. code-block:: bash

    php app/console doctrine:mongodb:generate:documents AcmeStoreBundle

This command makes sure that all of the getters and setters are generated
for the ``Product`` class. This is a safe command - you can run it over and
over again: it only generates getters and setters that don't exist (i.e. it
doesn't replace your existing methods).

.. note::

    Doctrine doesn't care whether your properties are ``protected`` or ``private``,
    or whether or not you have a getter or setter function for a property.
    The getters and setters are generated here only because you'll need them
    to interact with your PHP object.

Persisting Objects to MongoDB
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Now that you have a mapped ``Product`` document complete with getter and
setter methods, you're ready to persist data to MongoDB. From inside a controller,
this is pretty easy. Add the following method to the ``DefaultController``
of the bundle:

.. code-block:: php
    :linenos:

    // src/Acme/StoreBundle/Controller/DefaultController.php
    use Acme\StoreBundle\Document\Product;
    use Symfony\Component\HttpFoundation\Response;
    // ...

    public function createAction()
    {
        $product = new Product();
        $product->setName('A Foo Bar');
        $product->setPrice('19.99');

        $dm = $this->get('doctrine.odm.mongodb.document_manager');
        $dm->persist($product);
        $dm->flush();

        return new Response('Created product id '.$product->getId());
    }

.. note::

    If you're following along with this example, you'll need to create a
    route that points to this action to see it in work.

Let's walk through this example:

* **lines 8-10** In this section, you instantiate and work with the ``$product``
  object like any other, normal PHP object;

* **line 12** This line fetches Doctrine's *document manager* object, which is
  responsible for handling the process of persisting and fetching objects
  to and from MongoDB;

* **line 13** The ``persist()`` method tells Doctrine to "manage" the ``$product``
  object. This does not actually cause a query to be made to MongoDB (yet).

* **line 14** When the ``flush()`` method is called, Doctrine looks through
  all of the objects that it's managing to see if they need to be persisted
  to MongoDB. In this example, the ``$product`` object has not been persisted yet,
  so the document manager makes a query to MongoDB, which adds a new entry.

.. note::

    In fact, since Doctrine is aware of all your managed objects, when you
    call the ``flush()`` method, it calculates an overall changeset and executes
    the most efficient operation possible.

When creating or updating objects, the workflow is always the same. In the
next section, you'll see how Doctrine is smart enough to update entries if
they already exist in MongoDB.

.. tip::

    Doctrine provides a library that allows you to programmatically load testing
    data into your project (i.e. "fixture data"). For information, see
    :doc:`/cookbook/doctrine/doctrine_fixtures`.

Fetching Objects from MongoDB
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Fetching an object back out of MongoDB is even easier. For example, suppose
you've configured a route to display a specific ``Product`` based on its
``id`` value::

    public function showAction($id)
    {
        $product = $this->get('doctrine.odm.mongodb.document_manager')
            ->getRepository('AcmeStoreBundle:Product')
            ->find($id);

        if (!$product) {
            throw $this->createNotFoundException('No product found for id '.$id);
        }

        // do something, like pass the $product object into a template
    }

When you query for a particular type of object, you always use what's known
as its "repository". You can think of a repository as a PHP class whose only
job is to help you fetch objects of a certain class. You can access the
repository object for a document class via::

    $repository = $this->get('doctrine.odm.mongodb.document_manager')
        ->getRepository('AcmeStoreBundle:Product');

.. note::

    The ``AcmeStoreBundle:Product`` string is a shortcut you can use anywhere
    in Doctrine instead of the full class name of the document (i.e. ``Acme\StoreBundle\Document\Product``).
    As long as your document lives under the ``Document`` namespace of your bundle,
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

.. note::

    Of course, you can also issue complex queries, which you'll learn more
    about in the :ref:`book-doctrine-queries` section.

You can also take advantage of the useful ``findBy`` and ``findOneBy`` methods
to easily fetch objects based on multiple conditions::

    // query for one product matching be name and price
    $product = $repository->findOneBy(array('name' => 'foo', 'price' => 19.99));

    // query for all prdocuts matching the name, ordered by price
    $product = $repository->findBy(
        array('name' => 'foo'),
        array('price', 'ASC')
    );

Updating an Object
~~~~~~~~~~~~~~~~~~

Once you've fetched an object from Doctrine, updating it is easy. Suppose
you have a route that maps a product id to an update action in a controller::

    public function updateAction($id)
    {
        $dm = $this->get('doctrine.odm.mongodb.document_manager');
        $product = $dm->getRepository('AcmeStoreBundle:Product')->find($id);

        if (!$product) {
            throw $this->createNotFoundException('No product found for id '.$id);
        }

        $product->setName('New product name!');
        $dm->flush();

        return $this->redirect($this->generateUrl('homepage'));
    }

Updating an object involves just three steps:

1. fetching the object from Doctrine;
2. modifying the object;
3. calling ``flush()`` on the document manager

Notice that calling ``$dm->persist($product)`` isn't necessary. Recall that
this method simply tells Doctrine to manage or "watch" the ``$product`` object.
In this case, since you fetched the ``$product`` object from Doctrine, it's
already managed.

Deleting an Object
~~~~~~~~~~~~~~~~~~

Deleting an object is very similar, but requires a call to the ``remove()``
method of the document manager::

    $dm->remove($product);
    $dm->flush();

As you might expect, the ``remove()`` method notifies Doctrine that you'd
like to remove the given document from the MongoDB. The actual delete operation
however, isn't actually executed until the ``flush()`` method is called.

Querying for Objects
--------------------

As you saw above, the built-in repository class allows you to query for one
or many objects based on an number of different parameters. When this is
enough, this is the easiest way to query for documents. Of course, you can
also create more complex queries.

Using the Query Builder
~~~~~~~~~~~~~~~~~~~~~~~

Doctrine's ODM ships with a query "Builder" object, which allows you to construct
a query for exactly which documents you want to return. If you use an IDE,
you can also take advantage of auto-completion as you type the method names.
From inside a controller::

    $products = $this->get('doctrine.odm.mongodb.document_manager')
        ->createQueryBuilder('AcmeStoreBundle:Product')
        ->field('name')->equals('foo')
        ->limit(10)
        ->sort('price', 'ASC')
        ->getQuery()
        ->execute()

In this case, 10 products with a name of "foo", ordered from lowest price
to highest price are returned.

The ``QueryBuilder`` object contains every method necessary to build your
query. For more information on Doctrine's Query Builder, consult Doctrine's
`Query Builder`_ documentation. For a list of the available conditions you
can place on the query, see the `Conditional Operators`_ documentation specifically.

Custom Repository Classes
~~~~~~~~~~~~~~~~~~~~~~~~~

In the previous section, you began constructing and using more complex queries
from inside a controller. In order to isolate, test and reuse these queries,
it's a good idea to create a custom repository class for your document and
add methods with your query logic there.

To do this, add the name of the repository class to your mapping definition.

.. configuration-block::

    .. code-block:: php-annotations

        // src/Acme/StoreBundle/Document/Product.php
        namespace Acme\StoreBundle\Document;

        use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

        /**
         * @MongoDB\Document(repositoryClass="Acme\StoreBundle\Repository\ProductRepository")
         */
        class Product
        {
            //...
        }

    .. code-block:: yaml

        # src/Acme/StoreBundle/Resources/config/doctrine/Product.mongodb.yml
        Acme\StoreBundle\Document\Product:
            repositoryClass: Acme\StoreBundle\Repository\ProductRepository
            # ...

    .. code-block:: xml

        <!-- src/Acme/StoreBundle/Resources/config/doctrine/Product.mongodb.xml -->
        <!-- ... -->
        <doctrine-mongo-mapping xmlns="http://doctrine-project.org/schemas/odm/doctrine-mongo-mapping"
              xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
              xsi:schemaLocation="http://doctrine-project.org/schemas/odm/doctrine-mongo-mapping
                            http://doctrine-project.org/schemas/odm/doctrine-mongo-mapping.xsd">

            <document name="Acme\StoreBundle\Document\Product"
                    repository-class="Acme\StoreBundle\Repository\ProductRepository">
                <!-- ... -->
            </document>

        </doctrine-mong-mapping>

Doctrine can generate the repository class for you by running :

.. code-block:: bash

    php app/console doctrine:mongodb:generate:repositories AcmeStoreBundle

Next, add a new method - ``findAllOrderedByName()`` - to the newly generated
repository class. This method will query for all of the ``Product`` documents,
ordered alphabetically.

.. code-block:: php

    // src/Acme/StoreBundle/Repository/ProductRepository.php
    namespace Acme\StoreBundle\Repository;

    use Doctrine\ODM\MongoDB\DocumentRepository;

    class ProductRepository extends DocumentRepository
    {
        public function findAllOrderedByName()
        {
            return $this->createQueryBuilder()
                ->sort('name', 'ASC')
                ->getQuery()
                ->execute();
        }
    }

You can use this new method just like the default finder methods of the repository::

    $product = $this->get('doctrine.odm.mongodb.document_manager')
        ->getRepository('AcmeStoreBundle:Product')
        ->findAllOrderedByName();


.. note::

    When using a custom repository class, you still have access to the default
    finder methods such as ``find()`` and ``findAll()``.

Doctrine Extensions: Timestampable, Sluggable, etc.
---------------------------------------------------

Doctrine is quite flexible, and a number of third-party extensions are available
that allow you to easily perform repeated and common tasks on your entities.
These include thing such as *Sluggable*, *Timestampable*, *Loggable*, *Translatable*,
and *Tree*.

For more information on how to find and use these extensions, see the cookbook
article about :doc:`using common Doctrine extensions</cookbook/doctrine/common_extensions>`.

.. _cookbook-mongodb-field-types:

Doctrine Field Types Reference
------------------------------

Doctrine comes with a large number of field types available. Each of these
maps a PHP data type to a specific `MongoDB type`_. The following are just *some*
of the types supported by Doctrine:

* ``string``
* ``int``
* ``float``
* ``date``
* ``timestamp``
* ``boolean``
* ``file``

For more information, see Doctrine's `Mapping Types documentation`_.

.. index::
   single: Doctrine; ODM Console Commands
   single: CLI; Doctrine ODM

Console Commands
----------------

The Doctrine2 ODM integration offers several console commands under the
``doctrine:mongodb`` namespace. To view the command list you can run the console
without any arguments:

.. code-block:: bash

    php app/console

A list of available command will print out, many of which start with the
``doctrine:mongodb`` prefix. You can find out more information about any
of these commands (or any Symfony command) by running the ``help`` command.
For example, to get details about the ``doctrine:mongodb:query`` task, run:

.. code-block:: bash

    php app/console help doctrine:mongodb:query

.. note::

   To be able to load data fixtures into MongoDB, you will need to have the
   ``DoctrineFixturesBundle`` bundle installed. To learn how to do it,
   read the ":doc:`/cookbook/doctrine/doctrine_fixtures`" entry of the Cookbook.

.. index::
   single: Configuration; Doctrine MongoDB ODM
   single: Doctrine; MongoDB ODM configuration

Configuration
-------------

For detailed information on configuration options available when using the
Doctrine ODM, see the :doc:`MongoDB Reference</reference/configuration/mongodb>` section.

Registering Event Listeners and Subscribers
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Doctrine allows you to register listeners and subscribers that are notified
when different events occur inside Doctrine's ODM. For more information,
see Doctrine's `Event Documentation`_.

In Symfony, you can register a listener or subscriber by creating a :term:`service`
and then :ref:`tagging<book-service-container-tags>` it with a specific tag.

*   **event listener**: Use the ``doctrine.odm.mongodb.<connection>_event_listener``
    tag, where ``<connection>`` name is replaced by the name of your connection
    (usually ``default``). Also, be sure to add an ``event`` key to the tag
    specifying which event to listen to. Assuming your connection is called
    ``default``, then:

    .. configuration-block::

        .. code-block:: yaml

            services:
                my_doctrine_listener:
                    class:   Acme\HelloBundle\Listener\MyDoctrineListener
                    # ...
                    tags:
                        -  { name: doctrine.odm.mongodb.default_event_listener, event: postPersist }

        .. code-block:: xml

            <service id="my_doctrine_listener" class="Acme\HelloBundle\Listener\MyDoctrineListener">
                <!-- ... -->
                <tag name="doctrine.odm.mongodb.default_event_listener" event="postPersist" />
            </service>.

        .. code-block:: php

            $definition = new Definition('Acme\HelloBundle\Listener\MyDoctrineListener');
            // ...
            $definition->addTag('doctrine.odm.mongodb.default_event_listener');
            $container->setDefinition('my_doctrine_listener', $definition);

*   **event subscriber**: Use the ``doctrine.odm.mongodb.<connection>_event_subscriber``
    tag. No other keys are needed in the tag.

Summary
-------

With Doctrine, you can focus on your objects and how they're useful in your
application and worry about persisting to MongoDB second. This is because
Doctrine allows you to use any PHP object to hold your data and relies on
mapping metadata information to map an object's data to a MongoDB collection.

And even though Doctrine revolves around a simple concept, it's incredibly
powerful, allowing you to create complex queries and subscribe to events
that allow you to take different actions as objects go through their persistence
lifecycle.

.. _`MongoDB`:          http://www.mongodb.org/
.. _`documentation`:    http://www.doctrine-project.org/docs/mongodb_odm/1.0/en
.. _`Quick Start`:      http://www.mongodb.org/display/DOCS/Quickstart
.. _`Basic Mapping Documentation`: http://www.doctrine-project.org/docs/mongodb_odm/1.0/en/reference/basic-mapping.html
.. _`MongoDB type`: http://us.php.net/manual/en/mongo.types.php
.. _`Mapping Types Documentation`: http://www.doctrine-project.org/docs/mongodb_odm/1.0/en/reference/basic-mapping.html#doctrine-mapping-types
.. _`Query Builder`: http://www.doctrine-project.org/docs/mongodb_odm/1.0/en/reference/query-builder-api.html
.. _`Conditional Operators`: http://www.doctrine-project.org/docs/mongodb_odm/1.0/en/reference/query-builder-api.html#conditional-operators
.. _`Event Documentation`: http://www.doctrine-project.org/docs/mongodb_odm/1.0/en/reference/events.html