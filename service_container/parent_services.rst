.. index::
    single: DependencyInjection; Parent services

How to Manage Common Dependencies with Parent Services
======================================================

As you add more functionality to your application, you may well start to
have related classes that share some of the same dependencies. For example,
you may have multiple repository classes which need the
``doctrine.entity_manager`` service and an optional ``logger`` service::

    // src/AppBundle/Repository/BaseDoctrineRepository.php
    namespace AppBundle\Repository;

    // ...
    abstract class BaseDoctrineRepository
    {
        protected $entityManager;
        protected $logger;

        public function __construct(EntityManagerInterface $manager)
        {
            $this->entityManager = $manager;
        }

        public function setLogger(LoggerInterface $logger)
        {
            $this->logger = $logger;
        }

        // ...
    }

Just as you use PHP inheritance to avoid duplication in your PHP code, the
service container allows you to extend parent services in order to avoid
duplicated service definitions:

.. configuration-block::

    .. code-block:: yaml

        services:
            AppBundle\Repository\BaseDoctrineRepository:
                abstract:  true
                arguments: ['@doctrine.entity_manager']
                calls:
                    - [setLogger, ['@logger']]

            AppBundle\Repository\DoctrineUserRepository:
                # extend the AppBundle\Repository\BaseDoctrineRepository service
                parent: AppBundle\Repository\BaseDoctrineRepository

            AppBundle\Repository\DoctrinePostRepository:
                parent: AppBundle\Repository\BaseDoctrineRepository

            # ...

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="AppBundle\Repository\BaseDoctrineRepository" abstract="true">
                    <argument type="service" id="doctrine.entity_manager" />

                    <call method="setLogger">
                        <argument type="service" id="logger" />
                    </call>
                </service>

                <!-- extends the AppBundle\Repository\BaseDoctrineRepository service -->
                <service id="AppBundle\Repository\DoctrineUserRepository"
                    parent="AppBundle\Repository\BaseDoctrineRepository"
                />

                <service id="AppBundle\Repository\DoctrinePostRepository"
                    parent="AppBundle\Repository\BaseDoctrineRepository"
                />

                <!-- ... -->
            </services>
        </container>

    .. code-block:: php

        use AppBundle\Repository\DoctrineUserRepository;
        use AppBundle\Repository\DoctrinePostRepository;
        use AppBundle\Repository\BaseDoctrineRepository;
        use Symfony\Component\DependencyInjection\ChildDefinition;
        use Symfony\Component\DependencyInjection\Reference;

        $container->register(BaseDoctrineRepository::class)
            ->setAbstract(true)
            ->addArgument(new Reference('doctrine.entity_manager'))
            ->addMethodCall('setLogger', array(new Reference('logger')))
        ;

        // extend the AppBundle\Repository\BaseDoctrineRepository service
        $definition = new ChildDefinition(BaseDoctrineRepository::class);
        $definition->setClass(DoctrineUserRepository::class);
        $container->setDefinition(DoctrineUserRepository::class, $definition);

        $definition = new ChildDefinition(BaseDoctrineRepository::class);
        $definition->setClass(DoctrinePostRepository::class);
        $container->setDefinition(DoctrinePostRepository::class, $definition);

        // ...

In this context, having a ``parent`` service implies that the arguments
and method calls of the parent service should be used for the child services.
Specifically, the ``EntityManager`` will be injected and ``setLogger()`` will
be called when ``AppBundle\Repository\DoctrineUserRepository`` is instantiated.

All attributes on the parent service are shared with the child **except** for
``shared``, ``abstract`` and ``tags``. These are *not* inherited from the parent.

.. note::

    If you have a ``_defaults`` section in your file, all child services are required
    to explicitly override those values to avoid ambiguity. You will see a clear
    error message about this.

.. tip::

    In the examples shown, the classes sharing the same configuration also
    extend from the same parent class in PHP. This isn't necessary at all.
    You can just extract common parts of similar service definitions into
    a parent service without also extending a parent class in PHP.

Overriding Parent Dependencies
------------------------------

There may be times where you want to override what service is injected for
one child service only. You can override most settings by simply specifying it
in the child class:

.. configuration-block::

    .. code-block:: yaml

        services:
            # ...

            AppBundle\Repository\DoctrineUserRepository:
                parent: AppBundle\Repository\BaseDoctrineRepository

                # overrides the public setting of the parent service
                public: false

                # appends the '@app.username_checker' argument to the parent
                # argument list
                arguments: ['@app.username_checker']

            AppBundle\Repository\DoctrinePostRepository:
                parent: AppBundle\Repository\BaseDoctrineRepository

                # overrides the first argument (using the special index_N key)
                arguments:
                    index_0: '@doctrine.custom_entity_manager'

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... -->

                <!-- overrides the public setting of the parent service -->
                <service id="AppBundle\Repository\DoctrineUserRepository"
                    parent="AppBundle\Repository\BaseDoctrineRepository"
                    public="false"
                >
                    <!-- appends the '@app.username_checker' argument to the parent
                         argument list -->
                    <argument type="service" id="app.username_checker" />
                </service>

                <service id="AppBundle\Repository\DoctrinePostRepository"
                    parent="AppBundle\Repository\BaseDoctrineRepository"
                >
                    <!-- overrides the first argument (using the index attribute) -->
                    <argument index="0" type="service" id="doctrine.custom_entity_manager" />
                </service>

                <!-- ... -->
            </services>
        </container>

    .. code-block:: php

        use AppBundle\Repository\DoctrineUserRepository;
        use AppBundle\Repository\DoctrinePostRepository;
        use AppBundle\Repository\BaseDoctrineRepository;
        use Symfony\Component\DependencyInjection\ChildDefinition;
        use Symfony\Component\DependencyInjection\Reference;
        // ...

        $definition = new ChildDefinition(BaseDoctrineRepository::class);
        $definition->setClass(DoctrineUserRepository::class);
        // overrides the public setting of the parent service
        $definition->setPublic(false);
        // appends the '@app.username_checker' argument to the parent argument list
        $definition->addArgument(new Reference('app.username_checker'));
        $container->setDefinition(DoctrineUserRepository::class, $definition);

        $definition = new ChildDefinition(BaseDoctrineRepository::class);
        $definition->setClass(DoctrinePostRepository::class);
        // overrides the first argument
        $definition->replaceArgument(0, new Reference('doctrine.custom_entity_manager'));
        $container->setDefinition(DoctrinePostRepository::class, $definition);
