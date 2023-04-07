How to Remove a Service
=======================

A service can be removed from the service container if needed
(for instance in the test or a specific environment):

.. configuration-block::

    .. code-block:: php

        // config/services_test.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\RemovedService;

        return function(ContainerConfigurator $containerConfigurator) {
            $services = $containerConfigurator->services();

            $services->remove(RemovedService::class);
        };

Now, the container will not contain the ``App\RemovedService``
in the test environment.
