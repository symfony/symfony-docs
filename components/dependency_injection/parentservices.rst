.. index::
   single: Dependency Injection; Parent services

Managing Common Dependencies with Parent Services
=================================================

As you add more functionality to your application, you may well start to have
related classes that share some of the same dependencies. For example you
may have a Newsletter Manager which uses setter injection to set its dependencies::

    class NewsletterManager
    {
        protected $mailer;
        protected $emailFormatter;

        public function setMailer(Mailer $mailer)
        {
            $this->mailer = $mailer;
        }

        public function setEmailFormatter(EmailFormatter $emailFormatter)
        {
            $this->emailFormatter = $emailFormatter;
        }

        // ...
    }

and also a Greeting Card class which shares the same dependencies::

    class GreetingCardManager
    {
        protected $mailer;
        protected $emailFormatter;

        public function setMailer(Mailer $mailer)
        {
            $this->mailer = $mailer;
        }

        public function setEmailFormatter(EmailFormatter $emailFormatter)
        {
            $this->emailFormatter = $emailFormatter;
        }

        // ...
    }

The service config for these classes would look something like this:

.. configuration-block::

    .. code-block:: yaml

        parameters:
            # ...
            newsletter_manager.class: NewsletterManager
            greeting_card_manager.class: GreetingCardManager
        services:
            my_mailer:
                # ...
            my_email_formatter:
                # ...
            newsletter_manager:
                class:     "%newsletter_manager.class%"
                calls:
                    - [setMailer, ["@my_mailer"]]
                    - [setEmailFormatter, ["@my_email_formatter"]]

            greeting_card_manager:
                class:     "%greeting_card_manager.class%"
                calls:
                    - [setMailer, ["@my_mailer"]]
                    - [setEmailFormatter, ["@my_email_formatter"]]

    .. code-block:: xml

        <parameters>
            <!-- ... -->
            <parameter key="newsletter_manager.class">NewsletterManager</parameter>
            <parameter key="greeting_card_manager.class">GreetingCardManager</parameter>
        </parameters>

        <services>
            <service id="my_mailer" ...>
              <!-- ... -->
            </service>
            <service id="my_email_formatter" ...>
              <!-- ... -->
            </service>
            <service id="newsletter_manager" class="%newsletter_manager.class%">
                <call method="setMailer">
                     <argument type="service" id="my_mailer" />
                </call>
                <call method="setEmailFormatter">
                     <argument type="service" id="my_email_formatter" />
                </call>
            </service>
            <service id="greeting_card_manager" class="%greeting_card_manager.class%">
                <call method="setMailer">
                     <argument type="service" id="my_mailer" />
                </call>
                <call method="setEmailFormatter">
                     <argument type="service" id="my_email_formatter" />
                </call>
            </service>
        </services>

    .. code-block:: php

        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\DependencyInjection\Reference;

        // ...
        $container->setParameter('newsletter_manager.class', 'NewsletterManager');
        $container->setParameter('greeting_card_manager.class', 'GreetingCardManager');

        $container->setDefinition('my_mailer', ...);
        $container->setDefinition('my_email_formatter', ...);
        $container->setDefinition('newsletter_manager', new Definition(
            '%newsletter_manager.class%'
        ))->addMethodCall('setMailer', array(
            new Reference('my_mailer')
        ))->addMethodCall('setEmailFormatter', array(
            new Reference('my_email_formatter')
        ));
        $container->setDefinition('greeting_card_manager', new Definition(
            '%greeting_card_manager.class%'
        ))->addMethodCall('setMailer', array(
            new Reference('my_mailer')
        ))->addMethodCall('setEmailFormatter', array(
            new Reference('my_email_formatter')
        ));

There is a lot of repetition in both the classes and the configuration. This
means that if you changed, for example, the ``Mailer`` of ``EmailFormatter``
classes to be injected via the constructor, you would need to update the config
in two places. Likewise if you needed to make changes to the setter methods
you would need to do this in both classes. The typical way to deal with the
common methods of these related classes would be to extract them to a super class::

    abstract class MailManager
    {
        protected $mailer;
        protected $emailFormatter;

        public function setMailer(Mailer $mailer)
        {
            $this->mailer = $mailer;
        }

        public function setEmailFormatter(EmailFormatter $emailFormatter)
        {
            $this->emailFormatter = $emailFormatter;
        }

        // ...
    }

The ``NewsletterManager`` and ``GreetingCardManager`` can then extend this
super class::

    class NewsletterManager extends MailManager
    {
        // ...
    }

and::

    class GreetingCardManager extends MailManager
    {
        // ...
    }

In a similar fashion, the Symfony2 service container also supports extending
services in the configuration so you can also reduce the repetition by specifying
a parent for a service.

.. configuration-block::

    .. code-block:: yaml

        parameters:
            # ...
            newsletter_manager.class: NewsletterManager
            greeting_card_manager.class: GreetingCardManager
        services:
            my_mailer:
                # ...
            my_email_formatter:
                # ...
            mail_manager:
                abstract:  true
                calls:
                    - [setMailer, ["@my_mailer"]]
                    - [setEmailFormatter, ["@my_email_formatter"]]

            newsletter_manager:
                class:     "%newsletter_manager.class%"
                parent: mail_manager

            greeting_card_manager:
                class:     "%greeting_card_manager.class%"
                parent: mail_manager

    .. code-block:: xml

        <parameters>
            <!-- ... -->
            <parameter key="newsletter_manager.class">NewsletterManager</parameter>
            <parameter key="greeting_card_manager.class">GreetingCardManager</parameter>
        </parameters>

        <services>
            <service id="my_mailer" ...>
              <!-- ... -->
            </service>
            <service id="my_email_formatter" ...>
              <!-- ... -->
            </service>
            <service id="mail_manager" abstract="true">
                <call method="setMailer">
                     <argument type="service" id="my_mailer" />
                </call>
                <call method="setEmailFormatter">
                     <argument type="service" id="my_email_formatter" />
                </call>
            </service>
            <service id="newsletter_manager" class="%newsletter_manager.class%" parent="mail_manager"/>
            <service id="greeting_card_manager" class="%greeting_card_manager.class%" parent="mail_manager"/>
        </services>

    .. code-block:: php

        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\DependencyInjection\DefinitionDecorator;
        use Symfony\Component\DependencyInjection\Reference;

        // ...
        $container->setParameter('newsletter_manager.class', 'NewsletterManager');
        $container->setParameter('greeting_card_manager.class', 'GreetingCardManager');

        $container->setDefinition('my_mailer', ...);
        $container->setDefinition('my_email_formatter', ...);
        $container->setDefinition('mail_manager', new Definition(
        ))->setAbstract(
            true
        )->addMethodCall('setMailer', array(
            new Reference('my_mailer')
        ))->addMethodCall('setEmailFormatter', array(
            new Reference('my_email_formatter')
        ));
        $container->setDefinition('newsletter_manager', new DefinitionDecorator(
            'mail_manager'
        ))->setClass(
            '%newsletter_manager.class%'
        );
        $container->setDefinition('greeting_card_manager', new DefinitionDecorator(
            'mail_manager'
        ))->setClass(
            '%greeting_card_manager.class%'
        );

In this context, having a ``parent`` service implies that the arguments and
method calls of the parent service should be used for the child services.
Specifically, the setter methods defined for the parent service will be called
when the child services are instantiated.

.. note::

   If you remove the ``parent`` config key, the services will still be instantiated
   and they will still of course extend the ``MailManager`` class. The difference
   is that omitting the ``parent`` config key will mean that the ``calls``
   defined on the ``mail_manager`` service will not be executed when the
   child services are instantiated.

.. caution::

   The ``scope``, ``abstract`` and ``tags`` attributes are always taken from
   the child service.

The parent service is abstract as it should not be directly retrieved from the
container or passed into another service. It exists merely as a "template" that
other services can use. This is why it can have no ``class`` configured which
would cause an exception to be raised for a non-abstract service.

.. note::

   In order for parent dependencies to resolve, the ``ContainerBuilder`` must
   first be compiled. See :doc:`/components/dependency_injection/compilation`
   for more details.

Overriding Parent Dependencies
------------------------------

There may be times where you want to override what class is passed in for
a dependency of one child service only. Fortunately, by adding the method
call config for the child service, the dependencies set by the parent class
will be overridden. So if you needed to pass a different dependency just
to the ``NewsletterManager`` class, the config would look like this:

.. configuration-block::

    .. code-block:: yaml

        parameters:
            # ...
            newsletter_manager.class: NewsletterManager
            greeting_card_manager.class: GreetingCardManager
        services:
            my_mailer:
                # ...
            my_alternative_mailer:
                # ...
            my_email_formatter:
                # ...
            mail_manager:
                abstract:  true
                calls:
                    - [setMailer, ["@my_mailer"]]
                    - [setEmailFormatter, ["@my_email_formatter"]]

            newsletter_manager:
                class:     "%newsletter_manager.class%"
                parent: mail_manager
                calls:
                    - [setMailer, ["@my_alternative_mailer"]]

            greeting_card_manager:
                class:     "%greeting_card_manager.class%"
                parent: mail_manager

    .. code-block:: xml

        <parameters>
            <!-- ... -->
            <parameter key="newsletter_manager.class">NewsletterManager</parameter>
            <parameter key="greeting_card_manager.class">GreetingCardManager</parameter>
        </parameters>

        <services>
            <service id="my_mailer" ...>
              <!-- ... -->
            </service>
            <service id="my_alternative_mailer" ...>
              <!-- ... -->
            </service>
            <service id="my_email_formatter" ...>
              <!-- ... -->
            </service>
            <service id="mail_manager" abstract="true">
                <call method="setMailer">
                     <argument type="service" id="my_mailer" />
                </call>
                <call method="setEmailFormatter">
                     <argument type="service" id="my_email_formatter" />
                </call>
            </service>
            <service id="newsletter_manager" class="%newsletter_manager.class%" parent="mail_manager">
                 <call method="setMailer">
                     <argument type="service" id="my_alternative_mailer" />
                </call>
            </service>
            <service id="greeting_card_manager" class="%greeting_card_manager.class%" parent="mail_manager"/>
        </services>

    .. code-block:: php

        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\DependencyInjection\DefinitionDecorator;
        use Symfony\Component\DependencyInjection\Reference;

        // ...
        $container->setParameter('newsletter_manager.class', 'NewsletterManager');
        $container->setParameter('greeting_card_manager.class', 'GreetingCardManager');

        $container->setDefinition('my_mailer', ...);
        $container->setDefinition('my_alternative_mailer', ...);
        $container->setDefinition('my_email_formatter', ...);
        $container->setDefinition('mail_manager', new Definition(
        ))->setAbstract(
            true
        )->addMethodCall('setMailer', array(
            new Reference('my_mailer')
        ))->addMethodCall('setEmailFormatter', array(
            new Reference('my_email_formatter')
        ));
        $container->setDefinition('newsletter_manager', new DefinitionDecorator(
            'mail_manager'
        ))->setClass(
            '%newsletter_manager.class%'
        )->addMethodCall('setMailer', array(
            new Reference('my_alternative_mailer')
        ));
        $container->setDefinition('greeting_card_manager', new DefinitionDecorator(
            'mail_manager'
        ))->setClass(
            '%greeting_card_manager.class%'
        );

The ``GreetingCardManager`` will receive the same dependencies as before,
but the ``NewsletterManager`` will be passed the ``my_alternative_mailer``
instead of the ``my_mailer`` service.

Collections of Dependencies
---------------------------

It should be noted that the overridden setter method in the previous example
is actually called twice - once per the parent definition and once per the
child definition. In the previous example, that was fine, since the second
``setMailer`` call replaces mailer object set by the first call.

In some cases, however, this can be a problem. For example, if the overridden
method call involves adding something to a collection, then two objects will
be added to that collection. The following shows such a case, if the parent
class looks like this::

    abstract class MailManager
    {
        protected $filters;

        public function setFilter($filter)
        {
            $this->filters[] = $filter;
        }

        // ...
    }

If you had the following config:

.. configuration-block::

    .. code-block:: yaml

        parameters:
            # ...
            newsletter_manager.class: NewsletterManager
        services:
            my_filter:
                # ...
            another_filter:
                # ...
            mail_manager:
                abstract:  true
                calls:
                    - [setFilter, ["@my_filter"]]

            newsletter_manager:
                class:     "%newsletter_manager.class%"
                parent: mail_manager
                calls:
                    - [setFilter, ["@another_filter"]]

    .. code-block:: xml

        <parameters>
            <!-- ... -->
            <parameter key="newsletter_manager.class">NewsletterManager</parameter>
        </parameters>

        <services>
            <service id="my_filter" ...>
              <!-- ... -->
            </service>
            <service id="another_filter" ...>
              <!-- ... -->
            </service>
            <service id="mail_manager" abstract="true">
                <call method="setFilter">
                     <argument type="service" id="my_filter" />
                </call>
            </service>
            <service id="newsletter_manager" class="%newsletter_manager.class%" parent="mail_manager">
                 <call method="setFilter">
                     <argument type="service" id="another_filter" />
                </call>
            </service>
        </services>

    .. code-block:: php

        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\DependencyInjection\DefinitionDecorator;
        use Symfony\Component\DependencyInjection\Reference;

        // ...
        $container->setParameter('newsletter_manager.class', 'NewsletterManager');
        $container->setParameter('mail_manager.class', 'MailManager');

        $container->setDefinition('my_filter', ...);
        $container->setDefinition('another_filter', ...);
        $container->setDefinition('mail_manager', new Definition(
        ))->setAbstract(
            true
        )->addMethodCall('setFilter', array(
            new Reference('my_filter')
        ));
        $container->setDefinition('newsletter_manager', new DefinitionDecorator(
            'mail_manager'
        ))->setClass(
            '%newsletter_manager.class%'
        )->addMethodCall('setFilter', array(
            new Reference('another_filter')
        ));

In this example, the ``setFilter`` of the ``newsletter_manager`` service
will be called twice, resulting in the ``$filters`` array containing both
``my_filter`` and ``another_filter`` objects. This is great if you just want
to add additional filters to the subclasses. If you want to replace the filters
passed to the subclass, removing the parent setting from the config will
prevent the base class from calling ``setFilter``.

.. tip::

    In the examples shown there is a similar relationship between the parent
    and child services and the underlying parent and child classes. This does
    not need to be the case though, you can extract common parts of similar
    service definitions into a parent service without also inheriting a parent
    class.
