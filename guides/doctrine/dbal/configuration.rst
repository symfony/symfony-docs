.. index::
   single: Configuration; Doctrine DBAL
   single: Doctrine; DBAL configuration

Configuration
=============

.. code-block:: yaml

    # config/config.yml
    doctrine.dbal:
        driver:   PDOMySql
        dbname:   Symfony2
        user:     root
        password: null

You can also specify some additional configurations on a connection but they
are not required:

.. code-block:: yaml

    # ...

    doctrine.dbal:
        # ...

        host:                 localhost
        port:                 ~
        path:                 %kernel.data_dir%/symfony.sqlite
        event_manager_class:  Doctrine\Common\EventManager
        configuration_class:  Doctrine\DBAL\Configuration
        wrapper_class:        ~
        options:              []

If you want to configure multiple connections you can do so by simply listing them under
the key named ``connections``:

.. code-block:: yaml

    doctrine.dbal:
        default_connection:       default
        connections:
            default:
                dbname:               Symfony2
                user:                 root
                password:             null
                host:                 localhost
            customer:
                dbname:               customer
                user:                 root
                password:             null
                host:                 localhost

If you have defined multiple connections you can use the ``getDatabaseConnection()`` as well
but you must pass it an argument with the name of the connection you want to get::

    class UserController extends Controller
    {
        public function indexAction()
        {
            $conn = $this->container->getDatabaseConnection('customer');
        }
    }