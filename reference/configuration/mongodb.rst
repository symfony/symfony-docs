Doctrine MongoDB Configuration
==============================

Sample Configuration
--------------------

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

3. Annotations is assumed if a ``Document/`` but no
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

Full Default Configuration
~~~~~~~~~~~~~~~~~~~~~~~~~~

.. configuration-block::

    .. code-block:: yaml

        doctrine_mongodb:
            document_managers:

                # Prototype
                id:
                    connection:           ~
                    database:             ~
                    logging:              true
                    auto_mapping:         false
                    metadata_cache_driver:
                        type:                 ~
                        class:                ~
                        host:                 ~
                        port:                 ~
                        instance_class:       ~
                    mappings:

                        # Prototype
                        name:
                            mapping:              true
                            type:                 ~
                            dir:                  ~
                            prefix:               ~
                            alias:                ~
                            is_bundle:            ~
            connections:

                # Prototype
                id:
                    server:               ~
                    options:
                        connect:              ~
                        persist:              ~
                        timeout:              ~
                        replicaSet:           ~
                        username:             ~
                        password:             ~
            proxy_namespace:      Proxies
            proxy_dir:            %kernel.cache_dir%/doctrine/odm/mongodb/Proxies
            auto_generate_proxy_classes:  false
            hydrator_namespace:   Hydrators
            hydrator_dir:         %kernel.cache_dir%/doctrine/odm/mongodb/Hydrators
            auto_generate_hydrator_classes:  false
            default_document_manager:  ~
            default_connection:   ~
            default_database:     default


