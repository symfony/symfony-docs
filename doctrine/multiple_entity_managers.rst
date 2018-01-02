.. index::
   single: Doctrine; Multiple entity managers

How to Work with multiple Entity Managers and Connections
=========================================================

You can use multiple Doctrine entity managers or connections in a Symfony
application. This is necessary if you are using different databases or even
vendors with entirely different sets of entities. In other words, one entity
manager that connects to one database will handle some entities while another
entity manager that connects to another database might handle the rest.

.. note::

    Using multiple entity managers is pretty easy, but more advanced and not
    usually required. Be sure you actually need multiple entity managers before
    adding in this layer of complexity.

.. caution::

    Entities cannot define associations across different entity managers. If you
    need that, there are `several alternatives <https://stackoverflow.com/a/11494543/2804294>`_
    that require some custom setup.

The following configuration code shows how you can configure two entity managers:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/doctrine.yaml
        doctrine:
            dbal:
                default_connection: default
                connections:
                    default:
                        driver:   pdo_mysql
                        host:     '%database_host%'
                        port:     '%database_port%'
                        dbname:   '%database_name%'
                        user:     '%database_user%'
                        password: '%database_password%'
                        charset:  UTF8
                    customer:
                        driver:   pdo_mysql
                        host:     '%database_host2%'
                        port:     '%database_port2%'
                        dbname:   '%database_name2%'
                        user:     '%database_user2%'
                        password: '%database_password2%'
                        charset:  UTF8

            orm:
                default_entity_manager: default
                entity_managers:
                    default:
                        connection: default
                        mappings:
                            Main:
                                is_bundle: false
                                type: annotation
                                dir: '%kernel.project_dir%/src/Entity/Main'
                                prefix: 'App\Entity\Main'
                                alias: Main
                    customer:
                        connection: customer
                        mappings:
                            Customer:
                                is_bundle: false
                                type: annotation
                                dir: '%kernel.project_dir%/src/Entity/Customer'
                                prefix: 'App\Entity\Customer'
                                alias: Customer

    .. code-block:: xml

        <!-- config/packages/doctrine.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:doctrine="http://symfony.com/schema/dic/doctrine"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/doctrine
                http://symfony.com/schema/dic/doctrine/doctrine-1.0.xsd">

            <doctrine:config>
                <doctrine:dbal default-connection="default">
                    <doctrine:connection name="default"
                        driver="pdo_mysql"
                        host="%database_host%"
                        port="%database_port%"
                        dbname="%database_name%"
                        user="%database_user%"
                        password="%database_password%"
                        charset="UTF8"
                    />

                    <doctrine:connection name="customer"
                        driver="pdo_mysql"
                        host="%database_host2%"
                        port="%database_port2%"
                        dbname="%database_name2%"
                        user="%database_user2%"
                        password="%database_password2%"
                        charset="UTF8"
                    />
                </doctrine:dbal>

                <doctrine:orm default-entity-manager="default">
                    <doctrine:entity-manager name="default" connection="default">
                        <doctrine:mapping
                            name="Main"
                            is_bundle="false"
                            type="annotation"
                            dir="%kernel.project_dir%/src/Entity/Main"
                            prefix="App\Entity\Main"
                            alias="Main"
                        />
                    </doctrine:entity-manager>

                    <doctrine:entity-manager name="customer" connection="customer">
                        <doctrine:mapping
                            name="Customer"
                            is_bundle="false"
                            type="annotation"
                            dir="%kernel.project_dir%/src/Entity/Customer"
                            prefix="App\Entity\Customer"
                            alias="Customer"
                        />
                    </doctrine:entity-manager>
                </doctrine:orm>
            </doctrine:config>
        </container>

    .. code-block:: php

        // config/packages/doctrine.php
        $container->loadFromExtension('doctrine', array(
            'dbal' => array(
                'default_connection' => 'default',
                'connections' => array(
                    'default' => array(
                        'driver'   => 'pdo_mysql',
                        'host'     => '%database_host%',
                        'port'     => '%database_port%',
                        'dbname'   => '%database_name%',
                        'user'     => '%database_user%',
                        'password' => '%database_password%',
                        'charset'  => 'UTF8',
                    ),
                    'customer' => array(
                        'driver'   => 'pdo_mysql',
                        'host'     => '%database_host2%',
                        'port'     => '%database_port2%',
                        'dbname'   => '%database_name2%',
                        'user'     => '%database_user2%',
                        'password' => '%database_password2%',
                        'charset'  => 'UTF8',
                    ),
                ),
            ),

            'orm' => array(
                'default_entity_manager' => 'default',
                'entity_managers' => array(
                    'default' => array(
                        'connection' => 'default',
                        'mappings'   => array(
                            'Main'  => array(
                                is_bundle => false,
                                type => 'annotation',
                                dir => '%kernel.project_dir%/src/Entity/Main',
                                prefix => 'App\Entity\Main',
                                alias => 'Main',
                            )
                        ),
                    ),
                    'customer' => array(
                        'connection' => 'customer',
                        'mappings'   => array(
                            'Customer'  => array(
                                is_bundle => false,
                                type => 'annotation',
                                dir => '%kernel.project_dir%/src/Entity/Customer',
                                prefix => 'App\Entity\Customer',
                                alias => 'Customer',
                            )
                        ),
                    ),
                ),
            ),
        ));

In this case, you've defined two entity managers and called them ``default``
and ``customer``. The ``default`` entity manager manages entities in the
``src/Entity/Main`` directory, while the ``customer`` entity manager manages
entities in ``src/Entity/Customer``. You've also defined two connections, one
for each entity manager.

.. note::

    When working with multiple connections and entity managers, you should be
    explicit about which configuration you want. If you *do* omit the name of
    the connection or entity manager, the default (i.e. ``default``) is used.

When working with multiple connections to create your databases:

.. code-block:: terminal

    # Play only with "default" connection
    $ php bin/console doctrine:database:create

    # Play only with "customer" connection
    $ php bin/console doctrine:database:create --connection=customer

When working with multiple entity managers to generate migrations:

.. code-block:: terminal

    # Play only with "default" mappings
    $ php bin/console doctrine:migrations:diff
    $ php bin/console doctrine:migrations:migrate

    # Play only with "customer" mappings
    $ php bin/console doctrine:migrations:diff --em=customer
    $ php bin/console doctrine:migrations:migrate --em=customer

If you *do* omit the entity manager's name when asking for it,
the default entity manager (i.e. ``default``) is returned::

    // ...

    class UserController extends Controller
    {
        public function indexAction()
        {
            // All 3 return the "default" entity manager
            $em = $this->getDoctrine()->getManager();
            $em = $this->getDoctrine()->getManager('default');
            $em = $this->get('doctrine.orm.default_entity_manager');

            // Both of these return the "customer" entity manager
            $customerEm = $this->getDoctrine()->getManager('customer');
            $customerEm = $this->get('doctrine.orm.customer_entity_manager');
        }
    }

You can now use Doctrine just as you did before - using the ``default`` entity
manager to persist and fetch entities that it manages and the ``customer``
entity manager to persist and fetch its entities.

The same applies to repository calls::

    use AcmeStoreBundle\Entity\Customer;
    use AcmeStoreBundle\Entity\Product;
    // ...

    class UserController extends Controller
    {
        public function indexAction()
        {
            // Retrieves a repository managed by the "default" em
            $products = $this->getDoctrine()
                ->getRepository(Product::class)
                ->findAll()
            ;

            // Explicit way to deal with the "default" em
            $products = $this->getDoctrine()
                ->getRepository(Product::class, 'default')
                ->findAll()
            ;

            // Retrieves a repository managed by the "customer" em
            $customers = $this->getDoctrine()
                ->getRepository(Customer::class, 'customer')
                ->findAll()
            ;
        }
    }
