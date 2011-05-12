.. index::
   single: PdoSessionStorage; Session

How to use PdoSessionStorage to store sessions in database
==========================================================

The default session storage of Symfony2 is writing the session values to file(s). 
Most medium to large websites are using a database for the session values instead 
of files, because databases are easier to use and scale in a multi-webserver 
environment.

Symfony2 has a built-in solution for database session storage called ``PdoSessionStorage``.
To use it you just need to change some parameters in the ``config.yml`` (or the configuration 
format of your choice).

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        session:
            default_locale: %locale%
            lifetime:       3600
            auto_start:     true
            storage_id:     session.storage.pdo
            
        parameters:
            pdo.db_options:
                db_table: session
                db_id_col: session_id
                db_data_col: session_value
                db_time_col: session_time
		
        services:
            session.storage.pdo:
                class:    Symfony\Component\HttpFoundation\SessionStorage\PdoSessionStorage
                arguments: [@pdo, %pdo.db_options% ]
					
            pdo:
                class: PDO
                arguments:
                    dsn:      "mysql:dbname=mydatabase"
                    user:     myuser
                    password: mypassword 				


 * ``db_table``: The name of the session table in your database
 * ``db_id_col``: The name of the id column in your session table (VARCHAR(255) or larger)
 * ``db_data_col``: The name of the value column in your session table (TEXT or CLOB)
 * ``db_time_col``: The name of the time column in your session table (INTEGER)
	
With the given configuration the database connection settings are defined for the
session storage connection only. This is OK when you use a separate database for 
the session data.
If you like to store the session data in the same database as the rest of your
projects data, you can use the connection settings from the parameter.ini via
the placeholder-variables.

.. configuration-block::

    .. code-block:: yaml
        pdo:
            class: PDO
            arguments:
                dsn:      "mysql:dbname=%database_name%"
                user:     %database_user%
                password: %database_password%
			
The SQL-Statement for creating the needed Database-Table could look like the following (MySQL):

.. code-block: sql
    CREATE TABLE `session` (
        `session_id` varchar(255) NOT NULL,
        `session_value` text NOT NULL,
        `session_time` int(11) NOT NULL,
        PRIMARY KEY (`session_id`),
        UNIQUE KEY `session_id_idx` (`session_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;