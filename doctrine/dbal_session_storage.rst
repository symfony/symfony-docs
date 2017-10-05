.. index::
    single: Session; Database Storage

How to Use DbalSessionHandler to Store Sessions in the Database
===============================================================

The default Symfony session storage writes the session information to files.
Most medium to large websites use a database to store the session values
instead of files, because databases are easier to use and scale in a
multiple web server environment.

Symfony has a built-in solution for database session storage called
:class:`Symfony\\Bridge\\Doctrine\\HttpFoundation\\DbalSessionHandler`.
To use it, you just need to change some parameters in the main configuration file:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            session:
                # ...
                handler_id: session.handler.dbal

        services:
            session.handler.pdo:
                class:     Symfony\Bridge\Doctrine\HttpFoundation\DbalSessionHandler
                public:    false
                arguments:
                  - "@doctrine.dbal.default_connection"
                  - "sessions"

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <framework:config>
            <framework:session handler-id="session.handler.dbal" cookie-lifetime="3600" auto-start="true"/>
        </framework:config>

        <services>
            <service id="session.handler.pdo" class="Symfony\Bridge\Doctrine\HttpFoundation\DbalSessionHandler" public="false">
                <argument type="service" id="doctrine.dbal.default_connection"/>
                <argument>sessions</agruement>
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
                'handler_id' => 'session.handler.dbal',
            ),
        ));

        $storageDefinition = new Definition('Symfony\Bridge\Doctrine\HttpFoundation\DbalSessionHandler', array(
            new Reference('doctrine.dbal.default_connection'),
            'sessions'
        ));
        $container->setDefinition('session.handler.dbal', $storageDefinition);

Configuring the Table and Column Names
--------------------------------------

The table name, and all of the column names, can be configured by passing
a second string (for the table) and a third array argument (for the columns) to ``DbalSessionHandler``:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            session:
                # ...
                handler_id: session.handler.dbal

        services:
            session.handler.pdo:
                class:     Symfony\Bridge\Doctrine\HttpFoundation\DbalSessionHandler
                public:    false
                arguments:
                  - "@doctrine.dbal.default_connection"
                  - "sessions"
                  - db_id_col: sess_id
                    db_data_col: sess_data
                    db_time_col: sess_time

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <framework:config>
            <framework:session handler-id="session.handler.dbal" cookie-lifetime="3600" auto-start="true"/>
        </framework:config>

        <services>
            <service id="session.handler.pdo" class="Symfony\Bridge\Doctrine\HttpFoundation\DbalSessionHandler" public="false">
                <argument type="service" id="doctrine.dbal.default_connection"/>
                <argument>sessions</agruement>
                <argument type="collection">
                    <argument key="db_id_col">sess_id</argument>
                    <argument key="db_data_col">sess_data</argument>
                    <argument key="db_time_col">sess_time</argument>
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
                'handler_id' => 'session.handler.dbal',
            ),
        ));

        $storageDefinition = new Definition('Symfony\Bridge\Doctrine\HttpFoundation\DbalSessionHandler', array(
            new Reference('doctrine.dbal.default_connection'),
            'sessions',
            array('db_id_col' => 'sess_id', 'db_data_col' => 'sess_data', 'db_time_col' => 'sess_time')
        ));
        $container->setDefinition('session.handler.dbal', $storageDefinition);

These are parameters that you must configure:

``db_table``:
    The name of the session table in your database;

``db_id_col`` (default ``sess_id``):
    The name of the id column in your session table (VARCHAR(128));

``db_data_col`` (default ``sess_data``):
    The name of the value column in your session table (BLOB);

``db_time_col`` (default ``sess_time``):
    The name of the time column in your session table (INTEGER);

Preparing the Database to Store Sessions
----------------------------------------

Before storing sessions in the database, you must create the table that stores
the information. As this is a Doctrine bridge, you will need to create a
Doctrine database file in your preferred flavor.

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Entity/Session.php
        namespace AppBundle\Entity;

        use Doctrine\ORM\Mapping as ORM;

        /**
         * @ORM\Entity
         * @ORM\Table(name="sessions")
         */
        class Session
        {
            /**
             * @ORM\Column(type="string", length=128)
             * @ORM\Id
             */
            private $sess_id;

            /**
             * @ORM\Column(type="blob")
             */
            private $sess_data;

            /**
             * @ORM\Column(type="integer")
             */
            private $sess_time;
        }

    .. code-block:: yaml

        # src/AppBundle/Resources/config/doctrine/Session.orm.yml
        AppBundle\Entity\Session:
            type: entity
            table: sessions
            id:
                sess_id:
                    type: string
                    length: 128
            fields:
                sess_data:
                    type: blob
                sess_time:
                    type: integer

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/doctrine/Session.orm.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://doctrine-project.org/schemas/orm/doctrine-mapping
                http://doctrine-project.org/schemas/orm/doctrine-mapping.xsd">

            <entity name="AppBundle\Entity\Session" table="sessions">
                <id name="id" column="id" type="string" length="128"/>

                <field name="data" column="data" type="blob"/>
                <field name="time" column="time" type="integer"/>
            </entity>
        </doctrine-mapping>

.. caution::

    If the session data doesn't fit in the data column, it might get truncated
    by the database engine. To make matters worse, when the session data gets
    corrupted, PHP ignores the data without giving a warning.

    If the application stores large amounts of session data, this problem can
    be solved by increasing the column size (use ``BLOB`` or even ``MEDIUMBLOB``).
    When using MySQL as the database engine, you can also enable the `strict SQL mode`_
    to get noticed when such an error happens.

.. _`strict SQL mode`: https://dev.mysql.com/doc/refman/5.7/en/sql-mode.html
