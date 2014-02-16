.. index::
   single: Doctrine; Multiple entity managers

How to work with Multiple Entity Managers and Connections
=========================================================

You can use multiple Doctrine entity managers or connections in a Symfony2
application. This is necessary if you are using different databases or even
vendors with entirely different sets of entities. In other words, one entity
manager that connects to one database will handle some entities while another
entity manager that connects to another database might handle the rest.

.. note::

    Using multiple entity managers is pretty easy, but more advanced and not
    usually required. Be sure you actually need multiple entity managers before
    adding in this layer of complexity.

The following configuration code shows how you can configure two entity managers:

.. configuration-block::

    .. code-block:: yaml

        doctrine:
            dbal:
                default_connection: default
                connections:
                    default:
                        driver:   "%database_driver%"
                        host:     "%database_host%"
                        port:     "%database_port%"
                        dbname:   "%database_name%"
                        user:     "%database_user%"
                        password: "%database_password%"
                        charset:  UTF8
                    customer:
                        driver:   "%database_driver2%"
                        host:     "%database_host2%"
                        port:     "%database_port2%"
                        dbname:   "%database_name2%"
                        user:     "%database_user2%"
                        password: "%database_password2%"
                        charset:  UTF8

            orm:
                default_entity_manager: default
                entity_managers:
                    default:
                        connection: default
                        mappings:
                            AcmeDemoBundle:  ~
                            AcmeStoreBundle: ~
                    customer:
                        connection: customer
                        mappings:
                            AcmeCustomerBundle: ~

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/doctrine"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                                http://symfony.com/schema/dic/doctrine http://symfony.com/schema/dic/doctrine/doctrine-1.0.xsd">

            <config>
                <dbal default-connection="default">
                    <connection name="default"
                        driver="%database_driver%"
                        host="%database_host%"
                        port="%database_port%"
                        dbname="%database_name%"
                        user="%database_user%"
                        password="%database_password%"
                        charset="UTF8"
                    />

                    <connection name="customer"
                        driver="%database_driver2%"
                        host="%database_host2%"
                        port="%database_port2%"
                        dbname="%database_name2%"
                        user="%database_user2%"
                        password="%database_password2%"
                        charset="UTF8"
                    />
                </dbal>

                <orm default-entity-manager="default">
                    <entity-manager name="default" connection="default">
                        <mapping name="AcmeDemoBundle" />
                        <mapping name="AcmeStoreBundle" />
                    </entity-manager>

                    <entity-manager name="customer" connection="customer">
                        <mapping name="AcmeCustomerBundle" />
                    </entity-manager>
                </orm>
            </config>
        </srv:container>

    .. code-block:: php

        $container->loadFromExtension('doctrine', array(
            'dbal' => array(
                'default_connection' => 'default',
                'connections' => array(
                    'default' => array(
                        'driver'   => '%database_driver%',
                        'host'     => '%database_host%',
                        'port'     => '%database_port%',
                        'dbname'   => '%database_name%',
                        'user'     => '%database_user%',
                        'password' => '%database_password%',
                        'charset'  => 'UTF8',
                    ),
                    'customer' => array(
                        'driver'   => '%database_driver2%',
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
                            'AcmeDemoBundle'  => null,
                            'AcmeStoreBundle' => null,
                        ),
                    ),
                    'customer' => array(
                        'connection' => 'customer',
                        'mappings'   => array(
                            'AcmeCustomerBundle' => null,
                        ),
                    ),
                ),
            ),
        ));

In this case, you've defined two entity managers and called them ``default``
and ``customer``. The ``default`` entity manager manages entities in the
``AcmeDemoBundle`` and ``AcmeStoreBundle``, while the ``customer`` entity
manager manages entities in the ``AcmeCustomerBundle``. You've also defined
two connections, one for each entity manager.

.. note::

    When working with multiple connections and entity managers, you should be
    explicit about which configuration you want. If you *do* omit the name of
    the connection or entity manager, the default (i.e. ``default``) is used.

When working with multiple connections to create your databases:

.. code-block:: bash

    # Play only with "default" connection
    $ php app/console doctrine:database:create

    # Play only with "customer" connection
    $ php app/console doctrine:database:create --connection=customer

When working with multiple entity managers to update your schema:

.. code-block:: bash

    # Play only with "default" mappings
    $ php app/console doctrine:schema:update --force

    # Play only with "customer" mappings
    $ php app/console doctrine:schema:update --force --em=customer

If you *do* omit the entity manager's name when asking for it,
the default entity manager (i.e. ``default``) is returned::

    class UserController extends Controller
    {
        public function indexAction()
        {
            // All three return the "default" entity manager
            $em = $this->get('doctrine')->getManager();
            $em = $this->get('doctrine')->getManager('default');
            $em = $this->get('doctrine.orm.default_entity_manager');

            // Both of these return the "customer" entity manager
            $customerEm = $this->get('doctrine')->getManager('customer');
            $customerEm = $this->get('doctrine.orm.customer_entity_manager');
        }
    }

You can now use Doctrine just as you did before - using the ``default`` entity
manager to persist and fetch entities that it manages and the ``customer``
entity manager to persist and fetch its entities.

The same applies to repository calls::

    class UserController extends Controller
    {
        public function indexAction()
        {
            // Retrieves a repository managed by the "default" em
            $products = $this->get('doctrine')
                ->getRepository('AcmeStoreBundle:Product')
                ->findAll()
            ;

            // Explicit way to deal with the "default" em
            $products = $this->get('doctrine')
                ->getRepository('AcmeStoreBundle:Product', 'default')
                ->findAll()
            ;

            // Retrieves a repository managed by the "customer" em
            $customers = $this->get('doctrine')
                ->getRepository('AcmeCustomerBundle:Customer', 'customer')
                ->findAll()
            ;
        }
    }

