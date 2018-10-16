.. index::
   single: DependencyInjection; Service configurators

How to Configure a Service with a Configurator
==============================================

The *service configurator* is a feature of the service container that allows
you to use a callable to configure a service after its instantiation.

A service configurator can be used, for example, when you have a service
that requires complex setup based on configuration settings coming from
different sources/services. Using an external configurator, you can maintain
the service implementation cleanly and keep it decoupled from the other
objects that provide the configuration needed.

Another use case is when you have multiple objects that share a common
configuration or that should be configured in a similar way at runtime.

For example, suppose you have an application where you send different types
of emails to users. Emails are passed through different formatters that
could be enabled or not depending on some dynamic application settings.
You start defining a ``NewsletterManager`` class like this::

    // src/Mail/NewsletterManager.php
    namespace App\Mail;

    class NewsletterManager implements EmailFormatterAwareInterface
    {
        private $enabledFormatters;

        public function setEnabledFormatters(array $enabledFormatters)
        {
            $this->enabledFormatters = $enabledFormatters;
        }

        // ...
    }

and also a ``GreetingCardManager`` class::

    // src/Mail/GreetingCardManager.php
    namespace App\Mail;

    class GreetingCardManager implements EmailFormatterAwareInterface
    {
        private $enabledFormatters;

        public function setEnabledFormatters(array $enabledFormatters)
        {
            $this->enabledFormatters = $enabledFormatters;
        }

        // ...
    }

As mentioned before, the goal is to set the formatters at runtime depending
on application settings. To do this, you also have an ``EmailFormatterManager``
class which is responsible for loading and validating formatters enabled
in the application::

    // src/Mail/EmailFormatterManager.php
    namespace App\Mail;

    class EmailFormatterManager
    {
        // ...

        public function getEnabledFormatters()
        {
            // code to configure which formatters to use
            $enabledFormatters = array(...);

            // ...

            return $enabledFormatters;
        }
    }

If your goal is to avoid having to couple ``NewsletterManager`` and
``GreetingCardManager`` with ``EmailFormatterManager``, then you might want
to create a configurator class to configure these instances::

    // src/Mail/EmailConfigurator.php
    namespace App\Mail;

    class EmailConfigurator
    {
        private $formatterManager;

        public function __construct(EmailFormatterManager $formatterManager)
        {
            $this->formatterManager = $formatterManager;
        }

        public function configure(EmailFormatterAwareInterface $emailManager)
        {
            $emailManager->setEnabledFormatters(
                $this->formatterManager->getEnabledFormatters()
            );
        }

        // ...
    }

The ``EmailConfigurator``'s job is to inject the enabled formatters into
``NewsletterManager`` and ``GreetingCardManager`` because they are not aware of
where the enabled formatters come from. On the other hand, the
``EmailFormatterManager`` holds the knowledge about the enabled formatters and
how to load them, keeping the single responsibility principle.

.. tip::

    While this example uses a PHP class method, configurators can be any valid
    PHP callable, including functions, static methods and methods of services.

Using the Configurator
----------------------

You can configure the service configurator using the ``configurator`` option. If
you're using the :ref:`default services.yaml configuration <service-container-services-load-example>`,
all the classes are already loaded as services. All you need to do is specify the
``configurator``:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            # Registers all 4 classes as services, including App\Mail\EmailConfigurator
            App\:
                resource: '../src/*'
                # ...

            # override the services to set the configurator
            App\Mail\NewsletterManager:
                configurator: 'App\Mail\EmailConfigurator:configure'

            App\Mail\GreetingCardManager:
                configurator: 'App\Mail\EmailConfigurator:configure'

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <prototype namespace="App\" resource="../src/*" />

                <service id="App\Mail\NewsletterManager">
                    <configurator service="App\Mail\EmailConfigurator" method="configure" />
                </service>

                <service id="App\Mail\GreetingCardManager">
                    <configurator service="App\Mail\EmailConfigurator" method="configure" />
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        use App\Mail\GreetingCardManager;
        use App\Mail\NewsletterManager;
        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\DependencyInjection\Reference;

        // Same as before
        $definition = new Definition();

        $definition->setAutowired(true);

        $this->registerClasses($definition, 'App\\', '../src/*');

        $container->getDefinition(NewsletterManager::class)
            ->setConfigurator(array(new Reference(EmailConfigurator::class), 'configure'));

        $container->getDefinition(GreetingCardManager::class)
            ->setConfigurator(array(new Reference(EmailConfigurator::class), 'configure'));

The traditional configurator syntax in YAML files used an array to define
the service id and the method name:

.. code-block:: yaml

    app.newsletter_manager:
        # new syntax
        configurator: 'App\Mail\EmailConfigurator:configure'
        # old syntax
        configurator: ['@App\Mail\EmailConfigurator', configure]

That's it! When requesting the ``App\Mail\NewsletterManager`` or
``App\Mail\GreetingCardManager`` service, the created instance will first be
passed to the ``EmailConfigurator::configure()`` method.
