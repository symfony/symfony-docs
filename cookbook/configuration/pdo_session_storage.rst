.. index::
    single: Session; Database Storage

How to Use PdoSessionHandler to Store Sessions in the Database
==============================================================

.. caution::

    There was a backwards-compatibility break in Symfony 2.6: the database
    schema changed slightly. See :ref:`Symfony 2.6 Changes <pdo-session-handle-26-changes>`
    for details.

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

        services:
            session.handler.pdo:
                class:     Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler
                public:    false
                arguments:
                    - "mysql:dbname=mydatabase"
                    - { db_username: myuser, db_password: mypassword }

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <framework:config>
            <framework:session handler-id="session.handler.pdo" cookie-lifetime="3600" auto-start="true"/>
        </framework:config>

        <services>
            <service id="session.handler.pdo" class="Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler" public="false">
                <argument>mysql:dbname=mydatabase</agruement>
                <argument type="collection">
                    <argument key="db_username">myuser</argument>
                    <argument key="db_password">mypassword</argument>
                </argument>
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

        $storageDefinition = new Definition('Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler', array(
            'mysql:dbname=mydatabase',
            array('db_username' => 'myuser', 'db_password' => 'mypassword')
        ));
        $container->setDefinition('session.handler.pdo', $storageDefinition);

Configuring the Table and Column Names
--------------------------------------

This will expect a ``sessions`` table with a number of different columns.
The table name, and all of the column names, can be configured by passing
a second array argument to ``PdoSessionHandler``:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        services:
            # ...
            session.handler.pdo:
                class:     Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler
                public:    false
                arguments:
                    - "mysql:dbname=mydatabase"
                    - { db_table: sessions, db_username: myuser, db_password: mypassword }

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <services>
            <service id="session.handler.pdo" class="Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler" public="false">
                <argument>mysql:dbname=mydatabase</agruement>
                <argument type="collection">
                    <argument key="db_table">sessions</argument>
                    <argument key="db_username">myuser</argument>
                    <argument key="db_password">mypassword</argument>
                </argument>
            </service>
        </services>

    .. code-block:: php

        // app/config/config.php

        use Symfony\Component\DependencyInjection\Definition;
        // ...

        $storageDefinition = new Definition('Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler', array(
            'mysql:dbname=mydatabase',
            array('db_table' => 'sessions', 'db_username' => 'myuser', 'db_password' => 'mypassword')
        ));
        $container->setDefinition('session.handler.pdo', $storageDefinition);

.. versionadded:: 2.6
    The ``db_lifetime_col`` was introduced in Symfony 2.6. Prior to 2.6,
    this column did not exist.

The following things can be configured:

* ``db_table``: (default ``sessions``) The name of the session table in your
  database;
* ``db_id_col``: (default ``sess_id``) The name of the id column in your
  session table (VARCHAR(128));
* ``db_data_col``: (default ``sess_data``) The name of the value column in
  your session table (BLOB);
* ``db_time_col``: (default ``sess_time``) The name of the time column in
  your session table (INTEGER);
* ``db_lifetime_col``: (default ``sess_lifetime``) The name of the lifetime
  column in your session table (INTEGER).

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

        services:
            session.handler.pdo:
                class:     Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler
                public:    false
                arguments:
                    - "mysql:host=%database_host%;port=%database_port%;dbname=%database_name%"
                    - { db_username: %database_user%, db_password: %database_password% }

    .. code-block:: xml

        <service id="session.handler.pdo" class="Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler" public="false">
            <argument>mysql:host=%database_host%;port=%database_port%;dbname=%database_name%</agruement>
            <argument type="collection">
                <argument key="db_username">%database_user%</argument>
                <argument key="db_password">%database_password%</argument>
            </argument>
        </service>

    .. code-block:: php

        $storageDefinition = new Definition('Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler', array(
            'mysql:host=%database_host%;port=%database_port%;dbname=%database_name%',
            array('db_username' => '%database_user%', 'db_password' => '%database_password%')
        ));

Example SQL Statements
----------------------

.. _pdo-session-handle-26-changes:

.. sidebar:: Schema Changes needed when Upgrading to Symfony 2.6

    If you use the ``PdoSessionHandler`` prior to Symfony 2.6 and upgrade, you'll
    need to make a few changes to your session table:

    * A new session lifetime (``sess_lifetime`` by default) integer column
      needs to be added;
    * The data column (``sess_data`` by default) needs to be changed to a
      BLOB type.

    Check the SQL statements below for more details.

    To keep the old (2.5 and earlier) functionality, change your class name
    to use ``LegacyPdoSessionHandler`` instead of ``PdoSessionHandler`` (the
    legacy class was added in Symfony 2.6.2).

MySQL
~~~~~

The SQL statement for creating the needed database table might look like the
following (MySQL):

.. code-block:: sql

    CREATE TABLE `sessions` (
        `sess_id` VARBINARY(128) NOT NULL PRIMARY KEY,
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

For PostgreSQL, the statement should look like this:

.. code-block:: sql

    CREATE TABLE sessions (
        sess_id VARCHAR(128) NOT NULL PRIMARY KEY,
        sess_data BYTEA NOT NULL,
        sess_time INTEGER NOT NULL,
        sess_lifetime INTEGER NOT NULL
    );

Microsoft SQL Server
~~~~~~~~~~~~~~~~~~~~

For MSSQL, the statement might look like the following:

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
