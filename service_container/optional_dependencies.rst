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
