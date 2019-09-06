.. index::
   single: Doctrine; Multiple entity managers

How to Work with multiple Entity Managers and Connections
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
                        # configure these for your database server
                        url: '%env(DATABASE_URL)%'
                        driver: 'pdo_mysql'
                        server_version: '5.7'
                        charset: utf8mb4
                    human:
                        # configure these for your database server
                        url: '%env(DATABASE_HUMAN_URL)%'
                        driver: 'pdo_mysql'
                        server_version: '5.7'
                        charset: utf8mb4
            orm:
                default_entity_manager: default
                entity_managers:
                    default:
                        connection: default
                        mappings:
                            Animal:
                                is_bundle: false
                                type: annotation
                                dir: '%kernel.project_dir%/src/Entity/Animal'
                                prefix: 'App\Entity\Animal'
                                alias: Animal
                    human:
                        connection: human
                        mappings:
                            Human:
                                is_bundle: false
                                type: annotation
                                dir: '%kernel.project_dir%/src/Entity/Human'
                                prefix: 'App\Entity\Human'
                                alias: Human

    .. code-block:: xml

        <!-- config/packages/doctrine.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:doctrine="http://symfony.com/schema/dic/doctrine"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/doctrine
                https://symfony.com/schema/dic/doctrine/doctrine-1.0.xsd">

            <doctrine:config>
                <doctrine:dbal default-connection="default">
                    <!-- configure these for your database server -->
                    <doctrine:connection name="default"
                        url="%env(DATABASE_URL)%"
                        driver="pdo_mysql"
                        server_version="5.7"
                        charset="utf8mb4"
                    />

                    <!-- configure these for your database server -->
                    <doctrine:connection name="human"
                        url="%env(DATABASE_HUMAN_URL)%"
                        driver="pdo_mysql"
                        server_version="5.7"
                        charset="utf8mb4"
                    />
                </doctrine:dbal>

                <doctrine:orm default-entity-manager="default">
                    <doctrine:entity-manager name="default" connection="default">
                        <doctrine:mapping
                            name="Animal"
                            is_bundle="false"
                            type="annotation"
                            dir="%kernel.project_dir%/src/Entity/Animal"
                            prefix="App\Entity\Animal"
                            alias="Animal"
                        />
                    </doctrine:entity-manager>

                    <doctrine:entity-manager name="human" connection="human">
                        <doctrine:mapping
                            name="Human"
                            is_bundle="false"
                            type="annotation"
                            dir="%kernel.project_dir%/src/Entity/Human"
                            prefix="App\Entity\Human"
                            alias="Human"
                        />
                    </doctrine:entity-manager>
                </doctrine:orm>
            </doctrine:config>
        </container>

    .. code-block:: php

        // config/packages/doctrine.php
        $container->loadFromExtension('doctrine', [
            'dbal' => [
                'default_connection' => 'default',
                'connections' => [
                    // configure these for your database server
                    'default' => [
                        'url'            => '%env(DATABASE_URL)%',
                        'driver'         => 'pdo_mysql',
                        'server_version' => '5.7',
                        'charset'        => 'utf8mb4',
                    ],
                    // configure these for your database server
                    'human' => [
                        'url'            => '%env(DATABASE_HUMAN_URL)%',
                        'driver'         => 'pdo_mysql',
                        'server_version' => '5.7',
                        'charset'        => 'utf8mb4',
                    ],
                ],
            ],

            'orm' => [
                'default_entity_manager' => 'default',
                'entity_managers' => [
                    'default' => [
                        'connection' => 'default',
                        'mappings'   => [
                            'Animal'  => [
                                is_bundle => false,
                                type => 'annotation',
                                dir => '%kernel.project_dir%/src/Entity/Animal',
                                prefix => 'App\Entity\Animal',
                                alias => 'Animal',
                            ]
                        ],
                    ],
                    'human' => [
                        'connection' => 'human',
                        'mappings'   => [
                            'Human'  => [
                                is_bundle => false,
                                type => 'annotation',
                                dir => '%kernel.project_dir%/src/Entity/Human',
                                prefix => 'App\Entity\Human',
                                alias => 'Human',
                            ]
                        ],
                    ],
                ],
            ],
        ]);

In this case, you've defined two entity managers and called them ``default``
and ``human``. The ``default`` entity manager manages entities in the
``src/Entity/Animal`` directory, while the ``human`` entity manager manages
entities in ``src/Entity/Human``. You've also defined two connections, one
for each entity manager, but you are free to define the same connection for both.

.. caution::

    When working with multiple connections and entity managers, you should be
    explicit about which configuration you want. If you *do* omit the name of
    the connection or entity manager, the default (i.e. ``default``) is used.

    If you use a different name than ``default`` for the default entity manager,
    you will need to redefine the default entity manager in ``prod`` environment
    configuration too:

    .. code-block:: yaml

        # config/packages/prod/doctrine.yaml
        doctrine:
            orm:
                default_entity_manager: 'your default entity manager name'

        # ...

When working with multiple connections to create your databases:

.. code-block:: terminal

    # Play only with "default" connection
    $ php bin/console doctrine:database:create

    # Play only with "human" connection
    $ php bin/console doctrine:database:create --connection=human

When working with multiple entity managers to generate migrations:

.. code-block:: terminal

    # Play only with "default" mappings
    $ php bin/console doctrine:migrations:diff
    $ php bin/console doctrine:migrations:migrate

    # Play only with "human" mappings
    $ php bin/console doctrine:migrations:diff --em=human
    $ php bin/console doctrine:migrations:migrate --em=human

If you *do* omit the entity manager's name when asking for it,
the default entity manager (i.e. ``default``) is returned::

    // ...

    use Doctrine\ORM\EntityManagerInterface;

    class UserController extends AbstractController
    {
        public function index(EntityManagerInterface $entityManager)
        {
            // These methods also return the default entity manager, but it's preferred
            // to get it by injecting EntityManagerInterface in the action method
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager = $this->getDoctrine()->getManager('default');
            $entityManager = $this->get('doctrine.orm.default_entity_manager');

            // Both of these return the "human" entity manager
            $humanEntityManager = $this->getDoctrine()->getManager('human');
            $humanEntityManager = $this->get('doctrine.orm.human_entity_manager');
        }
    }

You can now use Doctrine just as you did before - using the ``default`` entity
manager to persist and fetch entities that it manages and the ``human``
entity manager to persist and fetch its entities.

Multiple Entity Managers and repositories
=========================================
Your entities usually have a custom repository associated with them. These repositories
are usually generated by Symfony commands such as the :doc:`make:entity </doctrine>` command that will
create repositories classes supporting autowiring, such as::

    // src/Repository/HumanRepository.php
    namespace App\Repository;

    use App\Entity\Human\Customer;
    use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
    use Doctrine\Common\Persistence\ManagerRegistry;

    class CustomerRepository extends ServiceEntityRepository
    {
        public function __construct(ManagerRegistry $registry)
        {
            parent::__construct($registry, Customer::class);
        }
    }

As long as you are explicit about what configuration you want, repositories calls can be really flexible::

    // src/Controller/DefaultController.php
    use App\Entity\Human\Customer;
    use App\Repository\CustomerRepository;

    // ...
    public function index(EntityManagerInterface $em, CustomerRepository $customerRepository)
    {
        // Retrieves a repository managed by the "human" em using autowiring
        $customers = $customerRepository->findAll();

        // Retrieves a repository managed by the "human" em
        // because only the "human" entity manager is set to manage the Customer entity
        $customers = $this->getDoctrine()
            ->getRepository(Customer::class)
            ->findAll();

        // Retrieves a repository managed by the "human" em, in an explicit way
        $customers = $this->getDoctrine()
            ->getRepository(Customer::class, 'human')
            ->findAll();

        // Same as the previous call
        $customers = $this->getDoctrine()
            ->getManager('human')
            ->getRepository(Customer::class)
            ->findAll();

        // Throws a "MappingException": the "default" em does not manage the Customer entity!
        $customers = $this->getDoctrine()
            ->getRepository(Customer::class, 'default')
            ->findAll();

        // Throws a "MappingException": the autowired $em instance is the "default" em
        // and this entity manager does not manage the Customer Entity!
        $customers = $em
            // Note: this method of a concrete $em object cannot take a second argument!
            ->getRepository(Customer::class)
            ->findAll();
    }

Entity Managers managing common Entities
=========================================

Some specific use cases may lead to define entities that are managed by more than one entity manager. While this is a supported use case, there are some important limitations to consider.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/doctrine.yaml
        doctrine:
            dbal:
                default_connection: default
                connections:
                    default:
                        # configure these for your database server
                        url: '%env(DATABASE_URL)%'
                        driver: 'pdo_mysql'
                        server_version: '5.7'
                        charset: utf8mb4
                    human:
                        # configure these for your database server
                        url: '%env(DATABASE_HUMAN_URL)%'
                        driver: 'pdo_mysql'
                        server_version: '5.7'
                        charset: utf8mb4
            orm:
                default_entity_manager: default
                entity_managers:
                    default:
                        connection: default
                        mappings:
                            Animal:
                                is_bundle: false
                                type: annotation
                                dir: '%kernel.project_dir%/src/Entity/Animal'
                                prefix: 'App\Entity\Animal'
                                alias: Animal
                    human:
                        connection: human
                        mappings:
                            Human:
                                is_bundle: false
                                type: annotation
                                dir: '%kernel.project_dir%/src/Entity/Human'
                                prefix: 'App\Entity\Human'
                                alias: Human
                    creature:
                        connection: default
                        mappings:
                            Creature:
                                is_bundle: false
                                type: annotation
                                dir: '%kernel.project_dir%/src/Entity'
                                prefix: 'App\Entity'
                                alias: Creature

    .. code-block:: xml

        <!-- config/packages/doctrine.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:doctrine="http://symfony.com/schema/dic/doctrine"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/doctrine
                https://symfony.com/schema/dic/doctrine/doctrine-1.0.xsd">

            <doctrine:config>
                <doctrine:dbal default-connection="default">
                    <!-- configure these for your database server -->
                    <doctrine:connection name="default"
                        url="%env(DATABASE_URL)%"
                        driver="pdo_mysql"
                        server_version="5.7"
                        charset="utf8mb4"
                    />

                    <!-- configure these for your database server -->
                    <doctrine:connection name="human"
                        url="%env(DATABASE_HUMAN_URL)%"
                        driver="pdo_mysql"
                        server_version="5.7"
                        charset="utf8mb4"
                    />
                </doctrine:dbal>

                <doctrine:orm default-entity-manager="default">
                    <doctrine:entity-manager name="default" connection="default">
                        <doctrine:mapping
                            name="Animal"
                            is_bundle="false"
                            type="annotation"
                            dir="%kernel.project_dir%/src/Entity/Animal"
                            prefix="App\Entity\Animal"
                            alias="Animal"
                        />
                    </doctrine:entity-manager>

                    <doctrine:entity-manager name="human" connection="human">
                        <doctrine:mapping
                            name="Human"
                            is_bundle="false"
                            type="annotation"
                            dir="%kernel.project_dir%/src/Entity/Human"
                            prefix="App\Entity\Human"
                            alias="Human"
                        />
                    </doctrine:entity-manager>

                    <doctrine:entity-manager name="creature" connection="default">
                        <doctrine:mapping
                            name="Creature"
                            is_bundle="false"
                            type="annotation"
                            dir="%kernel.project_dir%/src/Entity"
                            prefix="App\Entity"
                            alias="Creature"
                        />
                    </doctrine:entity-manager>
                </doctrine:orm>
            </doctrine:config>
        </container>

    .. code-block:: php

        // config/packages/doctrine.php
        $container->loadFromExtension('doctrine', [
            'dbal' => [
                'default_connection' => 'default',
                'connections' => [
                    // configure these for your database server
                    'default' => [
                        'url'            => '%env(DATABASE_URL)%',
                        'driver'         => 'pdo_mysql',
                        'server_version' => '5.7',
                        'charset'        => 'utf8mb4',
                    ],
                    // configure these for your database server
                    'human' => [
                        'url'            => '%env(DATABASE_HUMAN_URL)%',
                        'driver'         => 'pdo_mysql',
                        'server_version' => '5.7',
                        'charset'        => 'utf8mb4',
                    ],
                ],
            ],

            'orm' => [
                'default_entity_manager' => 'default',
                'entity_managers' => [
                    'default' => [
                        'connection' => 'default',
                        'mappings'   => [
                            'Animal'  => [
                                is_bundle => false,
                                type => 'annotation',
                                dir => '%kernel.project_dir%/src/Entity/Animal',
                                prefix => 'App\Entity\Animal',
                                alias => 'Animal',
                            ]
                        ],
                    ],
                    'human' => [
                        'connection' => 'human',
                        'mappings'   => [
                            'Human'  => [
                                is_bundle => false,
                                type => 'annotation',
                                dir => '%kernel.project_dir%/src/Entity/Human',
                                prefix => 'App\Entity\Human',
                                alias => 'Human',
                            ]
                        ],
                    ],
                    'creature' => [
                        'connection' => 'default',
                        'mappings'   => [
                            'Human'  => [
                                is_bundle => false,
                                type => 'annotation',
                                dir => '%kernel.project_dir%/src/Entity',
                                prefix => 'App\Entity',
                                alias => 'Creature',
                            ]
                        ],
                    ],
                ],
            ],
        ]);

In this case, you've defined a third entity manager called ``creature`` that manages
**all our entities**. The ``creature`` entity manager can reuse an existing connection or
defines a new one, here the ``default`` connection is used.

This specific use case however suffers from a limitation due to how our entity repositories
are defined and the fact that **service repositories only use by default the first defined entity manager for a given entity**::

    // src/Controller/DefaultController.php
    use App\Entity\Human\Customer;
    use App\Repository\CustomerRepository;

    // ...
    public function index(CustomerRepository $customerRepository)
    {
        // Retrieves a repository managed by the "human" em
        // because it's the first entity manager configured to manage the Customer Entity
        $customers1 = $customerRepository->findAll();

        // Retrieves a repository managed by the "human" em
        // again because it's the first entity manager configured to manage the Customer Entity
        $customers2 = $this->getDoctrine()
            ->getRepository(Customer::class)
            ->findAll();

        // Retrieves a repository managed by the "human" em
        $customers3 = $this->getDoctrine()
            ->getRepository(Customer::class, 'human')
            ->findAll();

        // Same as the previous call
        $customers3 = $this->getDoctrine()
            ->getManager('human')
            ->getRepository(Customer::class)
            ->findAll();

        // Retrieves a repository managed by the "human" em, not the "creature" em!
        $customers4 = $this->getDoctrine()
            ->getRepository(Customer::class, 'creature')
            ->findAll();
    }

The ``$customers4`` array here contains the very same entities instances than the ``$customers{1,2,3}`` array,  while they shoud be different, **separate entities instances**, from **two different** entities managers. The "service-styled definition" of the Customer repository does not allow the selection of a specific entity manager to use for the ``Customer`` entity, and, by default, **the first defined one is always used**.

One of the possible workaround to the limitation is to remove the autowiring support of  multi-managed entities repositories to ensure no implicit default choice is made internally: ::

    // src/Repository/CustomerRepository.php
    namespace App\Repository;

    use App\Entity\Human\Customer;
    use Doctrine\ORM\EntityRepository;

    class CustomerRepository extends EntityRepository
    {
        //
    }

The legacy definition of an entity repository, but making it non-serviceable, leads to the expected behavior::

    // src/Controller/DefaultController.php
    use App\Entity\Human\Customer;

    // ...
    public function index(/* The Customer repository is not serviceable anymore */)
    {
        // Retrieves a repository managed by the "human" em (first defined em still wins here)
        // You shoud be explicit!
        $customers1 = $this->getDoctrine()
            ->getRepository(Customer::class)
            ->findAll();

        // Retrieves a repository managed by the "human" em
        $customers2 = $this->getDoctrine()
            ->getRepository(Customer::class, 'human')
            ->findAll();

        // Same as:
        $customers2 = $this->getDoctrine()
            ->getManager('human')
            ->getRepository(Customer::class)
            ->findAll();

        // Retrieves a repository managed by the "creature" em
        $customers4 = $this->getDoctrine()
            ->getRepository(Customer::class, 'creature')
            ->findAll();

        // Same as:
        $customers4 = $this->getDoctrine()
            ->getRepository(Customer::class, 'creature')
            ->findAll();
    }

Here, ``$customers{1, 2}`` contain the same entities instances while ``$customers4`` now correctly contains its own entities instances.
