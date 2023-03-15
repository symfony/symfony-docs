.. index::
    single: DependencyInjection; Service Closures

Service Closures
================

This feature wraps the injected service into a closure allowing it to be
lazily loaded when and if needed.
This is useful if the service being injected is a bit heavy to instantiate
or is used only in certain cases.
The service is instantiated the first time the closure is called, while
all subsequent calls return the same instance, unless the service is
:doc:`not shared </service_container/shared>`::

    // src/Service/MyService.php
    namespace App\Service;

    use Symfony\Component\Mailer\MailerInterface;

    class MyService
    {
        /**
         * @param callable(): MailerInterface
         */
        public function __construct(
            private \Closure $mailer,
        ) {
        }

        public function doSomething(): void
        {
            // ...

            $this->getMailer()->send($email);
        }

        private function getMailer(): MailerInterface
        {
            return ($this->mailer)();
        }
    }

To define a service closure and inject it to another service, create an
argument of type ``service_closure``:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Service\MyService:
                arguments: [!service_closure '@mailer']

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Service\MyService">
                    <argument type="service_closure" id="mailer"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Service\MyService;

        return function (ContainerConfigurator $containerConfigurator) {
            $services = $containerConfigurator->services();

            $services->set(MyService::class)
                ->args([service_closure('mailer')]);

            // In case the dependency is optional
            // $services->set(MyService::class)
            //     ->args([service_closure('mailer')->ignoreOnInvalid()]);
        };

.. seealso::

    Another way to inject services lazily is via a
    :doc:`service locator </service_container/service_subscribers_locators>`.

Using a Service Closure in a Compiler Pass
------------------------------------------

In :doc:`compiler passes </service_container/compiler_passes>` you can create
a service closure by wrapping the service reference into an instance of
:class:`Symfony\\Component\\DependencyInjection\\Argument\\ServiceClosureArgument`::

    use Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Reference;

    public function process(ContainerBuilder $containerBuilder): void
    {
        // ...

        $myService->addArgument(new ServiceClosureArgument(new Reference('mailer')));
    }
