Doctrine Configuration
======================

DBAL Configuration
------------------

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
        default_connection:    default
        connections:
            default:
                dbname:        Symfony2
                user:          root
                password:      null
                host:          localhost
            customer:
                dbname:        customer
                user:          root
                password:      null
                host:          localhost

If you have defined multiple connections you can use the ``getDatabaseConnection()`` as well
but you must pass it an argument with the name of the connection you want to get::

    class UserController extends Controller
    {
        public function indexAction()
        {
            $conn = $this->container->getDatabaseConnection('customer');
        }
    }

ORM Configuration
-----------------

.. code-block:: yaml

    doctrine.orm:
        default_entity_manager:   default
        cache_driver:             apc           # array, apc, memcache, xcache
        entity_managers:
            default:
                connection:       default
            customer:
                connection:       customer

Just like the DBAL, if you have configured multiple ``EntityManager`` instances and want to
get a specific one you can use the ``getEntityManager()`` method by just passing it an argument
that is the name of the ``EntityManager`` you want::

    class UserController extends Controller
    {
        public function indexAction()
        {
            $em = $this->container->getService('doctrine.orm.customer_entity_manager');
        }
    }

Now the scenario arrises where you want to change your mapping information and
update your development database schema without blowing away everything and
losing your existing data. So first lets just add a new property to our ``User``
entity::

    namespace Application\HelloBundle\Entities;

    /** @Entity */
    class User
    {
        /** @Column(type="string") */
        protected $new;

        // ...
    }

Once you've done that, to get your database schema updated with the new column
you just need to run the following command:

    $ php hello/console doctrine:schema:update

Now your database will be updated and the new column added to the database
table.

Console Commands
----------------

The Doctrine2 ORM integration offers several console commands under the ``doctrine``
namespace. To view a list of the commands you can run the console without any arguments
or options:

    $ php hello/console
    ...

    doctrine
      :ensure-production-settings  Verify that Doctrine is properly configured for a production environment.
      :schema-tool                 Processes the schema and either apply it directly on EntityManager or generate the SQL output.
    doctrine:cache
      :clear-metadata              Clear all metadata cache for a entity manager.
      :clear-query                 Clear all query cache for a entity manager.
      :clear-result                Clear result cache for a entity manager.
    doctrine:data
      :load                        Load data fixtures to your database.
    doctrine:database
      :create                      Create the configured databases.
      :drop                        Drop the configured databases.
    doctrine:generate
      :entities                    Generate entity classes and method stubs from your mapping information.
      :entity                      Generate a new Doctrine entity inside a bundle.
      :proxies                     Generates proxy classes for entity classes.
      :repositories                Generate repository classes from your mapping information.
    doctrine:mapping
      :convert                     Convert mapping information between supported formats.
      :convert-d1-schema           Convert a Doctrine1 schema to Doctrine2 mapping files.
      :import                      Import mapping information from an existing database.
    doctrine:query
      :dql                         Executes arbitrary DQL directly from the command line.
      :sql                         Executes arbitrary SQL directly from the command line.
    doctrine:schema
      :create                      Processes the schema and either create it directly on EntityManager Storage Connection or generate the SQL output.
      :drop                        Processes the schema and either drop the database schema of EntityManager Storage Connection or generate the SQL output.
      :update                      Processes the schema and either update the database schema of EntityManager Storage Connection or generate the SQL output.

    ...
