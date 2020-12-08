.. index::
    single: DependencyInjection; Synthetic Services

How to Inject Instances into the Container
------------------------------------------

In some applications, you may need to inject a class instance as service,
instead of configuring the container to create a new instance.

For instance, the ``kernel`` service in Symfony is injected into the container
from within the ``Kernel`` class::

    // ...
    use Symfony\Component\HttpKernel\KernelInterface;
    use Symfony\Component\HttpKernel\TerminableInterface;

    abstract class Kernel implements KernelInterface, TerminableInterface
    {
        // ...

        protected function initializeContainer(): void
        {
            // ...
            $this->container->set('kernel', $this);

            // ...
        }
    }

Services that are set at runtime are called *synthetic services*. This service
has to be configured so the container knows the service exists during compilation
(otherwise, services depending on ``kernel`` will get a "service does not exist" error).

In order to do so, mark the service as synthetic in your service definition
configuration:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # synthetic services don't specify a class
            app.synthetic_service:
                synthetic: true

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>

                <!-- synthetic services don't specify a class -->
                <service id="app.synthetic_service" synthetic="true"/>

            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return function(ContainerConfigurator $configurator) {
            $services = $configurator->services();

            // synthetic services don't specify a class
            $services->set('app.synthetic_service')
                ->synthetic();
        };


Now, you can inject the instance in the container using
:method:`Container::set() <Symfony\\Component\\DependencyInjection\\Container::set>`::

    // instantiate the synthetic service
    $theService = ...;
    $container->set('app.synthetic_service', $theService);
