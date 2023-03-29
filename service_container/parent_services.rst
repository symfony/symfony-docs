How to Manage Common Dependencies with Parent Services
======================================================

As you add more functionality to your application, you may well start to
have related classes that share some of the same dependencies. For example,
you may have multiple repository classes which need the
``doctrine.orm.entity_manager`` service and an optional ``logger`` service::

    // src/Repository/BaseDoctrineRepository.php
    namespace App\Repository;

    use Doctrine\Persistence\ObjectManager;
    use Psr\Log\LoggerInterface;

    // ...
    abstract class BaseDoctrineRepository
    {
        protected $logger;

        public function __construct(
            protected ObjectManager $objectManager,
        ) {
        }

        public function setLogger(LoggerInterface $logger): void
        {
            $this->logger = $logger;
        }

        // ...
    }

Your child service classes may look like this::

    // src/Repository/DoctrineUserRepository.php
    namespace App\Repository;

    use App\Repository\BaseDoctrineRepository;

    // ...
    class DoctrineUserRepository extends BaseDoctrineRepository
    {
        // ...
    }

    // src/Repository/DoctrinePostRepository.php
    namespace App\Repository;

    use App\Repository\BaseDoctrineRepository;

    // ...
    class DoctrinePostRepository extends BaseDoctrineRepository
    {
        // ...
    }

The service container allows you to extend parent services in order to
avoid duplicated service definitions:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Repository\BaseDoctrineRepository:
                abstract:  true
                arguments: ['@doctrine.orm.entity_manager']
                calls:
                    - setLogger: ['@logger']

            App\Repository\DoctrineUserRepository:
                # extend the App\Repository\BaseDoctrineRepository service
                parent: App\Repository\BaseDoctrineRepository

            App\Repository\DoctrinePostRepository:
                parent: App\Repository\BaseDoctrineRepository

            # ...

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Repository\BaseDoctrineRepository" abstract="true">
                    <argument type="service" id="doctrine.orm.entity_manager"/>

                    <call method="setLogger">
                        <argument type="service" id="logger"/>
                    </call>
                </service>

                <!-- extends the App\Repository\BaseDoctrineRepository service -->
                <service id="App\Repository\DoctrineUserRepository"
                    parent="App\Repository\BaseDoctrineRepository"
                />

                <service id="App\Repository\DoctrinePostRepository"
                    parent="App\Repository\BaseDoctrineRepository"
                />

                <!-- ... -->
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Repository\BaseDoctrineRepository;
        use App\Repository\DoctrinePostRepository;
        use App\Repository\DoctrineUserRepository;

        return function(ContainerConfigurator $containerConfigurator) {
            $services = $containerConfigurator->services();

            $services->set(BaseDoctrineRepository::class)
                ->abstract()
                ->args([service('doctrine.orm.entity_manager')])
                ->call('setLogger', [service('logger')])
            ;

            $services->set(DoctrineUserRepository::class)
                // extend the App\Repository\BaseDoctrineRepository service
                ->parent(BaseDoctrineRepository::class)
            ;

            $services->set(DoctrinePostRepository::class)
                ->parent(BaseDoctrineRepository::class)
            ;
        };

In this context, having a ``parent`` service implies that the arguments
and method calls of the parent service should be used for the child services.
Specifically, the ``EntityManager`` will be injected and ``setLogger()`` will
be called when ``App\Repository\DoctrineUserRepository`` is instantiated.

All attributes on the parent service are shared with the child **except** for
``shared``, ``abstract`` and ``tags``. These are *not* inherited from the parent.

.. tip::

    In the examples shown, the classes sharing the same configuration also
    extend from the same parent class in PHP. This isn't necessary at all.
    You can also extract common parts of similar service definitions into
    a parent service without also extending a parent class in PHP.

Overriding Parent Dependencies
------------------------------

There may be times where you want to override what service is injected for
one child service only. You can override most settings by specifying it in
the child class:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            App\Repository\DoctrineUserRepository:
                parent: App\Repository\BaseDoctrineRepository

                # overrides the private setting of the parent service
                public: true

                # appends the '@app.username_checker' argument to the parent
                # argument list
                arguments: ['@app.username_checker']

            App\Repository\DoctrinePostRepository:
                parent: App\Repository\BaseDoctrineRepository

                # overrides the first argument (using the special index_N key)
                arguments:
                    index_0: '@doctrine.custom_entity_manager'

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... -->

                <!-- overrides the private setting of the parent service -->
                <service id="App\Repository\DoctrineUserRepository"
                    parent="App\Repository\BaseDoctrineRepository"
                    public="true"
                >
                    <!-- appends the '@app.username_checker' argument to the parent
                         argument list -->
                    <argument type="service" id="app.username_checker"/>
                </service>

                <service id="App\Repository\DoctrinePostRepository"
                    parent="App\Repository\BaseDoctrineRepository"
                >
                    <!-- overrides the first argument (using the index attribute) -->
                    <argument index="0" type="service" id="doctrine.custom_entity_manager"/>
                </service>

                <!-- ... -->
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Repository\BaseDoctrineRepository;
        use App\Repository\DoctrinePostRepository;
        use App\Repository\DoctrineUserRepository;
        // ...

        return function(ContainerConfigurator $containerConfigurator) {
            $services = $containerConfigurator->services();

            $services->set(BaseDoctrineRepository::class)
                // ...
            ;

            $services->set(DoctrineUserRepository::class)
                ->parent(BaseDoctrineRepository::class)

                // overrides the private setting of the parent service
                ->public()

                // appends the '@app.username_checker' argument to the parent
                // argument list
                ->args([service('app.username_checker')])
            ;

            $services->set(DoctrinePostRepository::class)
                ->parent(BaseDoctrineRepository::class)

                # overrides the first argument
                ->arg(0, service('doctrine.custom_entity_manager'))
            ;
        };
