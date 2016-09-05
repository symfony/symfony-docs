.. index::
    single: DependencyInjection; Synthetic Services

How to Inject Instances into the Container
------------------------------------------

In some applications, you may need to inject a class instance as service,
instead of configuring the container to create a new instance.

For instance, the ``kernel`` service in Symfony is injected into the container
from within the ``Kernel`` class::

    // ...
    abstract class Kernel implements KernelInterface, TerminableInterface
    {
        // ...

        protected function initializeContainer()
        {
            // ...
            $this->container->set('kernel', $this);

            // ...
        }
    }

Services that are set at runtime are called *synthetic services*. This service
has to be configured in the container, so the container knows the service does
exist during compilation (otherwise, services depending on this ``kernel``
service will get a "service does not exist" error).

In order to do so, mark the service as synthetic in your service definition
configuration:

.. configuration-block::

    .. code-block:: yaml

        services:

            // synthetic services don't specify a class
            app.synthetic_service:
                synthetic: true

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>

                <!-- synthetic services don't specify a class -->
                <service id="app.synthetic_service" synthetic="true" />

            </services>
        </container>

    .. code-block:: php

        // synthetic services don't specify a class
        $container->register('app.synthetic_service')
            ->setSynthetic(true)
        ;

Now, you can inject the instance in the container using
:method:`Container::set() <Symfony\\Component\\DependencyInjection\\Container::set>`::

    // instantiate the synthetic service
    $theService = ...;
    $container->set('app.synthetic_service', $theService);
