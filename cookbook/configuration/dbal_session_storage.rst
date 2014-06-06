.. index::
   single: Session; Database Storage

How to use PdoSessionHandler to store Sessions in the Database
==============================================================

The default session storage of Symfony2 writes the session information to
file(s). Most medium to large websites use a database to store the session
values instead of files, because databases are easier to use and scale in a
multi-webserver environment.

Symfony2 has a built-in solution for database session storage called
:class:`Symfony\\Bridge\\Doctrine\\HttpFoundation\\HttpFoundation\\DbalSessionHandler`.
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
--------------------------------------------

With the given configuration, the database connection settings are the ones you've
set for the default doctrine connection. This is OK if you're storing everything 
in the same database. If you want to store the sessions in another database you just have
to configure a new doctrine connection.


Table structure and example SQL Statements
----------------------
Because of the way this is implemented in the php class you can only configure the table name (The default is sessions)
Here are a couple of SQL statements to help you create a table that will work with this
MySQL
~~~~~

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
~~~~~~~~~~

For PostgreSQL, the statement should look like this:

.. code-block:: sql

    CREATE TABLE sessions (
        sess_id character varying(255) NOT NULL,
        sess_data text NOT NULL,
        sess_time integer NOT NULL,
        CONSTRAINT session_pkey PRIMARY KEY (sess_id)
    );

Microsoft SQL Server
~~~~~~~~~~~~~~~~~~~~

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
