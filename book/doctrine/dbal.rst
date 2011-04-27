.. index::
   pair: Doctrine; DBAL

DBAL
====

The `Doctrine`_ Database Abstraction Layer (DBAL) is an abstraction layer that
sits on top of `PDO`_ and offers an intuitive and flexible API for
communicating with the most popular relational databases.

.. tip::

    You can read more about the Doctrine DBAL on the official `documentation`_
    website.

To get started, configure the database connection parameters:

.. code-block:: yaml

    # app/config/config.yml
    doctrine:
        dbal:
            driver:   pdo_mysql
            dbname:   Symfony2
            user:     root
            password: null

.. note::

    DoctrineBundle supports all parameters that default Doctrine drivers
    accept, converted to the XML or YAML naming standards that Symfony
    enforces. See the Doctrine DBAL `documentation`_ for more information.

You can then access the Doctrine DBAL connection by accessing the
``database_connection`` service:

.. code-block:: php

    class UserController extends Controller
    {
        public function indexAction()
        {
            $conn = $this->get('database_connection');
            $users = $conn->fetchAll('SELECT * FROM users');

            // ...
        }
    }

.. index::
   single: Configuration; Doctrine DBAL
   single: Doctrine; DBAL configuration

Configuration
-------------

Besides default Doctrine options, there are some Symfony-related ones that you
can configure. The following block shows all possible configuration keys:

.. configuration-block::

    .. code-block:: yaml

        doctrine:
            dbal:
                dbname:               database
                host:                 localhost
                port:                 1234
                user:                 user
                password:             secret
                driver:               pdo_mysql
                driver_class:         MyNamespace\MyDriverImpl
                options:
                    foo: bar
                path:                 %kernel.data_dir%/data.sqlite
                memory:               true
                unix_socket:          /tmp/mysql.sock
                wrapper_class:        MyDoctrineDbalConnectionWrapper
                charset:              UTF8
                logging:              %kernel.debug%
                platform_service:     MyOwnDatabasePlatformService

    .. code-block:: xml

        <!-- xmlns:doctrine="http://symfony.com/schema/dic/doctrine" -->
        <!-- xsi:schemaLocation="http://symfony.com/schema/dic/doctrine http://symfony.com/schema/dic/doctrine/doctrine-1.0.xsd"> -->

        <doctrine:config>
            <doctrine:dbal>
                <doctrine:connection
                    name="default"
                    dbname="database"
                    host="localhost"
                    port="1234"
                    user="user"
                    password="secret"
                    driver="pdo_mysql"
                    driver-class="MyNamespace\MyDriverImpl"
                    path="%kernel.data_dir%/data.sqlite"
                    memory="true"
                    unix-socket="/tmp/mysql.sock"
                    wrapper-class="MyDoctrineDbalConnectionWrapper"
                    charset="UTF8"
                    logging="%kernel.debug%"
                    platform-service="MyOwnDatabasePlatformService"
                />
            </doctrine:dbal>
        </doctrine:config>

If you want to configure multiple connections in YAML, put them under the
``connections`` key and give them a unique name:

.. code-block:: yaml

    doctrine:
        dbal:
            default_connection:       default
            connections:
                default:
                    dbname:           Symfony2
                    user:             root
                    password:         null
                    host:             localhost
                customer:
                    dbname:           customer
                    user:             root
                    password:         null
                    host:             localhost

The ``database_connection`` service always refers to the *default* connection,
which is the first one defined or the one configured via the
``default_connection`` parameter.

Each connection is also accessible via the ``doctrine.dbal.[name]_connection``
service where ``[name]`` if the name of the connection.

.. _PDO:           http://www.php.net/pdo
.. _documentation: http://www.doctrine-project.org/docs/dbal/2.0/en
.. _Doctrine:      http://www.doctrine-project.org
