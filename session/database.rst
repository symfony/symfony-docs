.. index::
    single: Session; Database Storage

Store Sessions in a Database
============================

Symfony stores sessions in files by default. If your application is served by
multiple servers, you'll need to use instead a database to make sessions work
across different servers.

Symfony can store sessions in all kinds of databases (relational, NoSQL and
key-value) but recommends key-value databases like Redis to get best performance.

Store Sessions in a key-value Database (Redis)
----------------------------------------------

This section assumes that you have a fully-working Redis server and have also
installed and configured the `phpredis extension`_.

First, define a Symfony service for the connection to the Redis server:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...
            Redis:
                # you can also use \RedisArray, \RedisCluster or \Predis\Client classes
                class: Redis
                calls:
                    - connect:
                        - '%env(REDIS_HOST)%'
                        - '%env(int:REDIS_PORT)%'

                    # uncomment the following if your Redis server requires a password
                    # - auth:
                    #     - '%env(REDIS_PASSWORD)%'

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- you can also use \RedisArray, \RedisCluster or \Predis\Client classes -->
                <service id="Redis" class="Redis">
                    <call method="connect">
                        <argument>%env(REDIS_HOST)%</argument>
                        <argument>%env(int:REDIS_PORT)%</argument>
                    </call>

                    <!-- uncomment the following if your Redis server requires a password:
                    <call method="auth">
                        <argument>%env(REDIS_PASSWORD)%</argument>
                    </call> -->
                </service>
            </services>
        </container>

    .. code-block:: php

        use Symfony\Component\DependencyInjection\Reference;

        // ...
        $container
            // you can also use \RedisArray, \RedisCluster or \Predis\Client classes
            ->register('Redis', \Redis::class)
            ->addMethodCall('connect', ['%env(REDIS_HOST)%', '%env(int:REDIS_PORT)%'])
            // uncomment the following if your Redis server requires a password:
            // ->addMethodCall('auth', ['%env(REDIS_PASSWORD)%'])
        ;

Now pass this ``\Redis`` connection as an argument of the service associated to the
:class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\Handler\\RedisSessionHandler`.
This argument can also be a ``\RedisArray``, ``\RedisCluster``, ``\Predis\Client``,
and ``RedisProxy``:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...
            Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler:
                arguments:
                    - '@Redis'
                    # you can optionally pass an array of options. The only options are 'prefix' and 'ttl',
                    # which define the prefix to use for the keys to avoid collision on the Redis server
                    # and the expiration time for any given entry (in seconds), defaults are 'sf_s' and null:
                    # - { 'prefix' => 'my_prefix', 'ttl' => 600 }

    .. code-block:: xml

        <!-- config/services.xml -->
        <services>
            <service id="Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler">
                <argument type="service" id="Redis"/>
                <!-- you can optionally pass an array of options. The only options are 'prefix' and 'ttl',
                     which define the prefix to use for the keys to avoid collision on the Redis server
                     and the expiration time for any given entry (in seconds), defaults are 'sf_s' and null:
                <argument type="collection">
                    <argument key="prefix">my_prefix</argument>
                </argument> -->
            </service>
        </services>

    .. code-block:: php

        // config/services.php
        use Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler;

        $container
            ->register(RedisSessionHandler::class)
            ->addArgument(
                new Reference('Redis'),
                // you can optionally pass an array of options. The only options are 'prefix' and 'ttl',
                // which define the prefix to use for the keys to avoid collision on the Redis server
                // and the expiration time for any given entry (in seconds), defaults are 'sf_s' and null:
                // ['prefix' => 'my_prefix', 'ttl' => 600],
            );

Next, use the :ref:`handler_id <config-framework-session-handler-id>`
configuration option to tell Symfony to use this service as the session handler:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            # ...
            session:
                handler_id: Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <framework:config>
            <!-- ... -->
            <framework:session handler-id="Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler"/>
        </framework:config>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler;
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            // ...
            $framework->session()
                ->handlerId(RedisSessionHandler::class)
            ;
        };

That's all! Symfony will now use your Redis server to read and write the session
data. The main drawback of this solution is that Redis does not perform session
locking, so you can face *race conditions* when accessing sessions. For example,
you may see an *"Invalid CSRF token"* error because two requests were made in
parallel and only the first one stored the CSRF token in the session.

.. seealso::

    If you use Memcached instead of Redis, follow a similar approach but replace
    ``RedisSessionHandler`` by :class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\Handler\\MemcachedSessionHandler`.

Store Sessions in a Relational Database (MariaDB, MySQL, PostgreSQL)
--------------------------------------------------------------------

Symfony includes a :class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\Handler\\PdoSessionHandler`
to store sessions in relational databases like MariaDB, MySQL and PostgreSQL. To use it,
first register a new handler service with your database credentials:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler:
                arguments:
                    - '%env(DATABASE_URL)%'

                    # you can also use PDO configuration, but requires passing two arguments
                    # - 'mysql:dbname=mydatabase; host=myhost; port=myport'
                    # - { db_username: myuser, db_password: mypassword }

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <services>
                <service id="Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler" public="false">
                    <argument>%env(DATABASE_URL)%</argument>

                    <!-- you can also use PDO configuration, but requires passing two arguments: -->
                    <!-- <argument>mysql:dbname=mydatabase, host=myhost</argument>
                        <argument type="collection">
                            <argument key="db_username">myuser</argument>
                            <argument key="db_password">mypassword</argument>
                        </argument> -->
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;

        return static function (ContainerConfigurator $container) {
            $services = $configurator->services();

            $services->set(PdoSessionHandler::class)
                ->args([
                    '%env(DATABASE_URL)%',
                    // you can also use PDO configuration, but requires passing two arguments:
                    // 'mysql:dbname=mydatabase; host=myhost; port=myport',
                    // ['db_username' => 'myuser', 'db_password' => 'mypassword'],
                ])
            ;
        };

.. tip::

    When using MySQL as the database, the DSN defined in ``DATABASE_URL`` can
    contain the ``charset`` and ``unix_socket`` options as query string parameters.

    .. versionadded:: 5.3

        The support for ``charset`` and ``unix_socket`` options was introduced
        in Symfony 5.3.

Next, use the :ref:`handler_id <config-framework-session-handler-id>`
configuration option to tell Symfony to use this service as the session handler:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            session:
                # ...
                handler_id: Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <framework:config>
            <!-- ... -->
            <framework:session
                handler-id="Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler"/>
        </framework:config>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            // ...
            $framework->session()
                ->handlerId(PdoSessionHandler::class)
            ;
        };

Configuring the Session Table and Column Names
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The table used to store sessions is called ``sessions`` by default and defines
certain column names. You can configure these values with the second argument
passed to the ``PdoSessionHandler`` service:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler:
                arguments:
                    - '%env(DATABASE_URL)%'
                    - { db_table: 'customer_session', db_id_col: 'guid' }

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler" public="false">
                    <argument>%env(DATABASE_URL)%</argument>
                    <argument type="collection">
                        <argument key="db_table">customer_session</argument>
                        <argument key="db_id_col">guid</argument>
                    </argument>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;

        return static function (ContainerConfigurator $container) {
            $services = $configurator->services();

            $services->set(PdoSessionHandler::class)
                ->args([
                    '%env(DATABASE_URL)%',
                    ['db_table' => 'customer_session', 'db_id_col' => 'guid'],
                ])
            ;
        };

These are parameters that you can configure:

``db_table`` (default ``sessions``):
    The name of the session table in your database;

``db_username``: (default: ``''``)
    The username used to connect when using the PDO configuration (when using
    the connection based on the ``DATABASE_URL`` env var, it overrides the
    username defined in the env var).

``db_password``: (default: ``''``)
    The password used to connect when using the PDO configuration (when using
    the connection based on the ``DATABASE_URL`` env var, it overrides the
    password defined in the env var).

``db_id_col`` (default ``sess_id``):
    The name of the column where to store the session ID (column type: ``VARCHAR(128)``);

``db_data_col`` (default ``sess_data``):
    The name of the column where to store the session data (column type: ``BLOB``);

``db_time_col`` (default ``sess_time``):
    The name of the column where to store the session creation timestamp (column type: ``INTEGER``);

``db_lifetime_col`` (default ``sess_lifetime``):
    The name of the column where to store the session lifetime (column type: ``INTEGER``);

``db_connection_options`` (default: ``[]``)
    An array of driver-specific connection options;

``lock_mode`` (default: ``LOCK_TRANSACTIONAL``)
    The strategy for locking the database to avoid *race conditions*. Possible
    values are ``LOCK_NONE`` (no locking), ``LOCK_ADVISORY`` (application-level
    locking) and ``LOCK_TRANSACTIONAL`` (row-level locking).

Preparing the Database to Store Sessions
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Before storing sessions in the database, you must create the table that stores
the information. The session handler provides a method called
:method:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\Handler\\PdoSessionHandler::createTable`
to set up this table for you according to the database engine used::

    try {
        $sessionHandlerService->createTable();
    } catch (\PDOException $exception) {
        // the table could not be created for some reason
    }

If you prefer to set up the table yourself, it's recommended to generate an
empty database migration with the following command:

.. code-block:: terminal

    $ php bin/console doctrine:migrations:generate

Then, find the appropriate SQL for your database below, add it to the migration
file and run the migration with the following command:

.. code-block:: terminal

    $ php bin/console doctrine:migrations:migrate

.. _mysql:

MariaDB/MySQL
.............

.. code-block:: sql

    CREATE TABLE `sessions` (
        `sess_id` VARBINARY(128) NOT NULL PRIMARY KEY,
        `sess_data` BLOB NOT NULL,
        `sess_lifetime` INTEGER UNSIGNED NOT NULL,
        `sess_time` INTEGER UNSIGNED NOT NULL
    ) COLLATE utf8mb4_bin, ENGINE = InnoDB;

.. note::

    A ``BLOB`` column type (which is the one used by default by ``createTable()``)
    stores up to 64 kb. If the user session data exceeds this, an exception may
    be thrown or their session will be silently reset. Consider using a ``MEDIUMBLOB``
    if you need more space.

PostgreSQL
..........

.. code-block:: sql

    CREATE TABLE sessions (
        sess_id VARCHAR(128) NOT NULL PRIMARY KEY,
        sess_data BYTEA NOT NULL,
        sess_lifetime INTEGER NOT NULL,
        sess_time INTEGER NOT NULL
    );

Microsoft SQL Server
....................

.. code-block:: sql

    CREATE TABLE sessions (
        sess_id VARCHAR(128) NOT NULL PRIMARY KEY,
        sess_data NVARCHAR(MAX) NOT NULL,
        sess_lifetime INTEGER NOT NULL,
        sess_time INTEGER NOT NULL
    );

Store Sessions in a NoSQL Database (MongoDB)
--------------------------------------------

Symfony includes a :class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\Handler\\MongoDbSessionHandler`
to store sessions in the MongoDB NoSQL database. First, make sure to have a
working MongoDB connection in your Symfony application as explained in the
`DoctrineMongoDBBundle configuration`_ article.

Then, register a new handler service for ``MongoDbSessionHandler`` and pass it
the MongoDB connection as argument:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            Symfony\Component\HttpFoundation\Session\Storage\Handler\MongoDbSessionHandler:
                arguments:
                    - '@doctrine_mongodb.odm.default_connection'

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <services>
                <service id="Symfony\Component\HttpFoundation\Session\Storage\Handler\MongoDbSessionHandler" public="false">
                    <argument type="service">doctrine_mongodb.odm.default_connection</argument>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;

        return static function (ContainerConfigurator $container) {
            $services = $configurator->services();

            $services->set(MongoDbSessionHandler::class)
                ->args([
                    service('doctrine_mongodb.odm.default_connection'),
                ])
            ;
        };

Next, use the :ref:`handler_id <config-framework-session-handler-id>`
configuration option to tell Symfony to use this service as the session handler:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            session:
                # ...
                handler_id: Symfony\Component\HttpFoundation\Session\Storage\Handler\MongoDbSessionHandler

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <framework:config>
            <!-- ... -->
            <framework:session
                handler-id="Symfony\Component\HttpFoundation\Session\Storage\Handler\MongoDbSessionHandler"/>
        </framework:config>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Component\HttpFoundation\Session\Storage\Handler\MongoDbSessionHandler;
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            // ...
            $framework->session()
                ->handlerId(MongoDbSessionHandler::class)
            ;
        };

.. note::

    MongoDB ODM 1.x only works with the legacy driver, which is no longer
    supported by the Symfony session class. Install the ``alcaeus/mongo-php-adapter``
    package to retrieve the underlying ``\MongoDB\Client`` object or upgrade to
    MongoDB ODM 2.0.

That's all! Symfony will now use your MongoDB server to read and write the
session data. You do not need to do anything to initialize your session
collection. However, you may want to add an index to improve garbage collection
performance. Run this from the `MongoDB shell`_:

.. code-block:: javascript

    use session_db
    db.session.ensureIndex( { "expires_at": 1 }, { expireAfterSeconds: 0 } )

Configuring the Session Field Names
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The collection used to store sessions defines certain field names. You can
configure these values with the second argument passed to the
``MongoDbSessionHandler`` service:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            Symfony\Component\HttpFoundation\Session\Storage\Handler\MongoDbSessionHandler:
                arguments:
                    - '@doctrine_mongodb.odm.default_connection'
                    - { id_field: '_guid', 'expiry_field': 'eol' }

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="Symfony\Component\HttpFoundation\Session\Storage\Handler\MongoDbSessionHandler" public="false">
                    <argument type="service">doctrine_mongodb.odm.default_connection</argument>
                    <argument type="collection">
                        <argument key="id_field">_guid</argument>
                        <argument key="expiry_field">eol</argument>
                    </argument>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;

        return static function (ContainerConfigurator $container) {
            $services = $configurator->services();

            $services->set(MongoDbSessionHandler::class)
                ->args([
                    service('doctrine_mongodb.odm.default_connection'),
                    ['id_field' => '_guid', 'expiry_field' => 'eol'],
                ])
            ;
        };

These are parameters that you can configure:

``id_field`` (default ``_id``):
    The name of the field where to store the session ID;

``data_field`` (default ``data``):
    The name of the field where to store the session data;

``time_field`` (default ``time``):
    The name of the field where to store the session creation timestamp;

``expiry_field`` (default ``expires_at``):
    The name of the field where to store the session lifetime.

.. _`phpredis extension`: https://github.com/phpredis/phpredis
.. _`DoctrineMongoDBBundle configuration`: https://symfony.com/doc/master/bundles/DoctrineMongoDBBundle/config.html
.. _`MongoDB shell`: https://docs.mongodb.com/manual/mongo/
