.. index::
   single: Configuration; Doctrine DBAL
   single: Doctrine; DBAL configuration

Configuration
=============

One example configuration with a MySQL database could look like this following
example:

.. code-block:: yaml

    # app/config/config.yml
    doctrine:
        dbal:
            driver:   pdo_mysql
            dbname:   Symfony2
            user:     root
            password: null

The DoctrineBundle supports all parameters that all the default doctrine drivers
accept, converted to the XML or YAML naming standards that Symfony enforces.
See the Doctrine DBAL `documentation`_ for more information. Additionally
there are some Symfony related options that you can configure. The following
block shows all possible configuration keys without explaining their meaning
further:

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
                charset:              UTF-8
                logging:              %kernel.debug%
                platform_service:     MyOwnDatabasePlatformService

    .. code-block:: xml

        <!-- xmlns:doctrine="http://symfony.com/schema/dic/doctrine" -->
        <!-- xsi:schemaLocation="http://symfony.com/schema/dic/doctrine http://symfony.com/schema/dic/doctrine/doctrine-1.0.xsd"> -->

        <doctrine:config>
            <doctrine:dbal
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
                charset="UTF-8"
                logging="%kernel.debug%"
                platform-service="MyOwnDatabasePlatformService"
            />
        </doctrine:config>

There are also a bunch of dependency injection container parameters
that allow you to specify which classes are used (with their default values):

.. code-block:: yaml

    parameters:
        doctrine.dbal.logger_class: Symfony\Bundle\DoctrineBundle\Logger\DbalLogger
        doctrine.dbal.configuration_class: Doctrine\DBAL\Configuration
        doctrine.data_collector.class: Symfony\Bundle\DoctrineBundle\DataCollector\DoctrineDataCollector
        doctrine.dbal.event_manager_class: Doctrine\Common\EventManager
        doctrine.dbal.events.mysql_session_init.class: Doctrine\DBAL\Event\Listeners\MysqlSessionInit
        doctrine.dbal.events.oracle_session_init.class: Doctrine\DBAL\Event\Listeners\OracleSessionInit
        doctrine.dbal.logging: false

If you want to configure multiple connections you can do so by simply listing
them under the key named ``connections``. All the parameters shown above
can also be specified in the connections subkeys.

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

If you have defined multiple connections you can use the
``$this->get('doctrine.dbal.[connectionname]_connection)``
as well but you must pass it an argument with the
connection name that you want get::

    class UserController extends Controller
    {
        public function indexAction()
        {
            $defaultConn1 = $this->get('doctrine.dbal.connection');
            $defaultConn2 = $this->get('doctrine.dbal.default_connection');
            // $defaultConn1 === $defaultConn2

            $customerConn = $this->get('doctrine.dbal.customer_connection');
        }
    }

.. _documentation: http://www.doctrine-project.org/projects/dbal/2.0/docs/en
