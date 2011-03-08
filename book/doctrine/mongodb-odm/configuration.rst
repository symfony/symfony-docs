.. index::
   single: Configuration; Doctrine MongoDB ODM
   single: Doctrine; MongoDB ODM configuration

Configuration
=============

.. code-block:: yaml

    # app/config/config.yml
    doctrine_mongo_db:
        server: mongodb://localhost:27017
        default_database: hello_%kernel.environment%
        options:
            connect: true
        metadata_cache_driver: array # array, apc, xcache, memcache

If you wish to use memcache to cache your metadata, you need to configure the
``Memcache`` instance you can do the following:

.. code-block:: yaml

    # app/config/config.yml
    doctrine_mongo_db:
        server: mongodb://localhost:27017
        default_database: hello_%kernel.environment%
        options:
            connect: true
        metadata_cache_driver:
            type: memcache
            class: Doctrine\Common\Cache\MemcacheCache
            host: localhost
            port: 11211
            instance_class: Memcache

Mapping Configuration
~~~~~~~~~~~~~~~~~~~~~

Explicit definition of all the mapped documents is the only necessary
configuration for the ODM and there are several configuration options that you
can control. The following configuration options exist for a mapping:

- ``type`` One of "annotations", "xml", "yml", "php" or "static-php". This
  specifies which type of metadata type your mapping uses.
- ``dir`` Path to the mapping or entity files (depending on the driver). If
  this path is relative it is assumed to be relative to the bundle root. This
  only works if the name of your mapping is a bundle name. If you want to use
  this option to specify absolute paths you should prefix the path with the
  kernel parameters that exist in the DIC (for example %kernel.dir%).
- ``prefix`` A common namespace prefix that all documents of this mapping
  share. This prefix should never conflict with prefixes of other defined
  mappings otherwise some of your documents cannot be found by Doctrine. This
  option defaults to the bundle namespace + ``Document``, for example for an
  application bundle called "Hello" prefix would be
  ``Sensio\Hello\Document``.
- ``alias`` Doctrine offers a way to alias document namespaces to simpler,
  shorter names to be used in queries or for Repository access.
- ``is_bundle`` This option is a derived value from ``dir`` and by default is
  set to true if dir is relative proved by a ``file_exists()`` check that
  returns false. It is false if the existence check returns true. In this case
  an absolute path was specified and the metadata files are most likely in a
  directory outside of a bundle.

To avoid having to configure lots of information for your mappings you should
follow these conventions:

1. Put all your entities in a directory ``Document/`` inside your bundle. For
   example ``Sensio/Hello/Document/``.
2. If you are using xml, yml or php mapping put all your configuration files
   into the ``Resources/config/doctrine/metadata/doctrine/mongodb/`` directory
   sufficed with dcm.xml, dcm.yml or dcm.php respectively.
3. Annotations is assumed if an ``Document/`` but no
   ``Resources/config/doctrine/metadata/doctrine/mongodb/`` directory is found.

The following configuration shows a bunch of mapping examples:

.. code-block:: yaml

    doctrine_mongo_db:
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
                dir: %kernel.dir%/../src/vendor/DoctrineExtensions/lib/DoctrineExtensions/Documents
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

.. code-block:: yaml

    doctrine_mongo_db:
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
            dm2:
                connection: conn2

Now you can retrieve the configured services connection services::

    $conn1 = $container->get('doctrine.odm.mongodb.conn1_connection');
    $conn2 = $container->get('doctrine.odm.mongodb.conn2_connection');

And you can also retrieve the configured document manager services which utilize the above
connection services::

    $dm1 = $container->get('doctrine.odm.mongodb.dm1_document_manager');
    $dm2 = $container->get('doctrine.odm.mongodb.dm2_document_manager');

XML
~~~

You can specify the same configuration via XML if you prefer that. Here are
the same examples from above in XML.

Simple Single Connection:

.. code-block:: xml

    <?xml version="1.0" ?>

    <container xmlns="http://symfony.com/schema/dic/services"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xmlns:doctrine="http://symfony.com/schema/dic/doctrine/odm/mongodb"
        xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                            http://symfony.com/schema/dic/doctrine/odm/mongodb http://symfony.com/schema/dic/doctrine/odm/mongodb/mongodb-1.0.xsd">

        <doctrine:mongodb server="mongodb://localhost:27017"
                          default-database="hello_%kernel.environment%">
            <metadata-cache-driver type="memcache">
                <class>Doctrine\Common\Cache\MemcacheCache</class>
                <host>localhost</host>
                <port>11211</port>
                <instance-class>Memcache</instance_class>
            </metadata-cache-driver>
            <options>
                <connect>true</connect>
            </options>
        </doctrine:mongodb>
    </container>

Multiple Connections:

.. code-block:: xml

    <?xml version="1.0" ?>

    <container xmlns="http://symfony.com/schema/dic/services"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xmlns:doctrine="http://symfony.com/schema/dic/doctrine/odm/mongodb"
        xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                            http://symfony.com/schema/dic/doctrine/odm/mongodb http://symfony.com/schema/dic/doctrine/odm/mongodb/mongodb-1.0.xsd">

        <doctrine:mongodb default-database="hello_%kernel.environment%"
                          metadata-cache-driver="apc"
                          default-document-manager="dm2"
                          default-connection="dm2"
                          proxy-namespace="Proxies"
                          auto-generate-proxy-classes="true">
            <doctrine:connections>
                <doctrine:connection id="conn1" server="mongodb://localhost:27017">
                    <options>
                        <connect>true</connect>
                    </options>
                </doctrine:connection>
                <doctrine:connection id="conn2" server="mongodb://localhost:27017">
                    <options>
                        <connect>true</connect>
                    </options>
                </doctrine:connection>
            </doctrine:connections>
            <doctrine:document-managers>
                <doctrine:document-manager id="dm1" server="mongodb://localhost:27017" metadata-cache-driver="xcache" connection="conn1" />
                <doctrine:document-manager id="dm2" server="mongodb://localhost:27017" connection="conn2" />
            </doctrine:document-managers>
        </doctrine:mongodb>
    </container>

Writing Document Classes
------------------------

You can start writing document classes just how you normally would write some
PHP classes. The only difference is that you must map the classes to the
MongoDB ODM. You can provide the mapping information via xml, yaml or
annotations. In this example, for simplicity and ease of reading we will use
annotations.

First, lets write a simple User class::

    // src/Sensio/HelloBundle/Document/User.php

    namespace Sensio\HelloBundle\Document;

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
blocks::

    // ...

    /** @mongodb:Document(collection="users") */
    class User
    {
        /** @mongodb:Id */
        protected $id;

        /** @mongodb:String */
        protected $name;

        // ...
    }

Using Documents
---------------

Now that you have a PHP class that has been mapped properly you can begin
working with instances of that document persisting to and retrieving from
MongoDB.

From your controllers you can access the ``DocumentManager`` instance from the
container::

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

Later you can retrieve the persisted document by its id::

    class UserController extends Controller
    {
        public function editAction($id)
        {
            $dm = $this->get('doctrine.odm.mongodb.document_manager');
            $user = $dm->find('HelloBundle:User', $id);

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
.. _documentation: http://www.doctrine-project.org/projects/mongodb_odm/1.0/docs/en
