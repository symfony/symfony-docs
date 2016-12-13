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

        public function __construct(ObjectManager $manager)
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
            app.base_doctrine_repository:
                # as no class is configured, the parent service MUST be abstract
                abstract:  true
                arguments: ['@doctrine.entity_manager']
                calls:
                    - [setLogger, ['@logger']]

            app.user_repository:
                class:  AppBundle\Repository\DoctrineUserRepository
                # extend the app.base_doctrine_repository service
                parent: app.base_doctrine_repository

            app.post_repository:
                class:  AppBundle\Repository\DoctrinePostRepository
                parent: app.base_doctrine_repository

            # ...

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- as no class is configured, the parent service MUST be abstract -->
                <service id="app.base_doctrine_repository" abstract="true"> 
                    <argument type="service" id="doctrine.entity_manager">

                    <call method="setLogger">
                        <argument type="service" id="logger" />
                    </call>
                </service>

                <!-- extends the app.base_doctrine_repository service -->
                <service id="app.user_repository"
                    class="AppBundle\Repository\DoctrineUserRepository"
                    parent="app.base_doctrine_repository"
                />

                <service id="app.post_repository"
                    class="AppBundle\Repository\DoctrineUserRepository"
                    parent="app.base_doctrine_repository"
                />

                <!-- ... -->
            </services>
        </container>

    .. code-block:: php

        use AppBundle\Repository\DoctrineUserRepository;
        use AppBundle\Repository\DoctrinePostRepository;
        use Symfony\Component\DependencyInjection\ChildDefinition;
        use Symfony\Component\DependencyInjection\Reference;

        // as no class is configured, the parent service MUST be abstract
        $container->register('app.base_doctrine_repository')
            ->addArgument(new Reference('doctrine.entity_manager'))
            ->addMethodCall('setLogger', array(new Reference('logger')))
        ;

        // extend the app.base_doctrine_repository service
        $definition = new ChildDefinition('app.base_doctrine_repository');
        $definition->setClass(DoctrineUserRepository::class);
        $container->setDefinition('app.user_repository', $definition);

        $definition = new ChildDefinition('app.base_doctrine_repository');
        $definition->setClass(DoctrinePostRepository::class);

        $container->setDefinition('app.post_repository', $definition);

        // ...

In this context, having a ``parent`` service implies that the arguments
and method calls of the parent service should be used for the child services.
Specifically, the ``EntityManager`` will be injected and ``setLogger()`` will
be called when ``app.user_repository`` is instantiated.

.. caution::

    The ``shared``, ``abstract`` and ``tags`` attributes are *not* inherited from
    parent services.

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

            app.user_repository:
                class:  AppBundle\Repository\DoctrineUserRepository
                parent: app.base_doctrine_repository

                # overrides the public setting of the parent service
                public: false

                # appends the '@app.username_checker' argument to the parent
                # argument list
                arguments: ['@app.username_checker']

            app.post_repository:
                class:  AppBundle\Repository\DoctrinePostRepository
                parent: app.base_doctrine_repository

                # overrides the first argument (using the special index_N key)
                arguments:
                    index_0: '@doctrine.custom_entity_manager'

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... -->

                <!-- overrides the public setting of the parent service -->
                <service id="app.user_repository"
                    class="AppBundle\Repository\DoctrineUserRepository"
                    parent="app.base_doctrine_repository"
                    public="false"
                >
                    <!-- appends the '@app.username_checker' argument to the parent
                         argument list -->
                    <argument type="service" id="app.username_checker" />
                </service>

                <service id="app.post_repository"
                    class="AppBundle\Repository\DoctrineUserRepository"
                    parent="app.base_doctrine_repository"
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
        use Symfony\Component\DependencyInjection\ChildDefinition;
        use Symfony\Component\DependencyInjection\Reference;
        // ...

        $definition = new ChildDefinition('app.base_doctrine_repository');
        $definition->setClass(DoctrineUserRepository::class);
        // overrides the public setting of the parent service
        $definition->setPublic(false);
        // appends the '@app.username_checker' argument to the parent argument list
        $definition->addArgument(new Reference('app.username_checker'));
        $container->setDefinition('app.user_repository', $definition);

        $definition = new ChildDefinition('app.base_doctrine_repository');
        $definition->setClass(DoctrinePostRepository::class);
        // overrides the first argument
        $definition->replaceArgument(0, new Reference('doctrine.custom_entity_manager'));
        $container->setDefinition('app.post_repository', $definition);
