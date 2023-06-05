How to Define Non Shared Services
=================================

In the service container, all services are shared by default. This means that
each time you retrieve the service, you'll get the *same* instance. This is
usually the behavior you want, but in some cases, you might want to always get a
*new* instance.

In order to always get a new instance, set the ``shared`` setting to ``false``
in your service definition:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\SomeNonSharedService:
                shared: false
                # ...

    .. code-block:: xml

        <!-- config/services.xml -->
        <services>
            <service id="App\SomeNonSharedService" shared="false"/>
        </services>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\SomeNonSharedService;

        return function(ContainerConfigurator $container): void {
            $services = $container->services();

            $services->set(SomeNonSharedService::class)
                ->share(false);
        };

Now, whenever you request the ``App\SomeNonSharedService`` from the container,
you will be passed a new instance.
