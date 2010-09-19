.. index::
   single: Configuration; Doctrine MongoDB ODM
   single: Doctrine; MongoDB ODM configuration

Configuration
=============

.. code-block:: yaml

    # config/config.yml
    doctrine_odm.mongodb:
        server: mongodb://localhost:27017
        default_database: hello_%kernel.environment%
        options:
            connect: true
        metadata_cache_driver: array # array, apc, xcache, memcache

If you wish to use memcache to cache your metadata and you need to configure the ``Memcache`` instance you can do the following:

.. code-block:: yaml

    # config/config.yml
    doctrine_odm.mongodb:
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

Multiple Connections
~~~~~~~~~~~~~~~~~~~~

If you need multiple connections and document managers you can use the following syntax:

.. code-block:: yaml

    doctrine_odm.mongodb:
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

    $conn1 = $container['doctrine.odm.mongodb.conn1_connection'];
    $conn2 = $container['doctrine.odm.mongodb.conn2_connection'];

And you can also retrieve the configured document manager services which utilize the above
connection services::

    $dm1 = $container['doctrine.odm.mongodb.dm1_document_manager'];
    $dm2 = $container['doctrine.odm.mongodb.dm1_document_manager'];

XML
~~~

You can specify the same configuration via XML if you prefer that. Here are the same
examples from above in XML.

Simple Single Connection:

.. code-block:: xml

    <?xml version="1.0" ?>

    <container xmlns="http://www.symfony-project.org/schema/dic/services"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xmlns:doctrine="http://www.symfony-project.org/schema/dic/doctrine/odm/mongodb"
        xsi:schemaLocation="http://www.symfony-project.org/schema/dic/services http://www.symfony-project.org/schema/dic/services/services-1.0.xsd
                            http://www.symfony-project.org/schema/dic/doctrine/odm/mongodb http://www.symfony-project.org/schema/dic/doctrine/odm/mongodb/mongodb-1.0.xsd">

        <doctrine:mongodb server="mongodb://localhost:27017"
                          default_database="hello_%kernel.environment%">
            <metadata_cache_driver type="memcache">
                <class>Doctrine\Common\Cache\MemcacheCache</class>
                <host>localhost</host>
                <port>11211</port>
                <instance_class>Memcache</instance_class>
            </metadata_cache_driver>
            <options>
                <connect>true</connect>
            </options>
        </doctrine:mongodb>
    </container>

Multiple Connections:

.. code-block:: xml

    <?xml version="1.0" ?>

    <container xmlns="http://www.symfony-project.org/schema/dic/services"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xmlns:doctrine="http://www.symfony-project.org/schema/dic/doctrine/odm/mongodb"
        xsi:schemaLocation="http://www.symfony-project.org/schema/dic/services http://www.symfony-project.org/schema/dic/services/services-1.0.xsd
                            http://www.symfony-project.org/schema/dic/doctrine/odm/mongodb http://www.symfony-project.org/schema/dic/doctrine/odm/mongodb/mongodb-1.0.xsd">

        <doctrine:mongodb default_database="hello_%kernel.environment%"
                          metadata_cache_driver="apc"
                          default_document_manager="dm2"
                          default_connection="dm2"
                          proxy_namespace="Proxies"
                          auto_generate_proxy_classes="true">
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
            <doctrine:document_managers>
                <doctrine:document_manager id="dm1" server="mongodb://localhost:27017" metadata_cache_driver="xcache" connection="conn1" />
                <doctrine:document_manager id="dm2" server="mongodb://localhost:27017" connection="conn2" />
            </doctrine:document_managers>
        </doctrine:mongodb>
    </container>

Writing Document Classes
------------------------

You can start writing document classes just how you normally would write some PHP classes.
The only difference is that you must map the classes to the MongoDB ODM. You can provide
the mapping information via xml, yaml or annotations. In this example, for simplicity and
ease of reading we will use annotations.

First, lets write a simple User class::

    // src/Application/HelloBundle/Document/User.php

    namespace Application\HelloBundle\Document;

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

This class can be used independent from any persistence layer as it is a regular PHP
class and does not have any dependencies. Now we need to annotate the class so Doctrine
can read the annotated mapping information from the doc blocks::

    // ...

    /** @Document(collection="users") */
    class User
    {
        /** @Id */
        protected $id;

        /** @String */
        protected $name;

        // ...
    }

Using Documents
---------------

Now that you have a PHP class that has been mapped properly you can begin working with
instances of that document persisting to and retrieving from MongoDB.

From your controllers you can access the ``DocumentManager`` instances from
the container::

    class UserController extends Controller
    {
        public function createAction()
        {
            $user = new User();
            $user->setName('Jonathan H. Wage');

            $dm = $this['doctrine.odm.mongodb.document_manager'];
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
            $dm = $this['doctrine.odm.mongodb.document_manager'];
            $user = $dm->find('HelloBundle:User', $id);

            // ...
        }
    }

.. _MongoDB:       http://www.mongodb.org/
.. _documentation: http://www.doctrine-project.org/projects/mongodb_odm/1.0/docs/en
