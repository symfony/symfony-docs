.. index::
    single: Session; Database Storage

<<<<<<< HEAD:cookbook/configuration/pdo_session_storage.rst
How to Use PdoSessionHandler to Store Sessions in the Database
=======
How to use Store Session in the Database
>>>>>>> - Merged both database session storage configurations into one file:cookbook/configuration/database_session_storage.rst
==============================================================

The default Symfony session storage writes the session information to
file(s). Most medium to large websites use a database to store the session
values instead of files, because databases are easier to use and scale in a
multi-webserver environment.

<<<<<<< HEAD:cookbook/configuration/pdo_session_storage.rst
Symfony has a built-in solution for database session storage called
:class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\Handler\\PdoSessionHandler`.
To use it, you just need to change some parameters in ``config.yml`` (or the
configuration format of your choice):
=======
Symfony2 has two built-in solutions for database session storage one uses doctrine
:class:`Symfony\\Bridge\\Doctrine\\HttpFoundation\\HttpFoundation\\DbalSessionHandler` 
and the other uses PDO :class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\Handler\\PdoSessionHandler`.
>>>>>>> - Merged both database session storage configurations into one file:cookbook/configuration/database_session_storage.rst

Using Doctrine to Store the Session in the Database
---------------------------------------------------

To use it, you just need to inject this class as a service in ``config.yml``:

.. versionadded:: 2.1
    
.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            session:
                # ...
                handler_id:     session.handler.dbal_handler

        services:
           
            session.handler.dbal_handler:
                class:     Symfony\Bridge\Doctrine\HttpFoundation\DbalSessionHandler
                arguments: ["@doctrine.dbal.default_connection"]

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <framework:config>
            <framework:session handler-id="session.handler.dbal_handler" cookie-lifetime="3600" auto-start="true"/>
        </framework:config>

        <services>
            <service id="session.handler.dbal_handler" class="Symfony\Bridge\Doctrine\HttpFoundation\DbalSessionHandler">
                <argument type="service" id="doctrine.dbal.default_connection" />                
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
                'handler_id' => 'session.handler.dbal_handler',
            ),
        ));

        $storageDefinition = new Definition('Symfony\Bridge\Doctrine\HttpFoundation\DbalSessionHandler', array(
            new Reference('doctrine.dbal.default_connection'),
        ));
        $container->setDefinition('session.handler.dbal_handler', $storageDefinition);

You can pass a second parameter to the constructor to set the table name. 
* ``db_table``: The name of the session table in your database
* ``db_id_col``: The name of the id column in your session table (VARCHAR(255) or larger)
* ``db_data_col``: The name of the value column in your session table (TEXT or CLOB)
* ``db_time_col``: The name of the time column in your session table (INTEGER)

Configuring your Database Connection Information
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

With the given configuration, the database connection settings are the ones you've
set for the default doctrine connection. This is OK if you're storing everything 
in the same database. If you want to store the sessions in another database you just have
to configure a new doctrine connection.


Table structure and example SQL Statements
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Because of the way this is implemented in the php class you can only configure the table name (The default is sessions)
Here are a couple of SQL statements to help you create a table that will work with this
MySQL
.....

The SQL statement for creating the needed database table might look like the
following (MySQL):

.. code-block:: sql

    CREATE TABLE `sessions` (
        `sess_id` varchar(255) NOT NULL,
        `sess_data` text NOT NULL,
        `sess_time` int(11) NOT NULL,
        PRIMARY KEY (`sess_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

PostgreSQL
..........

For PostgreSQL, the statement should look like this:

.. code-block:: sql

    CREATE TABLE sessions (
        sess_id character varying(255) NOT NULL,
        sess_data text NOT NULL,
        sess_time integer NOT NULL,
        CONSTRAINT session_pkey PRIMARY KEY (sess_id)
    );

Microsoft SQL Server
....................

For MSSQL, the statement might look like the following:

.. code-block:: sql

    CREATE TABLE [dbo].[sessions](
	    [sess_id] [nvarchar](255) NOT NULL,
	    [sess_data] [ntext] NOT NULL,
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


Using PDO to Store the Session in the Database
----------------------------------------------
.. versionadded:: 2.1
    In Symfony 2.1 the class and namespace are slightly modified. You can now
    find the session storage classes in the ``Session\Storage`` namespace:
    ``Symfony\Component\HttpFoundation\Session\Storage``. Also
    note that in Symfony 2.1 you should configure ``handler_id`` not ``storage_id`` like in Symfony 2.0.
    Below, you'll notice that ``%session.storage.options%`` is not used anymore.

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            session:
                # ...
                handler_id: session.handler.pdo

        parameters:
            pdo.db_options:
                db_table:    session
                db_id_col:   session_id
                db_data_col: session_value
                db_time_col: session_time

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
                <parameter key="db_table">session</parameter>
                <parameter key="db_id_col">session_id</parameter>
                <parameter key="db_data_col">session_value</parameter>
                <parameter key="db_time_col">session_time</parameter>
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
            'db_table'      => 'session',
            'db_id_col'     => 'session_id',
            'db_data_col'   => 'session_value',
            'db_time_col'   => 'session_time',
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
* ``db_id_col``: The name of the id column in your session table (VARCHAR(255) or larger)
* ``db_data_col``: The name of the value column in your session table (TEXT or CLOB)
* ``db_time_col``: The name of the time column in your session table (INTEGER)

Sharing your Database Connection Information
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

With the given configuration, the database connection settings are defined for
the session storage connection only. This is OK when you use a separate
database for the session data.

But if you'd like to store the session data in the same database as the rest
of your project's data, you can use the connection settings from the
``parameters.yml`` file by referencing the database-related parameters defined there:

.. configuration-block::

    .. code-block:: yaml

        services:
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
~~~~~~~~~~~~~~~~~~~~~~

MySQL
.....

The SQL statement for creating the needed database table might look like the
following (MySQL):

.. code-block:: sql

    CREATE TABLE `session` (
        `session_id` varchar(255) NOT NULL,
        `session_value` text NOT NULL,
        `session_time` int(11) NOT NULL,
        PRIMARY KEY (`session_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

PostgreSQL
..........

For PostgreSQL, the statement should look like this:

.. code-block:: sql

    CREATE TABLE session (
        session_id character varying(255) NOT NULL,
        session_value text NOT NULL,
        session_time integer NOT NULL,
        CONSTRAINT session_pkey PRIMARY KEY (session_id)
    );

Microsoft SQL Server
....................

For MSSQL, the statement might look like the following:

.. code-block:: sql

    CREATE TABLE [dbo].[session](
        [session_id] [nvarchar](255) NOT NULL,
        [session_value] [ntext] NOT NULL,
        [session_time] [int] NOT NULL,
        PRIMARY KEY CLUSTERED(
            [session_id] ASC
        ) WITH (
            PAD_INDEX  = OFF,
            STATISTICS_NORECOMPUTE  = OFF,
            IGNORE_DUP_KEY = OFF,
            ALLOW_ROW_LOCKS  = ON,
            ALLOW_PAGE_LOCKS  = ON
        ) ON [PRIMARY]
    ) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]