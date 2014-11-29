.. index::
    single: Session; Database Storage

How to Use PdoSessionHandler to Store Sessions in the Database
==============================================================

The default Symfony session storage writes the session information to
file(s). Most medium to large websites use a database to store the session
values instead of files, because databases are easier to use and scale in a
multi-webserver environment.

Symfony has a built-in solution for database session storage called
:class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\Handler\\PdoSessionHandler`.
To use it, you just need to change some parameters in ``config.yml`` (or the
configuration format of your choice):

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            session:
                # ...
                handler_id: session.handler.pdo

        parameters:
            pdo.db_options:
                db_table:        sessions
                db_id_col:       sess_id
                db_data_col:     sess_data
                db_lifetime_col: sess_lifetime
                db_time_col:     sess_time

        services:
            pdo:
                class: PDO
                arguments:
                    dsn:      "mysql:dbname=mydatabase"
                    user:     myuser
                    password: mypassword
                calls:
                    - [setAttribute, [3, 2]] # \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION

            session.handler.pdo:
                class:     Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler
                arguments: ["@pdo", "%pdo.db_options%"]

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <framework:config>
            <framework:session handler-id="session.handler.pdo" cookie-lifetime="3600" auto-start="true"/>
        </framework:config>

        <parameters>
            <parameter key="pdo.db_options" type="collection">
                <parameter key="db_table">sessions</parameter>
                <parameter key="db_id_col">sess_id</parameter>
                <parameter key="db_data_col">sess_data</parameter>
                <parameter key="db_lifetime_col">sess_lifetime</parameter>
                <parameter key="db_time_col">sess_time</parameter>
            </parameter>
        </parameters>

        <services>
            <service id="pdo" class="PDO">
                <argument>mysql:dbname=mydatabase</argument>
                <argument>myuser</argument>
                <argument>mypassword</argument>
                <call method="setAttribute">
                    <argument type="constant">PDO::ATTR_ERRMODE</argument>
                    <argument type="constant">PDO::ERRMODE_EXCEPTION</argument>
                </call>
            </service>

            <service id="session.handler.pdo" class="Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler">
                <argument type="service" id="pdo" />
                <argument>%pdo.db_options%</argument>
            </service>
        </services>

    .. code-block:: php

        // app/config/config.php
        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\DependencyInjection\Reference;

        $container->loadFromExtension('framework', array(
            ...,
            'session' => array(
                // ...,
                'handler_id' => 'session.handler.pdo',
            ),
        ));

        $container->setParameter('pdo.db_options', array(
            'db_table'          => 'sessions',
            'db_id_col'         => 'sess_id',
            'db_data_col'       => 'sess_data',
            'db_lifetime_col'   => 'sess_lifetime',
            'db_time_col'       => 'sess_time',
        ));

        $pdoDefinition = new Definition('PDO', array(
            'mysql:dbname=mydatabase',
            'myuser',
            'mypassword',
        ));
        $pdoDefinition->addMethodCall('setAttribute', array(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION));
        $container->setDefinition('pdo', $pdoDefinition);

        $storageDefinition = new Definition('Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler', array(
            new Reference('pdo'),
            '%pdo.db_options%',
        ));
        $container->setDefinition('session.handler.pdo', $storageDefinition);

* ``db_table``: The name of the session table in your database
* ``db_id_col``: The name of the id column in your session table (VARCHAR(128))
* ``db_data_col``: The name of the value column in your session table (BLOB)
* ``db_lifetime_col``: The name of the lifetime column in your session table (INTEGER)
* ``db_time_col``: The name of the time column in your session table (INTEGER)

Sharing your Database Connection Information
--------------------------------------------

With the given configuration, the database connection settings are defined for
the session storage connection only. This is OK when you use a separate
database for the session data.

But if you'd like to store the session data in the same database as the rest
of your project's data, you can use the connection settings from the
``parameters.yml`` file by referencing the database-related parameters defined there:

.. configuration-block::

    .. code-block:: yaml

        pdo:
            class: PDO
            arguments:
                - "mysql:host=%database_host%;port=%database_port%;dbname=%database_name%"
                - "%database_user%"
                - "%database_password%"

    .. code-block:: xml

        <service id="pdo" class="PDO">
            <argument>mysql:host=%database_host%;port=%database_port%;dbname=%database_name%</argument>
            <argument>%database_user%</argument>
            <argument>%database_password%</argument>
        </service>

    .. code-block:: php

        $pdoDefinition = new Definition('PDO', array(
            'mysql:host=%database_host%;port=%database_port%;dbname=%database_name%',
            '%database_user%',
            '%database_password%',
        ));

Example SQL Statements
----------------------

In Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler, the createTable() method can be called once for setup.

MySQL
~~~~~

The SQL statement for creating the needed database table might look like the
following (MySQL):

.. code-block:: sql

    CREATE TABLE `sessions` (
        `sess_id` VARBINARY(128) NOT NULL PRIMARY KEY,
        `sess_data` BLOB NOT NULL,
        `sess_lifetime` MEDIUMINT NOT NULL,
        `sess_time` INTEGER UNSIGNED NOT NULL
    ) COLLATE utf8_bin, ENGINE = InnoDB;

PostgreSQL
~~~~~~~~~~

For PostgreSQL, the statement should look like this:

.. code-block:: sql

    CREATE TABLE sessions (
        sess_id VARCHAR(128) NOT NULL PRIMARY KEY,
        sess_data BYTEA NOT NULL,
        sess_lifetime INTEGER NOT NULL,
        sess_time INTEGER NOT NULL
    );

Microsoft SQL Server
~~~~~~~~~~~~~~~~~~~~

For MSSQL, the statement might look like the following:

.. code-block:: sql

    CREATE TABLE [dbo].[sessions](
        [sess_id] [nvarchar](255) NOT NULL,
        [sess_data] [ntext] NOT NULL,
        [sess_lifetime] [int] NOT NULL,
        [sess_time] [int] NOT NULL,
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
