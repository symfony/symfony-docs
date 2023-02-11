.. index::
   single: Doctrine; Multiple entity managers

How to Work with Multiple Entity Managers and Connections
=========================================================

You can use multiple Doctrine entity managers or connections in a Symfony
application. This is necessary if you are using different databases or even
vendors with entirely different sets of entities. In other words, one entity
manager that connects to one database will handle some entities while another
entity manager that connects to another database might handle the rest.
It is also possible to use multiple entity managers to manage a common set of
entities, each with their own database connection strings or separate cache configuration.

.. note::

    Using multiple entity managers is not complicated to configure, but more
    advanced and not usually required. Be sure you actually need multiple
    entity managers before adding in this layer of complexity.

.. caution::

    Entities cannot define associations across different entity managers. If you
    need that, there are `several alternatives`_ that require some custom setup.

The following configuration code shows how you can configure two entity managers:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/doctrine.yaml
        doctrine:
            dbal:
                connections:
                    default:
                        url: '%env(resolve:DATABASE_URL)%'
                    customer:
                        url: '%env(resolve:CUSTOMER_DATABASE_URL)%'
                default_connection: default
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
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:doctrine="http://symfony.com/schema/dic/doctrine"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/doctrine
                https://symfony.com/schema/dic/doctrine/doctrine-1.0.xsd">

            <doctrine:config>
                <doctrine:dbal default-connection="default">
                    <doctrine:connection name="default"
                        url="%env(resolve:DATABASE_URL)%"
                    />

                    <doctrine:connection name="customer"
                        url="%env(resolve:CUSTOMER_DATABASE_URL)%"
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
        use Symfony\Config\DoctrineConfig;

        return static function (DoctrineConfig $doctrine) {
            // Connections:
            $doctrine->dbal()
                ->connection('default')
                ->url(env('DATABASE_URL')->resolve());
            $doctrine->dbal()
                ->connection('customer')
                ->url(env('CUSTOMER_DATABASE_URL')->resolve());
            $doctrine->dbal()->defaultConnection('default');
            
            // Entity Managers:
            $doctrine->orm()->defaultEntityManager('default');
            $defaultEntityManager = $doctrine->orm()->entityManager('default');
            $defaultEntityManager->connection('default');
            $defaultEntityManager->mapping('Main')
                ->isBundle(false)
                ->type('annotation')
                ->dir('%kernel.project_dir%/src/Entity/Main')
                ->prefix('App\Entity\Main')
                ->alias('Main');
            $customerEntityManager = $doctrine->orm()->entityManager('customer');
            $customerEntityManager->connection('customer');
            $customerEntityManager->mapping('Customer')
                ->isBundle(false)
                ->type('annotation')
                ->dir('%kernel.project_dir%/src/Entity/Customer')
                ->prefix('App\Entity\Customer')
                ->alias('Customer')
            ;
        };

In this case, you've defined two entity managers and called them ``default``
and ``customer``. The ``default`` entity manager manages entities in the
``src/Entity/Main`` directory, while the ``customer`` entity manager manages
entities in ``src/Entity/Customer``. You've also defined two connections, one
for each entity manager, but you are free to define the same connection for both.

.. caution::

    When working with multiple connections and entity managers, you should be
    explicit about which configuration you want. If you *do* omit the name of
    the connection or entity manager, the default (i.e. ``default``) is used.

    If you use a different name than ``default`` for the default entity manager,
    you will need to redefine the default entity manager in the ``prod`` environment
    configuration and in the Doctrine migrations configuration (if you use that):

    .. code-block:: yaml

        # config/packages/prod/doctrine.yaml
        doctrine:
            orm:
                default_entity_manager: 'your default entity manager name'

        # ...

    .. code-block:: yaml

        # config/packages/doctrine_migrations.yaml
        doctrine_migrations:
            # ...
            em: 'your default entity manager name'

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

    // src/Controller/UserController.php
    namespace App\Controller;

    // ...
    use Doctrine\ORM\EntityManagerInterface;
    use Doctrine\Persistence\ManagerRegistry;

    class UserController extends AbstractController
    {
        public function index(ManagerRegistry $doctrine): Response
        {
            // Both methods return the default entity manager
            $entityManager = $doctrine->getManager();
            $entityManager = $doctrine->getManager('default');

            // This method returns instead the "customer" entity manager
            $customerEntityManager = $doctrine->getManager('customer');

            // ...
        }
    }

Entity managers also benefit from :ref:`autowiring aliases <service-autowiring-alias>`
when the :ref:`framework bundle <framework-bundle-configuration>` is used. For
example, to inject the ``customer`` entity manager, type-hint your method with
``EntityManagerInterface $customerEntityManager``.

You can now use Doctrine like you did before - using the ``default`` entity
manager to persist and fetch entities that it manages and the ``customer``
entity manager to persist and fetch its entities.

The same applies to repository calls::

    // src/Controller/UserController.php
    namespace App\Controller;

    use AcmeStoreBundle\Entity\Customer;
    use AcmeStoreBundle\Entity\Product;
    use Doctrine\Persistence\ManagerRegistry;
    // ...

    class UserController extends AbstractController
    {
        public function index(ManagerRegistry $doctrine): Response
        {
            // Retrieves a repository managed by the "default" entity manager
            $products = $doctrine->getRepository(Product::class)->findAll();

            // Explicit way to deal with the "default" entity manager
            $products = $doctrine->getRepository(Product::class, 'default')->findAll();

            // Retrieves a repository managed by the "customer" entity manager
            $customers = $doctrine->getRepository(Customer::class, 'customer')->findAll();

            // ...
        }
    }

.. caution::

    One entity can be managed by more than one entity manager. This however
    results in unexpected behavior when extending from ``ServiceEntityRepository``
    in your custom repository. The ``ServiceEntityRepository`` always
    uses the configured entity manager for that entity.

    In order to fix this situation, extend ``EntityRepository`` instead and
    no longer rely on autowiring::

        // src/Repository/CustomerRepository.php
        namespace App\Repository;

        use Doctrine\ORM\EntityRepository;

        class CustomerRepository extends EntityRepository
        {
            // ...
        }

    You should now always fetch this repository using ``ManagerRegistry::getRepository()``.

.. _`several alternatives`: https://stackoverflow.com/a/11494543
