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
            storage_id: pdo
            db_table: session
            db_id_col: session_id
            db_data_col: session_value
            db_time_col: session_time

 * ``db_table``: The name of the session table in your database
 * ``db_id_col``: The name of the id column in your session table (VARCHAR(255) or larger)
 * ``db_data_col``: The name of the value column in your session table (TEXT or CLOB)
 * ``db_time_col``: The name of the time column in your session table (INTEGER)
 

Now you have to define a PDO connection for the database in the ``config.yml``. For that 
create a new service called ``pdo_connection``:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        services:
            pdo_connection:
                class:    PDO
                arguments:
                    dsn:      "mysql:dbname=mydatabase"
                    user:     myuser
                    password: mypassword

