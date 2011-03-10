.. index::
   single: Configuration; Doctrine ORM
   single: Doctrine; ORM Configuration

Configuration
=============

In the overview we already described the only necessary configuration option
"mappings" to get the Doctrine ORM running with Symfony 2. All the other
configuration options are used with reasonable default values.

This following configuration example shows all the configuration defaults that
the ORM resolves to:

.. code-block:: yaml

    doctrine:
        orm:
            auto_generate_proxy_classes: true
            proxy_namespace: Proxies
            proxy_dir: %kernel.cache_dir%/doctrine/orm/Proxies
            default_entity_manager: default
            entity_managers:
                default:
                    mappings:
                        HelloBundle: ~
                    metadata_cache_driver: array
                    query_cache_driver: array
                    result_cache_driver: array

There are lots of other configuration options that you can use to overwrite
certain classes, but those are for very advanced use-cases only. You should
look at the
:doc:`configuration reference </reference/bundle_configuration/DoctrineBundle>`
to get an overview of all the supported options.

.. note::

    The ``default_entity_manager`` parameter is mandatory and you have to define
    at least one entity manager. Thus the ``mappings`` configuration is
    mandatory for each entity manager.

For the caching drivers you can specifiy the values "array", "apc", "memcache"
or "xcache".

The following example shows an overview of the caching configurations:

.. code-block:: yaml

    doctrine:
        orm:
            default_entity_manager: default
            entity_managers:
                default:
                    mappings:
                        HelloBundle: ~
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

- ``type`` One of "annotation", "xml", "yml", "php" or "static-php". This
  specifies which type of metadata type your mapping uses.
- ``dir`` Path to the mapping or entity files (depending on the driver). If
  this path is relative it is assumed to be relative to the bundle root. This
  only works if the name of your mapping is a bundle name. If you want to use
  this option to specifiy absolute paths you should prefix the path with the
  kernel parameters that exist in the DIC (for example %kernel.dir%).
- ``prefix`` A common namespace prefix that all entities of this mapping
  share. This prefix should never conflict with prefixes of other defined
  mappings otherwise some of your entities cannot be found by Doctrine. This
  option defaults to the bundle namespace + ``Entity``, for example for an
  application bundle called "HelloBundle" prefix would be
  ``Sensio\HelloBundle\Entity``.
- ``alias`` Doctrine offers a way to alias entity namespaces to simpler,
  shorter names to be used in DQL queries or for Repository access. When using a
  bundle the alias defaults to the bundle name.
- ``is_bundle`` This option is a derived value from ``dir`` and by default is
  set to true if dir is relative proved by a ``file_exists()`` check that
  returns false. It is false if the existence check returns true. In this case
  an absolute path was specified and the metadata files are most likely in a
  directory outside of a bundle.

To avoid having to configure lots of information for your mappings you should
follow these conventions:

1. Put all your entities in a directory ``Entity/`` inside your bundle. For
   example ``Sensio/HelloBundle/Entity/``.
2. If you are using xml, yml or php mapping put all your configuration files
   into the "Resources/config/doctrine/metadata/doctrine/orm/" directory
   suffixed with dcm.xml, dcm.yml or dcm.php respectively.
3. Annotations is assumed if an ``Entity/`` but no
   "Resources/config/doctrine/metadata/doctrine/orm/" directory is found.

The following configuration shows a bunch of mapping examples:

.. code-block:: yaml

    doctrine:
        orm:
            default_entity_manager: default
            entity_managers:
                default:
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
                            dir: %kernel.dir%/../src/vendor/DoctrineExtensions/lib/DoctrineExtensions/Entity
                            prefix: DoctrineExtensions\Entity\
                            alias: DExt

Registering Event Listeners and Subscribers
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Doctrine uses the lightweight ``Doctrine\Common\EventManager`` class to trigger
a number of different events which you can hook into. You can register Event
Listeners or Subscribers by tagging the respective services with
``doctrine.dbal.<connection>_event_listener`` or
``doctrine.dbal.<connection>_event_subscriber`` using the Dependency Injenction
container.

You have to use the name of the DBAL connection to clearly identify which
connection the listeners should be registered with. If you are using multiple
connections you can hook different events into each connection.

.. code-block:: xml

    <container xmlns="http://symfony.com/schema/dic/services"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

        <services>

            <service id="doctrine.extensions.versionable_listener" class="DoctrineExtensions\Versionable\VersionableListener">
                <tag name="doctrine.dbal.default_event_subscriber" />
            </service>

            <service id="mybundle.doctrine.mylistener" class="MyBundle\Doctrine\MyListener">
                <tag name="doctrine.dbal.default_event_listener" event="prePersist" />
            </service>

        </services>

    </container>

Although the Event Listener and Subscriber tags are prefixed with ``doctrine.dbal``
these tags also work for the ORM events. Internally Doctrine re-uses the EventManager
that is registered with the connection for the ORM.

Multiple Entity Managers
~~~~~~~~~~~~~~~~~~~~~~~~

You can use multiple EntityManagers in a Symfony application. This is
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
instances and want to get a specific one you can use the full service name to
retrieve it from the Symfony Dependency Injection Container::

    class UserController extends Controller
    {
        public function indexAction()
        {
            $em =  $this->get('doctrine.orm.entity_manager');
            $defaultEm =  $this->get('doctrine.orm.default_entity_manager');
            $customerEm = $this->get('doctrine.orm.customer_entity_manager');

            // $em === $defaultEm => true
            // $defaultEm === $customerEm => false
        }
    }

The service "doctrine.orm.entity_manager" is an alias for the default entity
manager defined in the "default_entity_manager" configuration option.

.. _doctrine-event-config:

Registering Event Listeners and Subscribers
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Doctrine ships with an event system that allows to hook into many different
events happening during the lifecycle of entities or at other occasions.

To register services to act as event listeners or subscribers (listeners from here)
you have to tag them with the appropriate names. Depending on your use-case you can hook
a listener into every DBAL Connection and ORM Entity Manager or just into one
specific DBAL connection and all the EntityManagers that use this connection.

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
                - { name: doctrine.common.event_listener }
            my.listener2:
                class: MyEventListener2
                tags:
                - { name: doctrine.dbal.default_event_listener }
            my.subscriber:
                class: MyEventSubscriber
                tags:
                - { name: doctrine.dbal.default_event_subscriber }

    .. code-block:: xml

        <?xml version="1.0" ?>
        <container xmlns="http://symfony-project.org/2.0/container"
            xmlns:doctrine="http://symfony.com/schema/dic/doctrine">

            <doctrine:config>
                <doctrine:dbal default-connection="default">
                    <doctrine:connection driver="pdo_sqlite" memory="true" />
                </doctrine:dbal>
            </doctrine:config>

            <services>
                <service id="my.listener" class="MyEventListener">
                    <tag name="doctrine.common.event_listener" />
                </service>
                <service id="my.listener2" class="MyEventListener2">
                    <tag name="doctrine.dbal.default_event_listener" />
                </service>
                <service id="my.subscriber" class="MyEventSubscriber">
                    <tag name="doctrine.dbal.default_event_subscriber" />
                </service>
            </services>
        </container>
