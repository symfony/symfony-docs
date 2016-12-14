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

    class NewsletterManager implements EmailFormatterAwareInterface
    {
        protected $mailer;
        protected $enabledFormatters;

        public function setEnabledFormatters(array $enabledFormatters)
        {
            $this->enabledFormatters = $enabledFormatters;
        }

        // ...
    }

and also a ``GreetingCardManager`` class::

    class GreetingCardManager implements EmailFormatterAwareInterface
    {
        protected $mailer;
        protected $enabledFormatters;

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

You can configure the service configurator using the ``configurator`` option:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            app.email_formatter_manager:
                class: EmailFormatterManager
                # ...

            app.email_configurator:
                class:     AppBundle\Mail\EmailConfigurator
                arguments: ['@app.email_formatter_manager']
                # ...

            app.newsletter_manager:
                class:        AppBundle\Mail\NewsletterManager
                arguments:    ['@mailer']
                configurator: ['@app.email_configurator', configure]

            app.greeting_card_manager:
                class:        AppBundle\Mail\GreetingCardManager
                arguments:    ['@mailer']
                configurator: ['@app.email_configurator', configure]

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="app.email_formatter_manager" class="AppBundle\Mail\EmailFormatterManager">
                    <!-- ... -->
                </service>

                <service id="app.email_configurator" class="AppBundle\Mail\EmailConfigurator">
                    <argument type="service" id="app.email_formatter_manager" />
                    <!-- ... -->
                </service>

                <service id="app.newsletter_manager" class="AppBundle\Mail\NewsletterManager">
                    <argument type="service" id="mailer" />

                    <configurator service="app.email_configurator" method="configure" />
                </service>

                <service id="greeting_card_manager" class="GreetingCardManager">
                    <argument type="service" id="mailer" />

                    <configurator service="app.email_configurator" method="configure" />
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/services.php
        use AppBundle\Mail\EmailConfigurator;
        use AppBundle\Mail\EmailFormatterManager;
        use AppBundle\Mail\GreetingCardManager;
        use AppBundle\Mail\NewsletterManager;
        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\DependencyInjection\Reference;

        // ...
        $container->register('app.email_formatter_manager', EmailFormatterManager::class);
        $container->register('app.email_configurator', EmailConfigurator::class);

        $container->register('app.newsletter_manager', NewsletterManager::class)
            ->addArgument(new Reference('mailer'))
            ->setConfigurator(array(new Reference('app.email_configurator'), 'configure'))
        ;

        $container->register('app.greeting_card_manager', GreetingCardManager::class);
            ->addArgument(new Reference('mailer'))
            ->setConfigurator(array(new Reference('app.email_configurator'), 'configure'))
        ;

That's it! When requesting the ``app.newsletter_manager`` or
``app.greeting_card_manager`` service, the created instance will first be
passed to the ``EmailConfigurator::configure()`` method.
