How to Make Service Arguments/References Optional
=================================================

Sometimes, one of your services may have an optional dependency, meaning
that the dependency is not required for your service to work properly. You can
configure the container to not throw an error in this case.

Setting Missing Dependencies to null
------------------------------------

You can use the ``null`` strategy to explicitly set the argument to ``null``
if the service does not exist:

.. configuration-block::

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... -->

                <service id="App\Newsletter\NewsletterManager">
                    <argument type="service" id="logger" on-invalid="null"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Newsletter\NewsletterManager;

        return function(ContainerConfigurator $container): void {
            $services = $container->services();

            $services->set(NewsletterManager::class)
                ->args([service('logger')->nullOnInvalid()]);
        };

.. note::

    The "null" strategy is not currently supported by the YAML driver.

Ignoring Missing Dependencies
-----------------------------

The behavior of ignoring missing dependencies is the same as the "null" behavior
except when used within a method call, in which case the method call itself
will be removed.

In the following example the container will inject a service using a method
call if the service exists and remove the method call if it does not:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Newsletter\NewsletterManager:
                calls:
                    - setLogger: ['@?logger']

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Newsletter\NewsletterManager">
                    <call method="setLogger">
                        <argument type="service" id="logger" on-invalid="ignore"/>
                    </call>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Newsletter\NewsletterManager;

        return function(ContainerConfigurator $container): void {
            $services = $container->services();

            $services->set(NewsletterManager::class)
                ->call('setLogger', [service('logger')->ignoreOnInvalid()])
            ;
        };

.. note::

    If the argument to the method call is a collection of arguments and any of
    them is missing, those elements are removed but the method call is still
    made with the remaining elements of the collection.

In YAML, the special ``@?`` syntax tells the service container that the
dependency is optional. The ``NewsletterManager`` must also be rewritten by
adding a ``setLogger()`` method::

        public function setLogger(LoggerInterface $logger): void
        {
            // ...
        }

Ignoring Uninitialized Dependencies
-----------------------------------

Some services only need a dependency if the application already used it. For
example, a listener that flushes a newsletter queue at the end of the request
doesn't need to create the queue when the request didn't add anything to it.
Use the ``ignore_uninitialized`` strategy to inject the service only if the
container already created it, and ``null`` otherwise:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\EventListener\NewsletterQueueFlusher:
                # the '@!' syntax injects the service only if it was already created
                arguments: ['@!App\Newsletter\NewsletterQueue']

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\EventListener\NewsletterQueueFlusher">
                    <argument type="service" id="App\Newsletter\NewsletterQueue"
                        on-invalid="ignore_uninitialized"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\EventListener\NewsletterQueueFlusher;
        use App\Newsletter\NewsletterQueue;

        return function(ContainerConfigurator $container): void {
            $services = $container->services();

            $services->set(NewsletterQueueFlusher::class)
                ->args([service(NewsletterQueue::class)->ignoreOnUninitialized()]);
        };

The container checks whether it already created the dependency at the moment
it creates the service that uses it, not later. This works in the example
because the container creates event listeners only when the application
dispatches their event. When used within a method call, the container removes
the method call if it hasn't created the dependency yet, like the ``ignore``
strategy does for missing services.
