.. index::
    single: Session; Database Storage

How to Use PdoSessionHandler to Store Sessions in the Database
==============================================================

The default Symfony session storage writes the session information to files.
Most medium to large websites use a database to store the session values
instead of files, because databases are easier to use and scale in a
multiple web server environment.

Symfony has a built-in solution for database session storage called
:class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\Handler\\PdoSessionHandler`.
To use it, first register a new handler service:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler:
                arguments:
                    - 'mysql:dbname=mydatabase; host=myhost; port=myport'
                    - { db_username: myuser, db_password: mypassword }

                    # If you're using Doctrine & want to re-use that connection, then:
                    # comment-out the above 2 lines and uncomment the line below
                    # - !service { class: PDO, factory: 'database_connection:getWrappedConnection' }
                    # If you get transaction issues (e.g. after login) uncomment the line below
                    # - { lock_mode: 1 }

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
                    <argument>mysql:dbname=mydatabase, host=myhost</argument>
                    <argument type="collection">
                        <argument key="db_username">myuser</argument>
                        <argument key="db_password">mypassword</argument>
                    </argument>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;

        $storageDefinition = $container->autowire(PdoSessionHandler::class)
            ->setArguments([
                'mysql:dbname=mydatabase; host=myhost; port=myport',
                ['db_username' => 'myuser', 'db_password' => 'mypassword'],
            ])
        ;

.. tip::

    Configure the database credentials
    :doc:`using environment variables in the config file </configuration/environment_variables>`
    to make your application more secure.

Next, tell Symfony to use your service as the session handler:

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
            <framework:session handler-id="Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler" cookie-lifetime="3600" auto-start="true"/>
        </framework:config>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;

        // ...
        $container->loadFromExtension('framework', [
            // ...
            'session' => [
                // ...
                'handler_id' => PdoSessionHandler::class,
            ],
        ]);

Configuring the Table and Column Names
--------------------------------------

This will expect a ``sessions`` table with a number of different columns.
The table name, and all of the column names, can be configured by passing
a second array argument to ``PdoSessionHandler``:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler:
                arguments:
                    - 'mysql:dbname=mydatabase; host=myhost; port=myport'
                    - { db_table: 'sessions', db_username: 'myuser', db_password: 'mypassword' }

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler" public="false">
                    <argument>mysql:dbname=mydatabase, host=myhost</argument>
                    <argument type="collection">
                        <argument key="db_table">sessions</argument>
                        <argument key="db_username">myuser</argument>
                        <argument key="db_password">mypassword</argument>
                    </argument>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;
        // ...

        $container->autowire(PdoSessionHandler::class)
            ->setArguments([
                'mysql:dbname=mydatabase; host=myhost; port=myport',
                ['db_table' => 'sessions', 'db_username' => 'myuser', 'db_password' => 'mypassword']
            ])
        ;

These are parameters that you can configure:

``db_table`` (default ``sessions``):
    The name of the session table in your database;

``db_id_col`` (default ``sess_id``):
    The name of the id column in your session table (VARCHAR(128));

``db_data_col`` (default ``sess_data``):
    The name of the value column in your session table (BLOB);

``db_time_col`` (default ``sess_time``):
    The name of the time column in your session table (INTEGER);

``db_lifetime_col`` (default ``sess_lifetime``):
    The name of the lifetime column in your session table (INTEGER).

.. _example-sql-statements:

Preparing the Database to Store Sessions
----------------------------------------

Before storing sessions in the database, you must create the table that stores
the information. The session handler provides a method called
:method:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\Handler::createTable`
to set up this table for you according to the database engine used::

    try {
        $sessionHandlerService->createTable();
    } catch (\PDOException $exception) {
        // the table could not be created for some reason
    }

If you prefer to set up the table yourself, these are some examples of the SQL
statements you may use according to your specific database engine.

A great way to run this on production is to generate an empty migration, and then
add this SQL inside:

.. code-block:: terminal

    $ php bin/console doctrine:migrations:generate

Find the correct SQL below and put it inside that file. Then execute it with:

.. code-block:: terminal

    $ php bin/console doctrine:migrations:migrate

MySQL
~~~~~

.. code-block:: sql

    CREATE TABLE `sessions` (
        `sess_id` VARCHAR(128) NOT NULL PRIMARY KEY,
        `sess_data` BLOB NOT NULL,
        `sess_time` INTEGER UNSIGNED NOT NULL,
        `sess_lifetime` MEDIUMINT NOT NULL
    ) COLLATE utf8_bin, ENGINE = InnoDB;

.. note::

    A ``BLOB`` column type can only store up to 64 kb. If the data stored in
    a user's session exceeds this, an exception may be thrown or their session
    will be silently reset. Consider using a ``MEDIUMBLOB`` if you need more
    space.

PostgreSQL
~~~~~~~~~~

.. code-block:: sql

    CREATE TABLE sessions (
        sess_id VARCHAR(128) NOT NULL PRIMARY KEY,
        sess_data BYTEA NOT NULL,
        sess_time INTEGER NOT NULL,
        sess_lifetime INTEGER NOT NULL
    );

Microsoft SQL Server
~~~~~~~~~~~~~~~~~~~~

.. code-block:: sql

    CREATE TABLE [dbo].[sessions](
        [sess_id] [nvarchar](255) NOT NULL,
        [sess_data] [ntext] NOT NULL,
        [sess_time] [int] NOT NULL,
        [sess_lifetime] [int] NOT NULL,
        PRIMARY KEY CLUSTERED(
            [sess_id] ASC
        ) WITH (
            PAD_INDEX  = OFF,
            STATISTICS_NORECOMPUTE  = OFF,
            IGNORE_DUP_KEY = OFF,
            ALLOW_ROW_LOCKS  = ON,
            ALLOW_PAGE_LOCKS  = ON
        ) ON [PRIMARY]
    ) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

.. caution::

    If the session data doesn't fit in the data column, it might get truncated
    by the database engine. To make matters worse, when the session data gets
    corrupted, PHP ignores the data without giving a warning.

    If the application stores large amounts of session data, this problem can
    be solved by increasing the column size (use ``BLOB`` or even ``MEDIUMBLOB``).
    When using MySQL as the database engine, you can also enable the `strict SQL mode`_
    to be notified when such an error happens.

.. _`strict SQL mode`: https://dev.mysql.com/doc/refman/5.7/en/sql-mode.html
