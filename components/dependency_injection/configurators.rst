.. index::
   single: Dependency Injection; Service configurators

Configuring Services with a Service Configurator
================================================

The Service Configurator is a feature of the Dependency Injection Container that
allows you to use a callable to configure a service after its instantiation.

You can specify a method in another service, a PHP function or a static method
in a class. The service instance is passed to the callable, allowing the
configurator to do whatever it needs to configure the service after its
creation.

A Service Configurator can be used, for example, when you a have a service that
requires complex setup based on configuration settings coming from different
sources/services. Using an external configurator, you can maintain the service
implementation clean and decoupled from the other objects that provide the
configuration for the service.

Another interesting use case is when you have multiple objects that share a
common configuration or that should be configured in a similar way at runtime.

For example, suppose you have an application where you send different types of
emails to users. Emails are passed through different formatters that could be
enabled or not depending on some dynamic application settings. You start
defining a ``NewsletterManager`` class like this::

    class NewsletterManager implements EmailFormatterAwareInterface
    {
        protected $mailer;
        protected $enabledFormatters;

        public function setMailer(Mailer $mailer)
        {
            $this->mailer = $mailer;
        }

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

        public function setMailer(Mailer $mailer)
        {
            $this->mailer = $mailer;
        }

        public function setEnabledFormatters(array $enabledFormatters)
        {
            $this->enabledFormatters = $enabledFormatters;
        }

        // ...
    }


As commented before, the formatters should be set at runtime depending on
application settings, so we also have a ``EmailFormatterManager`` class which is
responsible for loading and validating formatters enabled in the application::

    class EmailFormatterManager
    {
        protected $enabledFormatters;

        public function loadFormatters()
        {
            // ...
        }

        public function getEnabledFormatters()
        {
            return $this->enabledFormatters;
        }

        // ...
    }

If your goal is to avoid having to couple ``NewsletterManager`` and
``GreetingCardManager`` with ``EmailFormatterManager``, then you might want to
create a configurator class to configure these instances::

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

``EmailConfigurator`` task is to inject enabled filters to ``NewsletterManager``
and ``GreetingCardManager`` because they are not aware of where the enabled
filters come from. In the other hand, the ``EmailFormatterManager`` holds the
knowledge about the enabled formatters and how to load them, keeping the single
responsibility principle.

Configurator Service Config
---------------------------

The service config for the above classes would look something like this:

.. configuration-block::

    .. code-block:: yaml

        parameters:
            # ...
            newsletter_manager.class: NewsletterManager
            greeting_card_manager.class: GreetingCardManager
            email_formatter_manager.class: EmailFormatterManager
            email_configurator.class: EmailConfigurator

        services:
            my_mailer:
                # ...

            email_formatter_manager:
                class:     "%email_formatter_manager.class%"
                # ...

            email_configurator:
                class:     "%email_configurator.class%"
                arguments: [@email_formatter_manager]
                # ...

            newsletter_manager:
                class:     "%newsletter_manager.class%"
                calls:
                    - [setMailer, [@my_mailer]]
                configurator: [@email_configurator, configure]

            greeting_card_manager:
                class:     "%greeting_card_manager.class%"
                calls:
                    - [setMailer, [@my_mailer]]
                configurator: [@email_configurator, configure]


    .. code-block:: xml

        <parameters>
            <!-- ... -->
            <parameter key="newsletter_manager.class">NewsletterManager</parameter>
            <parameter key="greeting_card_manager.class">GreetingCardManager</parameter>
            <parameter key="email_formatter_manager.class">EmailFormatterManager</parameter>
            <parameter key="email_configurator.class">EmailConfigurator</parameter>
        </parameters>

        <services>
            <service id="my_mailer" ...>
              <!-- ... -->
            </service>
            <service id="email_formatter_manager" class="%email_formatter_manager.class%">
              <!-- ... -->
            </service>
            <service id="email_configurator" class="%email_configurator.class%">
                <argument type="service" id="email_formatter_manager" />
              <!-- ... -->
            </service>
            <service id="newsletter_manager" class="%newsletter_manager.class%">
                <call method="setMailer">
                     <argument type="service" id="my_mailer" />
                </call>
                <configurator service="email_configurator" method="configure" />
            </service>
            <service id="greeting_card_manager" class="%greeting_card_manager.class%">
                <call method="setMailer">
                     <argument type="service" id="my_mailer" />
                </call>
                <configurator service="email_configurator" method="configure" />
            </service>
        </services>

    .. code-block:: php

        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\DependencyInjection\Reference;

        // ...
        $container->setParameter('newsletter_manager.class', 'NewsletterManager');
        $container->setParameter('greeting_card_manager.class', 'GreetingCardManager');
        $container->setParameter('email_formatter_manager.class', 'EmailFormatterManager');
        $container->setParameter('email_configurator.class', 'EmailConfigurator');

        $container->setDefinition('my_mailer', ...);
        $container->setDefinition('email_formatter_manager', ...);
        $container->setDefinition('email_configurator', ...);
        $container->setDefinition('newsletter_manager', new Definition(
            '%newsletter_manager.class%'
        ))->addMethodCall('setMailer', array(
            new Reference('my_mailer'),
        ))->setConfigurator(array(
            new Reference('email_configurator'),
            'configure',
        )));
        $container->setDefinition('greeting_card_manager', new Definition(
            '%greeting_card_manager.class%'
        ))->addMethodCall('setMailer', array(
            new Reference('my_mailer'),
        ))->setConfigurator(array(
            new Reference('email_configurator'),
            'configure',
        )));
