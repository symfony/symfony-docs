.. index::
   single: Session; Database Storage

How to use PdoSessionStorage to store Sessions in the Database
==============================================================

The default session storage of Symfony2 writes the session information to
file(s). Most medium to large websites use a database to store the session
values instead of files, because databases are easier to use and scale in a
multi-webserver environment.

Symfony2 has a built-in solution for database session storage called
:class:`Symfony\\Component\\HttpFoundation\\SessionStorage\\PdoSessionStorage`.
To use it, you just need to change some parameters in ``config.yml`` (or the
configuration format of your choice):

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            session:
                # ...
                storage_id:     session.storage.pdo

        parameters:
            pdo.db_options:
                db_table:    session
                db_id_col:   session_id
                db_data_col: session_value
                db_time_col: session_time

        services:
            session.storage.pdo:
                class:     Symfony\Component\HttpFoundation\SessionStorage\PdoSessionStorage
                arguments: [@pdo, %session.storage.options%, %pdo.db_options%]

            pdo:
                class: PDO
                arguments:
                    dsn:      "mysql:dbname=mydatabase"
                    user:     myuser
                    password: mypassword

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <framework:config>
            <framework:session storage-id="session.storage.pdo" default-locale="en" lifetime="3600" auto-start="true"/>
        </framework:config>

        <parameters>
            <parameter key="pdo.db_options" type="collection">
                <parameter key="db_table">session</parameter>
                <parameter key="db_id_col">session_id</parameter>
                <parameter key="db_data_col">session_value</parameter>
                <parameter key="db_time_col">session_time</parameter>
            </parameter>
            <parameter key="pdo.options" />
        </parameters>

        <services>
            <service id="pdo" class="PDO">
                <argument id="dsn">mysql:dbname=sf2demo</argument>
                <argument id="user">root</argument>
                <argument id="password">password</argument>
            </service>

            <service id="session.storage.pdo" class="Symfony\Component\HttpFoundation\SessionStorage\PdoSessionStorage">
                <argument type="service" id="pdo" />
                <argument>%pdo.db_options%</argument>
                <argument>%pdo.options%</argument>
            </service>
        </services>

* ``db_table``: The name of the session table in your database
* ``db_id_col``: The name of the id column in your session table (VARCHAR(255) or larger)
* ``db_data_col``: The name of the value column in your session table (TEXT or CLOB)
* ``db_time_col``: The name of the time column in your session table (INTEGER)

Sharing your Database Connection Information
--------------------------------------------

With the given configuration, the database connection settings are defined for
the session storage connection only. This is OK when you use a separate
database for the session data.

But if you'd like to store the session data in the same database as the rest
of your project's data, you can use the connection settings from the
parameter.ini by referencing the database-related parameters defined there:

.. configuration-block::

    .. code-block:: yaml

        pdo:
            class: PDO
            arguments:
                dsn:      "mysql:dbname=%database_name%"
                user:     %database_user%
                password: %database_password%

    .. code-block:: xml

        <service id="pdo" class="PDO">
            <argument id="dsn">mysql:dbname=%database_name%</argument>
            <argument id="user">%database_user%</argument>
            <argument id="password">%database_password%</argument>
        </service>

Example MySQL Statement
-----------------------

The SQL-Statement for creating the needed Database-Table could look like the
following (MySQL):

.. code-block:: sql

    CREATE TABLE `session` (
        `session_id` varchar(255) NOT NULL,
        `session_value` text NOT NULL,
        `session_time` int(11) NOT NULL,
        PRIMARY KEY (`session_id`),
        UNIQUE KEY `session_id_idx` (`session_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
