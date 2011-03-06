.. index::
   single: Doctrine; ORM Configuration Reference
   single: Configuration Reference; Doctrine ORM

Configuration Reference
=======================

Short syntax
------------

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
                driver_class:         MyNamespace\MyDriverImpl
                options:
                    foo: bar
                path:                 %kernel.data_dir%/data.sqlite
                memory:               true
                unix_socket:          /tmp/mysql.sock
                wrapper_class:        MyDoctrineDbalConnectionWrapper
                charset:              UTF-8
                logging:              %kernel.debug%
                platform_service:     MyOwnDatabasePlatformService
                default_connection:   default
                types:
                    custom: Sensio\HelloBundle\MyCustomType
            orm:
                mappings:   # Required
                    HelloBundle: ~
                class_metadata_factory_name:    Doctrine\ORM\Mapping\ClassMetadataFactory
                auto_generate_proxy_classes:    true
                proxy_namespace:                Proxies
                proxy_dir:                      %kernel.cache_dir%/doctrine/orm/Proxies
                default_entity_manager:         default
                result_cache_driver:            array
                query_cache_driver:             array
                matadata_cache_driver:
                    type:           memcache
                    host:           localhost
                    port:           11211
                    instance_class: Memcache
                    class:          Doctrine\Common\Cache\MemcacheCache
                # The name of a DBAL connection (the one marked as default is used if not set)
                connection: default

    .. code-block:: xml

        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:doctrine="http://symfony.com/schema/dic/doctrine"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                                http://symfony.com/schema/dic/doctrine http://symfony.com/schema/dic/doctrine/doctrine-1.0.xsd">

            <doctrine:config>
                <doctrine:dbal
                    default-connection="default"
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
                    charset="UTF-8"
                    logging="%kernel.debug%"
                    platform-service="MyOwnDatabasePlatformService"
                >
                    <doctrine:type name="custom" class="Sensio\HelloBundle\MyCustomType" />
                </doctrine:dbal>

                <doctrine:orm
                    default-entity-manager="default"
                    auto-generate-proxy-classes="true"
                    proxy-namespace="Proxies"
                    proxy-dir="%kernel.cache_dir%/doctrine/orm/Proxies"
                    query-cache-driver="array"
                    result-cache-driver="array"
                    connection="default"
                    class-metadata-factory-name="Doctrine\ORM\Mapping\ClassMetadataFactory"
                >
                    <doctrine:metadata-cache-driver type="memcache" host="localhost" port="11211" instance-class="Memcache" class="Doctrine\Common\Cache\MemcacheCache" />
                    <doctrine:mapping name="HelloBundle" />
                    <doctrine:mapping
                        name="DoctrineExtensions"
                        type="xml"
                        dir="%kernel.dir%/../src/vendor/DoctrineExtensions/lib/DoctrineExtensions/Entity"
                        prefix="DoctrineExtensions\Entity"
                        alias="DExt"
                    />
                </doctrine:orm>
            </doctrine:config>
        </container>

Expanded syntax
---------------

.. configuration-block::

    .. code-block:: yaml

        doctrine:
            dbal:
                default_connection:   default
                connections:
                    default:
                        dbname:               database
                        host:                 localhost
                        port:                 1234
                        user:                 user
                        password:             secret
                        driver:               pdo_mysql
                        driver_class:         MyNamespace\MyDriverImpl
                        options:
                            foo: bar
                        path:                 %kernel.data_dir%/data.sqlite
                        memory:               true
                        unix_socket:          /tmp/mysql.sock
                        wrapper_class:        MyDoctrineDbalConnectionWrapper
                        charset:              UTF-8
                        logging:              %kernel.debug%
                        platform_service:     MyOwnDatabasePlatformService
                    conn1:
                        # ...
                types:
                    custom: Sensio\HelloBundle\MyCustomType
            orm:
                auto_generate_proxy_classes:    true
                proxy_namespace:                Proxies
                proxy_dir:                      %kernel.cache_dir%/doctrine/orm/Proxies
                default_entity_manager:         default # Required
                entity_managers:
                    # At least one has to be defined
                    default:
                        # The name of a DBAL connection (the one marked as default is used if not set)
                        connection:                     conn1
                        mappings: # Required
                            HelloBundle: ~
                        class_metadata_factory_name:    Doctrine\ORM\Mapping\ClassMetadataFactory
                        # All cache drivers have to be array, apc, xcache or memcache
                        metadata_cache_driver:          array
                        query_cache_driver:             array
                        result_cache_driver:
                            type:           memcache
                            host:           localhost
                            port:           11211
                            instance_class: Memcache
                            class:          Doctrine\Common\Cache\MemcacheCache
                    em2:
                        # ...

    .. code-block:: xml

        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:doctrine="http://symfony.com/schema/dic/doctrine"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                                http://symfony.com/schema/dic/doctrine http://symfony.com/schema/dic/doctrine/doctrine-1.0.xsd">

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
                        charset="UTF-8"
                        logging="%kernel.debug%"
                        platform-service="MyOwnDatabasePlatformService"
                    />
                    <doctrine:connection name="conn1" />
                    <doctrine:type name="custom" class="Sensio\HelloBundle\MyCustomType" />
                </doctrine:dbal>

                <doctrine:orm default-entity-manager="default" auto-generate-proxy-classes="true" proxy-namespace="Proxies" proxy-dir="%kernel.cache_dir%/doctrine/orm/Proxies">
                    <doctrine:entity-manager name="default" query-cache-driver="array" result-cache-driver="array" connection="conn1" class-metadata-factory-name="Doctrine\ORM\Mapping\ClassMetadataFactory">
                        <doctrine:metadata-cache-driver type="memcache" host="localhost" port="11211" instance-class="Memcache" class="Doctrine\Common\Cache\MemcacheCache" />
                        <doctrine:mapping name="HelloBundle" />
                    </doctrine:entity-manager>
                    <doctrine:entity-manager name="em2" connection="conn2" metadata-cache-driver="apc">
                        <doctrine:mapping
                            name="DoctrineExtensions"
                            type="xml"
                            dir="%kernel.dir%/../src/vendor/DoctrineExtensions/lib/DoctrineExtensions/Entity"
                            prefix="DoctrineExtensions\Entity"
                            alias="DExt"
                        />
                    </doctrine:entity-manager>
                </doctrine:orm>
            </doctrine:config>
        </container>

.. tip::

    You can use also mix the syntax by using the expanded one for the DBAL
    configuration and the short one for the ORM.

.. note::

    The short syntax is remapped internally to the expanded one using the name
    passed as ``default_entity_manager`` or ``default`` if not set. Same is true
    for the DBAL connections.
