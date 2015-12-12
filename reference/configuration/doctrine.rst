.. index::
    single: Doctrine; ORM configuration reference
    single: Configuration reference; Doctrine ORM

DoctrineBundle Configuration ("doctrine")
=========================================

Full Default Configuration
--------------------------

.. configuration-block::

    .. code-block:: yaml

        doctrine:
            dbal:
                default_connection:   default
                types:
                    # A collection of custom types
                    # Example
                    some_custom_type:
                        class:                Acme\HelloBundle\MyCustomType
                        commented:            true
                # If enabled all tables not prefixed with sf2_ will be ignored by the schema
                # tool. This is for custom tables which should not be altered automatically.
                #schema_filter:        ^sf2_

                connections:
                    # A collection of different named connections (e.g. default, conn2, etc)
                    default:
                        dbname:               ~
                        host:                 localhost
                        port:                 ~
                        user:                 root
                        password:             ~
                        charset:              ~
                        path:                 ~
                        memory:               ~

                        # The unix socket to use for MySQL
                        unix_socket:          ~

                        # True to use as persistent connection for the ibm_db2 driver
                        persistent:           ~

                        # The protocol to use for the ibm_db2 driver (default to TCPIP if omitted)
                        protocol:             ~

                        # True to use dbname as service name instead of SID for Oracle
                        service:              ~

                        # The session mode to use for the oci8 driver
                        sessionMode:          ~

                        # True to use a pooled server with the oci8 driver
                        pooled:               ~

                        # Configuring MultipleActiveResultSets for the pdo_sqlsrv driver
                        MultipleActiveResultSets:  ~
                        driver:               pdo_mysql
                        platform_service:     ~

                        # the version of your database engine
                        server_version:       ~

                        # when true, queries are logged to a 'doctrine' monolog channel
                        logging:              '%kernel.debug%'
                        profiling:            '%kernel.debug%'
                        driver_class:         ~
                        wrapper_class:        ~
                        options:
                            # an array of options
                            key:                  []
                        mapping_types:
                            # an array of mapping types
                            name:                 []
                        slaves:

                            # a collection of named slave connections (e.g. slave1, slave2)
                            slave1:
                                dbname:               ~
                                host:                 localhost
                                port:                 ~
                                user:                 root
                                password:             ~
                                charset:              ~
                                path:                 ~
                                memory:               ~

                                # The unix socket to use for MySQL
                                unix_socket:          ~

                                # True to use as persistent connection for the ibm_db2 driver
                                persistent:           ~

                                # The protocol to use for the ibm_db2 driver (default to TCPIP if omitted)
                                protocol:             ~

                                # True to use dbname as service name instead of SID for Oracle
                                service:              ~

                                # The session mode to use for the oci8 driver
                                sessionMode:          ~

                                # True to use a pooled server with the oci8 driver
                                pooled:               ~

                                # the version of your database engine
                                server_version:       ~

                                # Configuring MultipleActiveResultSets for the pdo_sqlsrv driver
                                MultipleActiveResultSets:  ~

            orm:
                default_entity_manager:  ~
                auto_generate_proxy_classes:  false
                proxy_dir:            '%kernel.cache_dir%/doctrine/orm/Proxies'
                proxy_namespace:      Proxies
                # search for the "ResolveTargetEntityListener" class for a cookbook about this
                resolve_target_entities: []
                entity_managers:
                    # A collection of different named entity managers (e.g. some_em, another_em)
                    some_em:
                        query_cache_driver:
                            type:                 array # Required
                            host:                 ~
                            port:                 ~
                            instance_class:       ~
                            class:                ~
                        metadata_cache_driver:
                            type:                 array # Required
                            host:                 ~
                            port:                 ~
                            instance_class:       ~
                            class:                ~
                        result_cache_driver:
                            type:                 array # Required
                            host:                 ~
                            port:                 ~
                            instance_class:       ~
                            class:                ~
                        connection:           ~
                        class_metadata_factory_name:  Doctrine\ORM\Mapping\ClassMetadataFactory
                        default_repository_class:  Doctrine\ORM\EntityRepository
                        auto_mapping:         false
                        hydrators:

                            # An array of hydrator names
                            hydrator_name:                 []
                        mappings:
                            # An array of mappings, which may be a bundle name or something else
                            mapping_name:
                                mapping:              true
                                type:                 ~
                                dir:                  ~
                                alias:                ~
                                prefix:               ~
                                is_bundle:            ~
                        dql:
                            # a collection of string functions
                            string_functions:
                                # example
                                # test_string: Acme\HelloBundle\DQL\StringFunction

                            # a collection of numeric functions
                            numeric_functions:
                                # example
                                # test_numeric: Acme\HelloBundle\DQL\NumericFunction

                            # a collection of datetime functions
                            datetime_functions:
                                # example
                                # test_datetime: Acme\HelloBundle\DQL\DatetimeFunction

                        # Register SQL Filters in the entity manager
                        filters:
                            # An array of filters
                            some_filter:
                                class:                ~ # Required
                                enabled:              false

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:doctrine="http://symfony.com/schema/dic/doctrine"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/doctrine
                http://symfony.com/schema/dic/doctrine/doctrine-1.0.xsd">

            <doctrine:config>
                <doctrine:dbal default-connection="default">
                    <doctrine:connection
                        name="default"
                        dbname="database"
                        host="localhost"
                        port="1234"
                        user="user"
                        password="secret"
                        driver="pdo_mysql"
                        driver-class="MyNamespace\MyDriverImpl"
                        path="%kernel.data_dir%/data.sqlite"
                        memory="true"
                        unix-socket="/tmp/mysql.sock"
                        wrapper-class="MyDoctrineDbalConnectionWrapper"
                        charset="UTF8"
                        logging="%kernel.debug%"
                        platform-service="MyOwnDatabasePlatformService"
                        server-version="5.6"
                    >
                        <doctrine:option key="foo">bar</doctrine:option>
                        <doctrine:mapping-type name="enum">string</doctrine:mapping-type>
                    </doctrine:connection>
                    <doctrine:connection name="conn1" />
                    <doctrine:type name="custom">Acme\HelloBundle\MyCustomType</doctrine:type>
                </doctrine:dbal>

                <doctrine:orm
                    default-entity-manager="default"
                    auto-generate-proxy-classes="false"
                    proxy-namespace="Proxies"
                    proxy-dir="%kernel.cache_dir%/doctrine/orm/Proxies"
                >
                    <doctrine:entity-manager
                        name="default"
                        query-cache-driver="array"
                        result-cache-driver="array"
                        connection="conn1"
                        class-metadata-factory-name="Doctrine\ORM\Mapping\ClassMetadataFactory"
                    >
                        <doctrine:metadata-cache-driver
                            type="memcache"
                            host="localhost"
                            port="11211"
                            instance-class="Memcache"
                            class="Doctrine\Common\Cache\MemcacheCache"
                        />

                        <doctrine:mapping name="AcmeHelloBundle" />

                        <doctrine:dql>
                            <doctrine:string-function name="test_string">
                                Acme\HelloBundle\DQL\StringFunction
                            </doctrine:string-function>

                            <doctrine:numeric-function name="test_numeric">
                                Acme\HelloBundle\DQL\NumericFunction
                            </doctrine:numeric-function>

                            <doctrine:datetime-function name="test_datetime">
                                Acme\HelloBundle\DQL\DatetimeFunction
                            </doctrine:datetime-function>
                        </doctrine:dql>
                    </doctrine:entity-manager>

                    <doctrine:entity-manager name="em2" connection="conn2" metadata-cache-driver="apc">
                        <doctrine:mapping
                            name="DoctrineExtensions"
                            type="xml"
                            dir="%kernel.root_dir%/../vendor/gedmo/doctrine-extensions/lib/DoctrineExtensions/Entity"
                            prefix="DoctrineExtensions\Entity"
                            alias="DExt"
                        />
                    </doctrine:entity-manager>
                </doctrine:orm>
            </doctrine:config>
        </container>

Configuration Overview
----------------------

This following configuration example shows all the configuration defaults
that the ORM resolves to:

.. code-block:: yaml

    doctrine:
        orm:
            auto_mapping: true
            # the standard distribution overrides this to be true in debug, false otherwise
            auto_generate_proxy_classes: false
            proxy_namespace: Proxies
            proxy_dir: '%kernel.cache_dir%/doctrine/orm/Proxies'
            default_entity_manager: default
            metadata_cache_driver: array
            query_cache_driver: array
            result_cache_driver: array

There are lots of other configuration options that you can use to overwrite
certain classes, but those are for very advanced use-cases only.

Caching Drivers
~~~~~~~~~~~~~~~

For the caching drivers you can specify the values ``array``, ``apc``, ``memcache``,
``memcached``, ``redis``, ``wincache``, ``zenddata``, ``xcache`` or ``service``.

The following example shows an overview of the caching configurations:

.. code-block:: yaml

    doctrine:
        orm:
            auto_mapping: true
            metadata_cache_driver: apc
            query_cache_driver:
                type: service
                id: my_doctrine_common_cache_service
            result_cache_driver:
                type: memcache
                host: localhost
                port: 11211
                instance_class: Memcache

Mapping Configuration
~~~~~~~~~~~~~~~~~~~~~

Explicit definition of all the mapped entities is the only necessary
configuration for the ORM and there are several configuration options that
you can control. The following configuration options exist for a mapping:

type
....

One of ``annotation``, ``xml``, ``yml``, ``php`` or ``staticphp``. This
specifies which type of metadata type your mapping uses.

dir
...

Path to the mapping or entity files (depending on the driver). If this path
is relative it is assumed to be relative to the bundle root. This only works
if the name of your mapping is a bundle name. If you want to use this option
to specify absolute paths you should prefix the path with the kernel parameters
that exist in the DIC (for example ``%kernel.root_dir%``).

prefix
......

A common namespace prefix that all entities of this mapping share. This
prefix should never conflict with prefixes of other defined mappings otherwise
some of your entities cannot be found by Doctrine. This option defaults
to the bundle namespace + ``Entity``, for example for an application bundle
called AcmeHelloBundle prefix would be ``Acme\HelloBundle\Entity``.

alias
.....

Doctrine offers a way to alias entity namespaces to simpler, shorter names
to be used in DQL queries or for Repository access. When using a bundle
the alias defaults to the bundle name.

is_bundle
.........

This option is a derived value from ``dir`` and by default is set to ``true``
if dir is relative proved by a ``file_exists()`` check that returns ``false``.
It is ``false`` if the existence check returns ``true``. In this case an
absolute path was specified and the metadata files are most likely in a
directory outside of a bundle.

.. index::
    single: Configuration; Doctrine DBAL
    single: Doctrine; DBAL configuration

.. _`reference-dbal-configuration`:

Doctrine DBAL Configuration
---------------------------

DoctrineBundle supports all parameters that default Doctrine drivers
accept, converted to the XML or YAML naming standards that Symfony
enforces. See the Doctrine `DBAL documentation`_ for more information.
The following block shows all possible configuration keys:

.. configuration-block::

    .. code-block:: yaml

        doctrine:
            dbal:
                dbname:               database
                host:                 localhost
                port:                 1234
                user:                 user
                password:             secret
                driver:               pdo_mysql
                # the DBAL driverClass option
                driver_class:         MyNamespace\MyDriverImpl
                # the DBAL driverOptions option
                options:
                    foo: bar
                path:                 '%kernel.data_dir%/data.sqlite'
                memory:               true
                unix_socket:          /tmp/mysql.sock
                # the DBAL wrapperClass option
                wrapper_class:        MyDoctrineDbalConnectionWrapper
                charset:              UTF8
                logging:              '%kernel.debug%'
                platform_service:     MyOwnDatabasePlatformService
                server_version:       5.6
                mapping_types:
                    enum: string
                types:
                    custom: Acme\HelloBundle\MyCustomType
                # the DBAL keepSlave option
                keep_slave:           false

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:doctrine="http://symfony.com/schema/dic/doctrine"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/doctrine
                http://symfony.com/schema/dic/doctrine/doctrine-1.0.xsd"
        >

            <doctrine:config>
                <doctrine:dbal
                    name="default"
                    dbname="database"
                    host="localhost"
                    port="1234"
                    user="user"
                    password="secret"
                    driver="pdo_mysql"
                    driver-class="MyNamespace\MyDriverImpl"
                    path="%kernel.data_dir%/data.sqlite"
                    memory="true"
                    unix-socket="/tmp/mysql.sock"
                    wrapper-class="MyDoctrineDbalConnectionWrapper"
                    charset="UTF8"
                    logging="%kernel.debug%"
                    platform-service="MyOwnDatabasePlatformService"
                    server-version="5.6">

                    <doctrine:option key="foo">bar</doctrine:option>
                    <doctrine:mapping-type name="enum">string</doctrine:mapping-type>
                    <doctrine:type name="custom">Acme\HelloBundle\MyCustomType</doctrine:type>
                </doctrine:dbal>
            </doctrine:config>
        </container>

.. note::

    The ``server_version`` option was added in Doctrine DBAL 2.5, which
    is used by DoctrineBundle 1.3. The value of this option should match
    your database server version (use ``postgres -V`` or ``psql -V`` command
    to find your PostgreSQL version and ``mysql -V`` to get your MySQL
    version).

    If you don't define this option and you haven't created your database
    yet, you may get ``PDOException`` errors because Doctrine will try to
    guess the database server version automatically and none is available.

If you want to configure multiple connections in YAML, put them under the
``connections`` key and give them a unique name:

.. code-block:: yaml

    doctrine:
        dbal:
            default_connection:       default
            connections:
                default:
                    dbname:           Symfony
                    user:             root
                    password:         null
                    host:             localhost
                    server_version:   5.6
                customer:
                    dbname:           customer
                    user:             root
                    password:         null
                    host:             localhost
                    server_version:   5.7

The ``database_connection`` service always refers to the *default* connection,
which is the first one defined or the one configured via the
``default_connection`` parameter.

Each connection is also accessible via the ``doctrine.dbal.[name]_connection``
service where ``[name]`` is the name of the connection.

.. _DBAL documentation: http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html

Shortened Configuration Syntax
------------------------------

When you are only using one entity manager, all config options available
can be placed directly under ``doctrine.orm`` config level.

.. code-block:: yaml

    doctrine:
        orm:
            # ...
            query_cache_driver:
               # ...
            metadata_cache_driver:
                # ...
            result_cache_driver:
                # ...
            connection: ~
            class_metadata_factory_name:  Doctrine\ORM\Mapping\ClassMetadataFactory
            default_repository_class:  Doctrine\ORM\EntityRepository
            auto_mapping: false
            hydrators:
                # ...
            mappings:
                # ...
            dql:
                # ...
            filters:
                # ...

This shortened version is commonly used in other documentation sections.
Keep in mind that you can't use both syntaxes at the same time.

Custom Mapping Entities in a Bundle
-----------------------------------

Doctrine's ``auto_mapping`` feature loads annotation configuration from
the ``Entity/`` directory of each bundle *and* looks for other formats (e.g.
YAML, XML) in the ``Resources/config/doctrine`` directory.

If you store metadata somewhere else in your bundle, you can define your
own mappings, where you tell Doctrine exactly *where* to look, along with
some other configurations.

If you're using the ``auto_mapping`` configuration, you just need to overwrite
the configurations you want. In this case it's important that the key of
the mapping configurations corresponds to the name of the bundle.

For example, suppose you decide to store your ``XML`` configuration for
``AppBundle`` entities in the ``@AppBundle/SomeResources/config/doctrine``
directory instead:

.. configuration-block::

    .. code-block:: yaml

        doctrine:
            # ...
            orm:
                # ...
                auto_mapping: true
                mappings:
                    # ...
                    AppBundle:
                        type: xml
                        dir: SomeResources/config/doctrine

    .. code-block:: xml

        <?xml version="1.0" charset="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:doctrine="http://symfony.com/schema/dic/doctrine">

            <doctrine:config>
                <doctrine:orm auto-mapping="true">
                    <mapping name="AppBundle" dir="SomeResources/config/doctrine" type="xml" />
                </doctrine:orm>
            </doctrine:config>
        </container>

    .. code-block:: php

        $container->loadFromExtension('doctrine', array(
            'orm' => array(
                'auto_mapping' => true,
                'mappings' => array(
                    'AppBundle' => array('dir' => 'SomeResources/config/doctrine', 'type' => 'xml'),
                ),
            ),
        ));

Mapping Entities Outside of a Bundle
------------------------------------

You can also create new mappings, for example outside of the Symfony folder.

For example, the following looks for entity classes in the ``App\Entity``
namespace in the ``src/Entity`` directory and gives them an ``App`` alias
(so you can say things like ``App:Post``):

.. configuration-block::

    .. code-block:: yaml

        doctrine:
                # ...
                orm:
                    # ...
                    mappings:
                        # ...
                        SomeEntityNamespace:
                            type: annotation
                            dir: '%kernel.root_dir%/../src/Entity'
                            is_bundle: false
                            prefix: App\Entity
                            alias: App

    .. code-block:: xml

        <?xml version="1.0" charset="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:doctrine="http://symfony.com/schema/dic/doctrine">

            <doctrine:config>
                <doctrine:orm>
                    <mapping name="SomeEntityNamespace"
                        type="annotation"
                        dir="%kernel.root_dir%/../src/Entity"
                        is-bundle="false"
                        prefix="App\Entity"
                        alias="App"
                    />
                </doctrine:orm>
            </doctrine:config>
        </container>

    .. code-block:: php

        $container->loadFromExtension('doctrine', array(
            'orm' => array(
                'auto_mapping' => true,
                'mappings' => array(
                    'SomeEntityNamespace' => array(
                        'type'      => 'annotation',
                        'dir'       => '%kernel.root_dir%/../src/Entity',
                        'is_bundle' => false,
                        'prefix'    => 'App\Entity',
                        'alias'     => 'App',
                    ),
                ),
            ),
        ));

Detecting a Mapping Configuration Format
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If the ``type`` on the bundle configuration isn't set, the DoctrineBundle
will try to detect the correct mapping configuration format for the bundle.

DoctrineBundle will look for files matching ``*.orm.[FORMAT]`` (e.g.
``Post.orm.yml``) in the configured ``dir`` of your mapping (if you're mapping
a bundle, then ``dir`` is relative to the bundle's directory).

The bundle looks for (in this order) XML, YAML and PHP files.
Using the ``auto_mapping`` feature, every bundle can have only one
configuration format. The bundle will stop as soon as it locates one.

If it wasn't possible to determine a configuration format for a bundle,
the DoctrineBundle will check if there is an ``Entity`` folder in the bundle's
root directory. If the folder exist, Doctrine will fall back to using an
annotation driver.

Default Value of Dir
~~~~~~~~~~~~~~~~~~~~

If ``dir`` is not specified, then its default value depends on which configuration
driver is being used. For drivers that rely on the PHP files (annotation,
staticphp) it will be ``[Bundle]/Entity``. For drivers that are using
configuration files (XML, YAML, ...) it will be
``[Bundle]/Resources/config/doctrine``.

If the ``dir`` configuration is set and the ``is_bundle`` configuration
is ``true``, the DoctrineBundle will prefix the ``dir`` configuration with
the path of the bundle.

.. _`DQL User Defined Functions`: http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/cookbook/dql-user-defined-functions.html
