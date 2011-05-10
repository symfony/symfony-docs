.. index::
   pair: Doctrine; ORM

Doctrine ORM
============

`Doctrine`_ is an Object relational mapper (ORM) for PHP that sits on top of a
powerful DataBase Abstraction Layer (DBAL). It provides transparent
persistence for PHP objects.

.. tip::

    You can read more about the Doctrine Object Relational Mapper on the
    official `documentation`_ website.

To get started, enable and configure the :doc:`Doctrine DBAL
</book/doctrine/dbal>`, then enable the ORM. If you follow the conventions
described in this chapter, you just need to tell Doctrine to auto map your
entities:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        doctrine:
            orm:
                auto_mapping: true

    .. code-block:: xml

        <!-- xmlns:doctrine="http://symfony.com/schema/dic/doctrine" -->
        <!-- xsi:schemaLocation="http://symfony.com/schema/dic/doctrine http://symfony.com/schema/dic/doctrine/doctrine-1.0.xsd"> -->

        <doctrine:config>
            <doctrine:orm auto_mapping="true" />
        </doctrine:config>

    .. code-block:: php

        $container->loadFromExtension('doctrine', array('orm' => array(
            "auto_mapping" => true,
        ));

As Doctrine provides transparent persistence for PHP objects, it works with
any PHP class:

.. code-block:: php

    // Acme/HelloBundle/Entity/User.php
    namespace Acme\HelloBundle\Entity;

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

.. tip::

     When defining your entities, you can omit the getter/setter methods and
     let Doctrine create them for you with the ``doctrine:generate:entities``
     command. This only works after you create the mapping information (see
     below).

To let Doctrine manage your classes (entities in Doctrine2 speak), you need to
write mapping information with annotations, XML, or YAML:

.. configuration-block::

    .. code-block:: php-annotations

        // Acme/HelloBundle/Entity/User.php
        namespace Acme\HelloBundle\Entity;

        /**
         * @orm:Entity
         */
        class User
        {
            /**
             * @orm:Id
             * @orm:Column(type="integer")
             * @orm:GeneratedValue(strategy="AUTO")
             */
            protected $id;

            /**
             * @orm:Column(type="string", length="255")
             */
            protected $name;
        }

    .. code-block:: yaml

        # Acme/HelloBundle/Resources/config/doctrine/Acme.HelloBundle.Entity.User.orm.yml
        Acme\HelloBundle\Entity\User:
            type: entity
            table: user
            id:
                id:
                    type: integer
                    generator:
                        strategy: AUTO
            fields:
                name:
                    type: string
                    length: 255

    .. code-block:: xml

        <!-- Acme/HelloBundle/Resources/config/doctrine/Acme.HelloBundle.Entity.User.orm.xml -->
        <doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping"
              xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
              xsi:schemaLocation="http://doctrine-project.org/schemas/orm/doctrine-mapping
                            http://doctrine-project.org/schemas/orm/doctrine-mapping.xsd">

            <entity name="Acme\HelloBundle\Entity\User" table="user">
                <id name="id" type="integer" column="id">
                    <generator strategy="AUTO"/>
                </id>
                <field name="name" column="name" type="string" length="255" />
            </entity>

        </doctrine-mapping>

.. note::

    When using annotations in your Symfony2 project you have to namespace all
    Doctrine ORM annotations with the ``orm:`` prefix.

.. tip::

    If you use YAML or XML to describe your entities, you can omit the creation
    of the Entity class, and let the ``doctrine:generate:entities`` command do
    it for you.

.. tip::

    Instead of creating one file per entity, you can also define all your
    mapping information into a single ``doctrine.orm.yml`` file.

Create the database and the schema related to your metadata information with
the following commands:

.. code-block:: bash

    $ php app/console doctrine:database:create
    $ php app/console doctrine:schema:create

Eventually, use your entity and manage its persistent state with Doctrine:

.. code-block:: php

    // Acme/HelloBundle/Controller/UserController.php
    namespace Acme\HelloBundle\Controller;

    use Acme\HelloBundle\Entity\User;

    class UserController extends Controller
    {
        public function createAction()
        {
            $user = new User();
            $user->setName('Jonathan H. Wage');

            $em = $this->get('doctrine')->getEntityManager();
            $em->persist($user);
            $em->flush();

            // ...
        }

        public function editAction($id)
        {
            $em = $this->get('doctrine')->getEntityManager();
            $user = $em->find('AcmeHelloBundle:User', $id);
            $user->setBody('new body');
            $em->persist($user);
            $em->flush();

            // ...
        }

        public function deleteAction($id)
        {
            $em = $this->get('doctrine')->getEntityManager();
            $user = $em->find('AcmeHelloBundle:User', $id);
            $em->remove($user);
            $em->flush();

            // ...
        }
    }

Now the scenario arises where you want to change your mapping information and
update your development database schema without blowing away everything and
losing your existing data. So first let's just add a new property to our
``User`` entity:

.. code-block:: php

    namespace Acme\HelloBundle\Entity;

    /** @orm:Entity */
    class User
    {
        /** @orm:Column(type="string") */
        protected $new;

        // ...
    }

Once you've done that, to get your database schema updated with the new column
you just need to run the following command:

    $ php app/console doctrine:schema:update

Now your database will be updated and the new column added to the database
table.

.. index::
   single: Configuration; Doctrine ORM
   single: Doctrine; ORM Configuration

Configuration
-------------

In the overview we already described the only necessary configuration option
to get the Doctrine ORM running with Symfony 2. All the other configuration
options are used with reasonable default values.

This following configuration example shows all the configuration defaults that
the ORM resolves to:

.. code-block:: yaml

    doctrine:
        orm:
            auto_mapping: true
            auto_generate_proxy_classes: true
            proxy_namespace: Proxies
            proxy_dir: %kernel.cache_dir%/doctrine/orm/Proxies
            default_entity_manager: default
            metadata_cache_driver: array
            query_cache_driver: array
            result_cache_driver: array

There are lots of other configuration options that you can use to overwrite
certain classes, but those are for very advanced use-cases only. You should
look at the :doc:`configuration reference
</reference/bundle_configuration/DoctrineBundle>` to get an overview of all
the supported options.

For the caching drivers you can specify the values "array", "apc", "memcache"
or "xcache".

The following example shows an overview of the caching configurations:

.. code-block:: yaml

    doctrine:
        orm:
            auto_mapping: true
            metadata_cache_driver: apc
            query_cache_driver: xcache
            result_cache_driver:
                type: memcache
                host: localhost
                port: 11211
                instance_class: Memcache

Mapping Configuration
~~~~~~~~~~~~~~~~~~~~~

Explicit definition of all the mapped entities is the only necessary
configuration for the ORM and there are several configuration options that you
can control. The following configuration options exist for a mapping:

* ``type`` One of ``annotations``, ``xml``, ``yml``, ``php`` or ``staticphp``.
  This specifies which type of metadata type your mapping uses.

* ``dir`` Path to the mapping or entity files (depending on the driver). If
  this path is relative it is assumed to be relative to the bundle root. This
  only works if the name of your mapping is a bundle name. If you want to use
  this option to specify absolute paths you should prefix the path with the
  kernel parameters that exist in the DIC (for example %kernel.root_dir%).

* ``prefix`` A common namespace prefix that all entities of this mapping
  share. This prefix should never conflict with prefixes of other defined
  mappings otherwise some of your entities cannot be found by Doctrine. This
  option defaults to the bundle namespace + ``Entity``, for example for an
  application bundle called ``AcmeHelloBundle`` prefix would be
  ``Acme\HelloBundle\Entity``.

* ``alias`` Doctrine offers a way to alias entity namespaces to simpler,
  shorter names to be used in DQL queries or for Repository access. When using
  a bundle the alias defaults to the bundle name.

* ``is_bundle`` This option is a derived value from ``dir`` and by default is
  set to true if dir is relative proved by a ``file_exists()`` check that
  returns false. It is false if the existence check returns true. In this case
  an absolute path was specified and the metadata files are most likely in a
  directory outside of a bundle.

To avoid having to configure lots of information for your mappings you should
follow these conventions:

1. Put all your entities in a directory ``Entity/`` inside your bundle. For
example ``Acme/HelloBundle/Entity/``.

2. If you are using xml, yml or php mapping put all your configuration files
into the "Resources/config/doctrine/" directory suffixed with ``orm.xml``,
``orm.yml`` or ``orm.php`` respectively.

3. Annotations is assumed if an ``Entity/`` but no
"Resources/config/doctrine/" directory is found.

The following configuration shows a bunch of mapping examples:

.. code-block:: yaml

    doctrine:
        orm:
            auto_mapping: false
            mappings:
                MyBundle1: ~
                MyBundle2: yml
                MyBundle3: { type: annotation, dir: Entity/ }
                MyBundle4: { type: xml, dir: Resources/config/doctrine/mapping }
                MyBundle5:
                    type: yml
                    dir: my-bundle-mappings-dir
                    alias: BundleAlias
                doctrine_extensions:
                    type: xml
                    dir: %kernel.root_dir%/../src/vendor/DoctrineExtensions/lib/DoctrineExtensions/Entity
                    prefix: DoctrineExtensions\Entity\
                    alias: DExt

Multiple Entity Managers
~~~~~~~~~~~~~~~~~~~~~~~~

You can use multiple ``EntityManager``s in a Symfony2 application. This is
necessary if you are using different databases or even vendors with entirely
different sets of entities.

The following configuration code shows how to define two EntityManagers:

.. code-block:: yaml

    doctrine:
        orm:
            default_entity_manager:   default
            cache_driver:             apc           # array, apc, memcache, xcache
            entity_managers:
                default:
                    connection:       default
                    mappings:
                        MyBundle1: ~
                        MyBundle2: ~
                customer:
                    connection:       customer
                    mappings:
                        MyBundle3: ~

Just like the DBAL, if you have configured multiple ``EntityManager``
instances and want to get a specific one, use its name to retrieve it from the
Doctrine registry::

    class UserController extends Controller
    {
        public function indexAction()
        {
            $em =  $this->get('doctrine')->getEntityManager();
            $customerEm =  $this->get('doctrine')->getEntityManager('customer');
        }
    }

.. _doctrine-event-config:

Registering Event Listeners and Subscribers
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Doctrine uses the lightweight ``Doctrine\Common\EventManager`` class to
trigger a number of different events which you can hook into. You can register
Event Listeners or Subscribers by tagging the respective services with
``doctrine.event_listener`` or ``doctrine.event_subscriber`` using the service
container.

To register services to act as event listeners or subscribers (listeners from
here) you have to tag them with the appropriate names. Depending on your
use-case you can hook a listener into every DBAL Connection and ORM Entity
Manager or just into one specific DBAL connection and all the EntityManagers
that use this connection.

.. configuration-block::

    .. code-block:: yaml

        doctrine:
            dbal:
                default_connection: default
                connections:
                    default:
                        driver: pdo_sqlite
                        memory: true

        services:
            my.listener:
                class: MyEventListener
                tags:
                    - { name: doctrine.event_listener }
            my.listener2:
                class: MyEventListener2
                tags:
                    - { name: doctrine.event_listener, connection: default }
            my.subscriber:
                class: MyEventSubscriber
                tags:
                    - { name: doctrine.event_subscriber, connection: default }

    .. code-block:: xml

        <?xml version="1.0" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:doctrine="http://symfony.com/schema/dic/doctrine">

            <doctrine:config>
                <doctrine:dbal default-connection="default">
                    <doctrine:connection driver="pdo_sqlite" memory="true" />
                </doctrine:dbal>
            </doctrine:config>

            <services>
                <service id="my.listener" class="MyEventListener">
                    <tag name="doctrine.event_listener" />
                </service>
                <service id="my.listener2" class="MyEventListener2">
                    <tag name="doctrine.event_listener" connection="default" />
                </service>
                <service id="my.subscriber" class="MyEventSubscriber">
                    <tag name="doctrine.event_subscriber" connection="default" />
                </service>
            </services>
        </container>

.. index::
   single: Doctrine; ORM Console Commands
   single: CLI; Doctrine ORM

Console Commands
----------------

The Doctrine2 ORM integration offers several console commands under the
``doctrine`` namespace. To view the command list you can run the console
without any arguments or options:

.. code-block:: bash

    $ php app/console
    ...

    doctrine
      :ensure-production-settings  Verify that Doctrine is properly configured for a production environment.
      :schema-tool                 Processes the schema and either apply it directly on EntityManager or generate the SQL output.
    doctrine:cache
      :clear-metadata              Clear all metadata cache for a entity manager.
      :clear-query                 Clear all query cache for a entity manager.
      :clear-result                Clear result cache for a entity manager.
    doctrine:fixtures
      :load                        Load data fixtures to your database.
    doctrine:database
      :create                      Create the configured databases.
      :drop                        Drop the configured databases.
    doctrine:generate
      :entities                    Generate entity classes and method stubs from your mapping information.
      :entity                      Generate a new Doctrine entity inside a bundle.
      :proxies                     Generates proxy classes for entity classes.
      :repositories                Generate repository classes from your mapping information.
    doctrine:mapping
      :convert                     Convert mapping information between supported formats.
      :convert-d1-schema           Convert a Doctrine1 schema to Doctrine2 mapping files.
      :import                      Import mapping information from an existing database.
    doctrine:query
      :dql                         Executes arbitrary DQL directly from the command line.
      :sql                         Executes arbitrary SQL directly from the command line.
    doctrine:schema
      :create                      Processes the schema and either create it directly on EntityManager Storage Connection or generate the SQL output.
      :drop                        Processes the schema and either drop the database schema of EntityManager Storage Connection or generate the SQL output.
      :update                      Processes the schema and either update the database schema of EntityManager Storage Connection or generate the SQL output.

    ...

.. note::

   To be able to load data fixtures to your database, you will need to have the
   ``DoctrineFixturesBundle`` bundle installed. To learn how to do it,
   read the ":doc:`/cookbook/doctrine/doctrine_fixtures`" entry of the Cookbook.

Form Integration
----------------

There is a tight integration between Doctrine ORM and the Symfony2 Form
component. Since Doctrine Entities are plain old php objects they nicely
integrate into the Form component by default, at least for the primitive data
types such as strings, integers and fields. However you can also integrate
them nicely with associations.

This is done by the help of a dedicated type:
:class:`Symfony\\Bridge\\Doctrine\\Form\\Type\\EntityType`. It provides a list
of choices from which an entity can be selected::

    use Symfony\Bridge\Doctrine\Form\Type\EntityType;

    $builder->add('users','entity',
         array('class' => 'Acme\\HelloBundle\\Entity\\User',
    ));

The required ``class`` option expects the Entity class name as an argument.
The optional ``property`` option allows you to choose the property used to
display the entity (``__toString`` will be used if not set). The optional
'query_builder' option expects a ``QueryBuilder`` instance or a closure
receiving the repository as an argument and returning the QueryBuilder used to
get the choices. If not set all entities will be used.

.. _documentation: http://www.doctrine-project.org/docs/orm/2.0/en
.. _Doctrine:      http://www.doctrine-project.org
