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
Standard Distribution, add the following to your ``bin/deps`` file:

.. code-block:: text

    /bundles/Symfony/Bundle DoctrineMongoDBBundle git://github.com/symfony/DoctrineMongoDBBundle.git
    /                       mongodb-odm           git://github.com/doctrine/mongodb-odm.git
    /                       mongodb               git://github.com/doctrine/mongodb.git

Now, update the vendor libraries by running:

.. code-block:: bash

    $ php bin/vendors.php

Next, add the ``Doctrine\ODM\MongoDB`` and ``Doctrine\MongoDB`` namespaces
to the ``app/autoload.php`` file so that these libraries can be autoloaded.
Be sure to add them anywhere *above* the ``Doctrine`` namespace (shown here):

    // app/autoload.php
    $loader->registerNamespaces(array(
        // ...
        'Doctrine\\ODM\\MongoDB'    => __DIR__.'/../vendor/mongodb-odm/lib',
        'Doctrine\\MongoDB'         => __DIR__.'/../vendor/mongodb/lib',
        'Doctrine'                  => __DIR__.'/../vendor/doctrine/lib',
        // ...
    ));

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
        document_managers:
            default:
                auto_mapping: true
                database:     my_test_database
        connections:
            default:

.. note::

    Of course, you'll also need to make sure that the MongoDB server is running
    in the background. For more details, see the MongoDB `Quick Start`_ guide.

A Simple Example: A Product
---------------------------

The best way to understand the Doctrine MongoDB ODM is to see it in action.
In this section, you'll walk through each step needed to create start persisting
document to and from MongoDB.

.. sidebar:: Code along with the example

    If you want to follow along with the example in this chapter, create
    an ``AcmeStoreBundle`` via:
    
    .. code-block:: bash
    
        php app/console init:bundle Acme/StoreBundle src/

    Next, be sure that the new bundle is enabled in the kernel::
    
        // app/AppKernel.php
        
        public function registerBundles()
        {
            $bundles = array(
                // ...
                new Acme\StoreBundle\AcmeStoreBundle(),
            );
        }

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

The class - often called a "document", meaning *a basic class that holds data*
- is simple and helps fulfill the business requirement of needing products
in your application. This class can't be persisted to Doctrine MongoDB yet
- it's just a simple PHP class.

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

        use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

        /**
         * @ODM\Document
         */
        class Product
        {
            /**
             * @ODM\Id
             */
            protected $id;

            /**
             * @ODM\String
             */
            protected $name;

            /**
             * @ODM\Float
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
        </doctrine-mapping>

Doctrine allows you to choose from a wide variety of different field types,
each with their own options. For information on the available field types,
see the :ref:`cookbook-mongodb-field-types` section.

.. seealso::

    You can also check out Doctrine's `Basic Mapping Documentation`_ for
    all details about mapping information. If you use annotations, you'll
    need to prepend all annotations with ``ODM\`` (e.g. ``ODM\String``),
    which is not shown in Doctrine's documentation. You'll also need to include
    the ``use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;`` statement,
    which *imports* the ``ODM`` annotations prefix.

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

    Doctrine doesn't care whether your properties are ``public``, ``protected``
    or ``private``, or whether or not you have a getter or setter function
    for a property. The getters and setters are generated here only because
    you'll need them to interact with your PHP object.

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

* **lines 7-10** In this section, you instantiate and work with the ``$product``
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
  the most efficient query/queries possible.

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
repository object for an entity class via::

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

    $em->remove($product);
    $em->flush();

As you might expect, the ``remove()`` method notifies Doctrine that you'd
like to remove the given entity from the MongoDB. The actual delete operation
however, isn't actually executed until the ``flush()`` method is called.

Querying for Objects
--------------------

Writing Queries
~~~~~~~~~~~~~~~

Using the Query Builder
~~~~~~~~~~~~~~~~~~~~~~~

Custom Repository Classes
~~~~~~~~~~~~~~~~~~~~~~~~~

Doctrine Extensions: Timestampable, Sluggable, etc.
---------------------------------------------------

Doctrine is quite flexible, and a number of third-party extensions are available
that allow you to easily perform repeated and common tasks on your entities.
These include thing such as *Sluggable*, *Timestampable*, *Loggable*, *Translatable*,
and *Tree*.

For more information on how to find and use these extensions, see the cookbook
article about :doc:`using common Doctrine extensions</cookbook/doctrine/common_extensions>`.

.. _book-doctrine-field-types:

Doctrine Field Types Reference
------------------------------

Doctrine comes with a large number of field types available. Each of these
maps a PHP data type to a specific column type in whatever database you're
using. The following types are supported in Doctrine:

-----> Fill in types

For more information, see Doctrine's `Mapping Types documentation`_.

.. index::
   single: Doctrine; ORM Console Commands
   single: CLI; Doctrine ORM

Console Commands
----------------

The Doctrine2 ORM integration offers several console commands under the
``doctrine`` namespace. To view the command list you can run the console
without any arguments:

.. code-block:: bash

    php app/console

A list of available command will print out, many of which start with the
``doctrine:mongodb`` prefix. You can find out more information about any
of these commands (or any Symfony command) by running the ``help`` command.
For example, to get details about the ``doctrine:mongodb:query`` task, run:

.. code-block:: bash

    php app/console help doctrine:mongodb:query

Some notable or interesting tasks include:

----> List cool tasks here

.. note::

   To be able to load data fixtures to your database, you will need to have the
   ``DoctrineFixturesBundle`` bundle installed. To learn how to do it,
   read the ":doc:`/cookbook/doctrine/doctrine_fixtures`" entry of the Cookbook.

.. index::
   single: Configuration; Doctrine MongoDB ODM
   single: Doctrine; MongoDB ODM configuration

Configuration
-------------

.. code-block:: yaml

    # app/config/config.yml
    doctrine_mongodb:
        connections:
            default:
                server: mongodb://localhost:27017
                options:
                    connect: true
        default_database: hello_%kernel.environment%
        document_managers:
            default:
                mappings:
                    AcmeDemoBundle: ~
                metadata_cache_driver: array # array, apc, xcache, memcache

If you wish to use memcache to cache your metadata, you need to configure the
``Memcache`` instance; for example, you can do the following:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        doctrine_mongodb:
            default_database: hello_%kernel.environment%
            connections:
                default:
                    server: mongodb://localhost:27017
                    options:
                        connect: true
            document_managers:
                default:
                    mappings:
                        AcmeDemoBundle: ~
                    metadata_cache_driver:
                        type: memcache
                        class: Doctrine\Common\Cache\MemcacheCache
                        host: localhost
                        port: 11211
                        instance_class: Memcache

    .. code-block:: xml

        <?xml version="1.0" ?>

        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:doctrine_mongodb="http://symfony.com/schema/dic/doctrine/odm/mongodb"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                                http://symfony.com/schema/dic/doctrine/odm/mongodb http://symfony.com/schema/dic/doctrine/odm/mongodb/mongodb-1.0.xsd">

            <doctrine_mongodb:config default-database="hello_%kernel.environment%">
                <doctrine_mongodb:document-manager id="default">
                    <doctrine_mongodb:mapping name="AcmeDemoBundle" />
                    <doctrine_mongodb:metadata-cache-driver type="memcache">
                        <doctrine_mongodb:class>Doctrine\Common\Cache\MemcacheCache</doctrine_mongodb:class>
                        <doctrine_mongodb:host>localhost</doctrine_mongodb:host>
                        <doctrine_mongodb:port>11211</doctrine_mongodb:port>
                        <doctrine_mongodb:instance-class>Memcache</doctrine_mongodb:instance-class>
                    </doctrine_mongodb:metadata-cache-driver>
                </doctrine_mongodb:document-manager>
                <doctrine_mongodb:connection id="default" server="mongodb://localhost:27017">
                    <doctrine_mongodb:options>
                        <doctrine_mongodb:connect>true</doctrine_mongodb:connect>
                    </doctrine_mongodb:options>
                </doctrine_mongodb:connection>
            </doctrine_mongodb:config>
        </container>

Mapping Configuration
~~~~~~~~~~~~~~~~~~~~~

Explicit definition of all the mapped documents is the only necessary
configuration for the ODM and there are several configuration options that you
can control. The following configuration options exist for a mapping:

- ``type`` One of ``annotations``, ``xml``, ``yml``, ``php`` or ``staticphp``.
  This specifies which type of metadata type your mapping uses.
- ``dir`` Path to the mapping or entity files (depending on the driver). If
  this path is relative it is assumed to be relative to the bundle root. This
  only works if the name of your mapping is a bundle name. If you want to use
  this option to specify absolute paths you should prefix the path with the
  kernel parameters that exist in the DIC (for example %kernel.root_dir%).
- ``prefix`` A common namespace prefix that all documents of this mapping
  share. This prefix should never conflict with prefixes of other defined
  mappings otherwise some of your documents cannot be found by Doctrine. This
  option defaults to the bundle namespace + ``Document``, for example for an
  application bundle called ``AcmeHelloBundle``, the prefix would be
  ``Acme\HelloBundle\Document``.
- ``alias`` Doctrine offers a way to alias document namespaces to simpler,
  shorter names to be used in queries or for Repository access.
- ``is_bundle`` This option is a derived value from ``dir`` and by default is
  set to true if dir is relative proved by a ``file_exists()`` check that
  returns false. It is false if the existence check returns true. In this case
  an absolute path was specified and the metadata files are most likely in a
  directory outside of a bundle.

To avoid having to configure lots of information for your mappings you should
follow these conventions:

1. Put all your documents in a directory ``Document/`` inside your bundle. For
   example ``Acme/HelloBundle/Document/``.
2. If you are using xml, yml or php mapping put all your configuration files
   into the ``Resources/config/doctrine/`` directory
   suffixed with mongodb.xml, mongodb.yml or mongodb.php respectively.
3. Annotations is assumed if an ``Document/`` but no
   ``Resources/config/doctrine/`` directory is found.

The following configuration shows a bunch of mapping examples:

.. code-block:: yaml

    doctrine_mongodb:
        document_managers:
            default:
                mappings:
                    MyBundle1: ~
                    MyBundle2: yml
                    MyBundle3: { type: annotation, dir: Documents/ }
                    MyBundle4: { type: xml, dir: Resources/config/doctrine/mapping }
                    MyBundle5:
                        type: yml
                        dir: my-bundle-mappings-dir
                        alias: BundleAlias
                    doctrine_extensions:
                        type: xml
                        dir: %kernel.root_dir%/../src/vendor/DoctrineExtensions/lib/DoctrineExtensions/Documents
                        prefix: DoctrineExtensions\Documents\
                        alias: DExt

Registering Event Listeners and Subscribers
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Doctrine uses the lightweight ``Doctrine\Common\EventManager`` class to trigger
a number of different events which you can hook into. You can register Event
Listeners or Subscribers by tagging the respective services with
``doctrine.odm.mongodb.<connection>_event_listener`` or
``doctrine.odm.mongodb.<connection>_event_subscriber`` using the Dependency Injection
container.

You have to use the name of the MongoDB connection to clearly identify which
connection the listeners should be registered with. If you are using multiple
connections you can hook different events into each connection.

Multiple Connections
~~~~~~~~~~~~~~~~~~~~

If you need multiple connections and document managers you can use the
following syntax:

.. configuration-block

    .. code-block:: yaml

        doctrine_mongodb:
            default_database: hello_%kernel.environment%
            default_connection: conn2
            default_document_manager: dm2
            metadata_cache_driver: apc
            connections:
                conn1:
                    server: mongodb://localhost:27017
                    options:
                        connect: true
                conn2:
                    server: mongodb://localhost:27017
                    options:
                        connect: true
            document_managers:
                dm1:
                    connection: conn1
                    metadata_cache_driver: xcache
                    mappings:
                        AcmeDemoBundle: ~
                dm2:
                    connection: conn2
                    mappings:
                        AcmeHelloBundle: ~

    .. code-block:: xml

        <?xml version="1.0" ?>

        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:doctrine_mongodb="http://symfony.com/schema/dic/doctrine/odm/mongodb"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                                http://symfony.com/schema/dic/doctrine/odm/mongodb http://symfony.com/schema/dic/doctrine/odm/mongodb/mongodb-1.0.xsd">

            <doctrine_mongodb:config
                    default-database="hello_%kernel.environment%"
                    default-document-manager="dm2"
                    default-connection="dm2"
                    proxy-namespace="Proxies"
                    auto-generate-proxy-classes="true">
                <doctrine_mongodb:connection id="conn1" server="mongodb://localhost:27017">
                    <doctrine_mongodb:options>
                        <doctrine_mongodb:connect>true</doctrine_mongodb:connect>
                    </doctrine_mongodb:options>
                </doctrine_mongodb:connection>
                <doctrine_mongodb:connection id="conn2" server="mongodb://localhost:27017">
                    <doctrine_mongodb:options>
                        <doctrine_mongodb:connect>true</doctrine_mongodb:connect>
                    </doctrine_mongodb:options>
                </doctrine_mongodb:connection>
                <doctrine_mongodb:document-manager id="dm1" metadata-cache-driver="xcache" connection="conn1">
                    <doctrine_mongodb:mapping name="AcmeDemoBundle" />
                </doctrine_mongodb:document-manager>
                <doctrine_mongodb:document-manager id="dm2" connection="conn2">
                    <doctrine_mongodb:mapping name="AcmeHelloBundle" />
                </doctrine_mongodb:document-manager>
            </doctrine_mongodb:config>
        </container>

Now you can retrieve the configured services connection services::

    $conn1 = $container->get('doctrine.odm.mongodb.conn1_connection');
    $conn2 = $container->get('doctrine.odm.mongodb.conn2_connection');

And you can also retrieve the configured document manager services which utilize the above
connection services::

    $dm1 = $container->get('doctrine.odm.mongodb.dm1_document_manager');
    $dm2 = $container->get('doctrine.odm.mongodb.dm2_document_manager');

Registering Event Listeners and Subscribers
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Registering events works like described in the :ref:`ORM Bundle documentation <doctrine-event-config>`.
The MongoDB event tags are called "doctrine.odm.mongodb.default_event_listener" and
"doctrine.odm.mongodb.default_event_subscriber" respectively where "default" is the name of the
MongoDB document manager.

.. _`MongoDB`:          http://www.mongodb.org/
.. _`documentation`:    http://www.doctrine-project.org/docs/mongodb_odm/1.0/en
.. _`Quick Start`:      http://www.mongodb.org/display/DOCS/Quickstart
.. _`Basic Mapping Documentation`: http://www.doctrine-project.org/docs/mongodb_odm/1.0/en/reference/basic-mapping.html
.. _`Mapping Types Documentation`: http://www.doctrine-project.org/docs/mongodb_odm/1.0/en/reference/basic-mapping.html#doctrine-mapping-types