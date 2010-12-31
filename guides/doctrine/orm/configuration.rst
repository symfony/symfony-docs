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

    doctrine.orm:
        mappings:
            HelloBundle: ~
        auto_generate_proxy_classes: true
        proxy_namespace: Proxies
        proxy_dir: %kernel.cache_dir%/doctrine/orm/Proxies
        default_entity_manager: default
        default_connection: default
        metadata_cache_driver: array
        query_cache_driver: array
        result_cache_driver: array

There are lots of other configuration options that you can use to overwrite
certain classes, but those are for very advanced use-cases only. You should
look at the "orm.xml" file in the DoctrineBundle to get an overview of all the
supported options.

For the caching drivers you can specifiy the values "array", "apc", "memcache"
or "xcache".

The following example shows an overview of the caching configurations:

.. code-block:: yaml

    doctrine.orm:
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

- ``type`` One of "annotations", "xml", "yml", "php" or "static-php". This
  specifies which type of metadata type your mapping uses.
- ``dir`` Path to the mapping or entity files (depending on the driver). If
  this path is relative it is assumed to be relative to the bundle root. This
  only works if the name of your mapping is a bundle name. If you want to use
  this option to specifiy absolute paths you should prefix the path with the
  kernel parameters that exist in the DIC (for example %kernel.dir%).
- ``prefix`` A common namespace prefix that all entities of this mapping
  share. This prefix should never conflict with prefixes of other defined
  mappings otherwise some of your entities cannot be found by Doctrine. This
  option defaults to the bundle namespace + `Entities`, for example for an
  application bundle called "Hello" prefix would be
  "Application\Hello\Entities".
- ``alias`` Doctrine offers a way to alias entity namespaces to simpler,
  shorter names to be used in DQL queries or for Repository access.
- ``is_bundle`` This option is a derived value from ``dir`` and by default is
  set to true if dir is relative proved by a ``file_exists()`` check that
  returns false. It is false if the existance check returns true. In this case
  an absolute path was specified and the metadata files are most likely in a
  directory outside of a bundle.

To avoid having to configure lots of information for your mappings you should
follow these conventions:

1. Put all your entities in a directory Entities/ inside your bundle. For
example "Application/Hello/Entities/".
2. If you are using xml, yml or php mapping put all your configuration files
into the "Resources/config/doctrine/metadata/doctrine/orm/" directory sufficed
with dcm.xml, dcm.yml or dcm.php respectively.
3. Annotations is assumed if an "Entities/" but no
"Resources/config/doctrine/metadata/doctrine/orm/" directory is found.

The following configuration shows a bunch of mapping examples:

.. code-block:: yaml

    doctrine.orm:
        mappings:
            MyBundle1: ~
            MyBundle2: yml
            MyBundle3: { type: annotation, dir: Entities/ }
            MyBundle4: { type: xml, dir: Resources/config/doctrine/mapping }
            MyBundle5:
                type: yml
                dir: my-bundle-mappings-dir
                alias: BundleAlias
            doctrine_extensions:
                type: xml
                dir: %kernel.dir%/../src/vendor/DoctrineExtensions/lib/DoctrineExtensions/Entities
                prefix: DoctrineExtensions\Entities\
                alias: DExt

Multiple Entity Managers
~~~~~~~~~~~~~~~~~~~~~~~~

You can use multiple EntityManagers in a Symfony application. This is
necessary if you are using different databases or even vendors with entirely
different sets of entities.

The following configuration code shows how to define two EntityManagers:

.. code-block:: yaml

    doctrine.orm:
        default_entity_manager:   default
        cache_driver:             apc           # array, apc, memcache, xcache
        entity_managers:
            default:
                connection:       default
            customer:
                connection:       customer

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
