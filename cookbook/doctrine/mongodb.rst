How to use MongoDB
==================

.. index::
   pair: Doctrine; MongoDB ODM

The `MongoDB`_ Object Document Mapper is much like the Doctrine2 ORM in the way
it works and architecture. You only deal with plain PHP objects and they are
persisted transparently without imposing on your domain model.

.. tip::

    You can read more about the Doctrine MongoDB Object Document Mapper on the
    projects `documentation`_.

To get started working with Doctrine and the MongoDB Object Document Mapper you
just need to enable it and specify the bundle that contains your mapped documents:

.. code-block:: yaml

    # app/config/config.yml

    doctrine_mongodb:
        document_managers:
            default:
                mappings:
                    AcmeHelloBundle: ~

Now you can start writing documents and mapping them with annotations, xml or
yaml.

.. configuration-block::

    .. code-block:: php-annotations

        // Acme/HelloBundle/Document/User.php

        namespace Acme\HelloBundle\Document;

        /**
         * @mongodb:Document(collection="users")
         */
        class User
        {
            /**
             * @mongodb:Id
             */
            protected $id;

            /**
             * @mongodb:Field(type="string")
             */
            protected $name;

            /**
             * Get id
             *
             * @return integer $id
             */
            public function getId()
            {
                return $this->id;
            }

            /**
             * Set name
             *
             * @param string $name
             */
            public function setName($name)
            {
                $this->name = $name;
            }

            /**
             * Get name
             *
             * @return string $name
             */
            public function getName()
            {
                return $this->name;
            }
        }

    .. code-block:: yaml

        # Acme/HelloBundle/Resources/config/doctrine/Acme.HelloBundle.Document.User.mongodb.yml
        Acme\HelloBundle\Document\User:
            type: document
            collection: user
            fields:
                id:
                    id: true
                name:
                    type: string
                    length: 255

    .. code-block:: xml

        <!-- Acme/HelloBundle/Resources/config/doctrine/Acme.HelloBundle.Document.User.mongodb.xml -->
        <doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping"
              xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
              xsi:schemaLocation="http://doctrine-project.org/schemas/orm/doctrine-mapping
                            http://doctrine-project.org/schemas/orm/doctrine-mapping.xsd">

            <document name="Acme\HelloBundle\Document\User" collection="user">
                <field name="id" id="true" />
                <field name="name" type="string" length="255" />
            </document>

        </doctrine-mapping>

.. note::

    When using annotations in your Symfony2 project you have to namespace all
    Doctrine MongoDB annotations with the ``mongodb:`` prefix.

.. tip::

    If you use YAML or XML to describe your documents, you can omit the creation
    of the Document class, and let the ``doctrine:generate:documents`` command
    do it for you.

Now, use your document and manage its persistent state with Doctrine:

.. code-block:: php

    use Acme\HelloBundle\Document\User;

    class UserController extends Controller
    {
        public function createAction()
        {
            $user = new User();
            $user->setName('Jonathan H. Wage');

            $dm = $this->get('doctrine.odm.mongodb.document_manager');
            $dm->persist($user);
            $dm->flush();

            // ...
        }

        public function editAction($id)
        {
            $dm = $this->get('doctrine.odm.mongodb.document_manager');
            $user = $dm->createQuery('find all from AcmeHelloBundle:User where id = ?', $id);
            $user->setBody('new body');
            $dm->flush();

            // ...
        }

        public function deleteAction($id)
        {
            $dm = $this->get('doctrine.odm.mongodb.document_manager');
            $user = $dm->createQuery('find all from AcmeHelloBundle:User where id = ?', $id);
            $dm->remove($user);
            $dm->flush();

            // ...
        }
    }

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

Writing Document Classes
------------------------

You can start writing document classes just how you normally would write some
PHP classes. The only difference is that you must map the classes to the
MongoDB ODM. You can provide the mapping information via xml, yaml or
annotations. In this example, for simplicity and ease of reading we will use
annotations.

First, let's write a simple User class.

.. code-block:: php

    // src/Acme/HelloBundle/Document/User.php

    namespace Acme\HelloBundle\Document;

    class User
    {
        protected $id;
        protected $name;

        public function getId()
        {
            return $this->id;
        }

        public function setName($name)
        {
            $this->name = $name;
        }

        public function getName()
        {
            return $this->name;
        }
    }

This class can be used independent from any persistence layer as it is a
regular PHP class and does not have any dependencies. Now we need to annotate
the class so Doctrine can read the annotated mapping information from the doc
blocks.

.. code-block:: php-annotations

    // ...

    /** @mongodb:Document(collection="users") */
    class User
    {
        /**
         * @mongodb:Id
         */
        protected $id;

        /**
         * @mongodb:Field(type="string")
         */
        protected $name;

        // ...
    }

Using Documents
---------------

Now that you have a PHP class that has been mapped properly you can begin
working with instances of that document persisting to and retrieving from
MongoDB.

From your controllers you can access the ``DocumentManager`` instance from the
container.

.. code-block:: php

    class UserController extends Controller
    {
        public function createAction()
        {
            $user = new User();
            $user->setName('Jonathan H. Wage');

            $dm = $this->get('doctrine.odm.mongodb.document_manager');
            $dm->persist($user);
            $dm->flush();

            // ...
        }
    }

Later you can retrieve the persisted document by its id.

.. code-block:: php

    class UserController extends Controller
    {
        public function editAction($id)
        {
            $dm = $this->get('doctrine.odm.mongodb.document_manager');
            $user = $dm->find('AcmeHelloBundle:User', $id);

            // ...
        }
    }

Registering Event Listeners and Subscribers
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Registering events works like described in the :ref:`ORM Bundle documentation <doctrine-event-config>`.
The MongoDB event tags are called "doctrine.odm.mongodb.default_event_listener" and
"doctrine.odm.mongodb.default_event_subscriber" respectively where "default" is the name of the
MongoDB document manager.

.. _MongoDB:       http://www.mongodb.org/
.. _documentation: http://www.doctrine-project.org/docs/mongodb_odm/1.0/en
