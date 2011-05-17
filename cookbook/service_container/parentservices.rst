How to Manage Common Dependencies with Parent Services
======================================================

As you add more functionality to your application you may well start to have
related classes that share some of the same dependencies. For example you may have a 
Newsletter Manager which uses setter injection to set its dependencies::

    namespace Acme\HelloBundle\Mail;

    use Acme\HelloBundle\Mailer;
    use Acme\HelloBundle\EmailFormatter;

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
            $this->mailer = $mailer;
        }
        // ...
    }

and also have Greeting Card class which shares the same dependencies::

    namespace Acme\HelloBundle\Mail;

    use Acme\HelloBundle\Mailer;
    use Acme\HelloBundle\EmailFormatter;

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
            $this->mailer = $mailer;
        }
        // ...
    }

The config for these classes would look something like this:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/HelloBundle/Resources/config/services.yml
        parameters:
            # ...
            newsletter_manager.class: Acme\HelloBundle\Mail\NewsletterManager
            greeting_card_manager.class: Acme\HelloBundle\Mail\GreetingCardManager
        services:
            my_mailer:
                # ...
            my_email_formatter:
                # ...
            newsletter_manager:
                class:     %newsletter_manager.class%
                calls:
                    - [ setMailer, [ @my_mailer ] ]
                    - [ setEmailFormatter, [ @my_email_formatter] ]

            greeting_card_manager:
                class:     %greeting_card_manager.class%
                calls:
                    - [ setMailer, [ @my_mailer ] ]
                    - [ setEmailFormatter, [ @my_email_formatter] ]

    .. code-block:: xml

        <!-- src/Acme/HelloBundle/Resources/config/services.xml -->
        <parameters>
            <!-- ... -->
            <parameter key="newsletter_manager.class">Acme\HelloBundle\Mail\NewsletterManager</parameter>
            <parameter key="greeting_card_manager.class">Acme\HelloBundle\Mail\GreetingCardManager</parameter>
        </parameters>

        <services>
            <service id="my_mailer" ... >
              <!-- ... -->
            </service>
            <service id="my_email_formatter" ... >
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

        // src/Acme/HelloBundle/Resources/config/services.php
        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\DependencyInjection\Reference;

        // ...
        $container->setParameter('newsletter_manager.class', 'Acme\HelloBundle\Mail\NewsletterManager');
        $container->setParameter('greeting_card_manager.class', 'Acme\HelloBundle\Mail\GreetingCardManager');

        $container->setDefinition('my_mailer', ... );
        $container->setDefinition('my_email_formatter', ... );
        $container->setDefinition('newsletter_manager', new Definition(
            new Reference('newsletter_manager.class')
        ))->addMethodCall('setMailer', array(
            new Reference('my_mailer')
        ))->addMethodCall('setEmailFormatter', array(
            new Reference('my_email_formatter')
        ));
        $container->setDefinition('greeting_card_manager', new Definition(
            new Reference('greeting_card_manager.class')
        ))->addMethodCall('setMailer', array(
            new Reference('my_mailer')
        ))->addMethodCall('setEmailFormatter', array(
            new Reference('my_email_formatter')
        ));

There is a lot of repetition in both the classes and the configuration. This means that if you
change the Mailer of EmailFormatter classes to be injected then you need to update the config
in two places now. Likewise if you needed to make changes to the setter methods you would need to 
do this for both classes. The typical way to deal with the common methods of these related classes would
be to extract them to a super class::

    namespace Acme\HelloBundle\Mail;

    use Acme\HelloBundle\Mailer;
    use Acme\HelloBundle\EmailFormatter;

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
            $this->mailer = $mailer;
        }
        // ...
    }

The NewsletterManager and GreetingCardManager can then extend this super class::

    namespace Acme\HelloBundle\Mail;

    class NewsletterManager extends MailManager
    {
        // ...
    }

and::

    namespace Acme\HelloBundle\Mail;

    class GreetingCardManager extends MailManager
    {
        // ...
    }

The Symfony2 service container also supports extending services in the configuration
so you can also reduce the repetition by specifying a parent for a service.

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/HelloBundle/Resources/config/services.yml
        parameters:
            # ...
            newsletter_manager.class: Acme\HelloBundle\Mail\NewsletterManager
            greeting_card_manager.class: Acme\HelloBundle\Mail\GreetingCardManager
            mail_manager.class: Acme\HelloBundle\Mail\MailManager
        services:
            my_mailer:
                # ...
            my_email_formatter:
                # ...
            mail_manager:
                class:     %mail_manager.class%
                abstract:  true
                calls:
                    - [ setMailer, [ @my_mailer ] ]
                    - [ setEmailFormatter, [ @my_email_formatter] ]
            
            newsletter_manager:
                class:     %newsletter_manager.class%
                parent: mail_manager
            
            greeting_card_manager:
                class:     %greeting_card_manager.class%
                parent: mail_manager
            
    .. code-block:: xml

        <!-- src/Acme/HelloBundle/Resources/config/services.xml -->
        <parameters>
            <!-- ... -->
            <parameter key="newsletter_manager.class">Acme\HelloBundle\Mail\NewsletterManager</parameter>
            <parameter key="greeting_card_manager.class">Acme\HelloBundle\Mail\GreetingCardManager</parameter>
            <parameter key="mail_manager.class">Acme\HelloBundle\Mail\MailManager</parameter>
        </parameters>

        <services>
            <service id="my_mailer" ... >
              <!-- ... -->
            </service>
            <service id="my_email_formatter" ... >
              <!-- ... -->
            </service>
            <service id="mail_manager" class="%mail_manager.class%" abstract="true">
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

        // src/Acme/HelloBundle/Resources/config/services.php
        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\DependencyInjection\Reference;

        // ...
        $container->setParameter('newsletter_manager.class', 'Acme\HelloBundle\Mail\NewsletterManager');
        $container->setParameter('greeting_card_manager.class', 'Acme\HelloBundle\Mail\GreetingCardManager');
        $container->setParameter('mail_manager.class', 'Acme\HelloBundle\Mail\MailManager');

        $container->setDefinition('my_mailer', ... );
        $container->setDefinition('my_email_formatter', ... );
        $container->setDefinition('mail_manager', new Definition(
            '%mail_manager.class%'
        ))->SetAbstract(
            true
        )->addMethodCall('setMailer', array(
            new Reference('my_mailer')
        ))->addMethodCall('setEmailFormatter', array(
            new Reference('my_email_formatter')
        ));
        $container->setDefinition('newsletter_manager', new DefinitionDecorator(
            'mail_manager'
        ))->setClass(
            new Reference('newsletter_manager.class')
        );
        $container->setDefinition('greeting_card_manager', new DefinitionDecorator(
            'mail_manager'
        ))->setClass(
            new Reference('greeting_card_manager.class')
        );

The setter methods defined for the parent service will be called so they only need to be configured
in one place.

.. note::

   The parent service must be specified in the config for its method calls to be made.
   It will not be called even though the service class extends the parent class.

The parent class is abstract as it should not be directly instantiated. Setting it to abstract 
in the config file as has been done above will mean that it can only be used as a parent service
and cannot be used directly as a service to inject and will be removed at compile time.

Overriding Parent Dependencies
------------------------------

There may be times where you want to override what class is passed in for a dependencies
for one of the child services only. Fortunately this is easily done, just adding the method 
call config for that service will override the dependency set by the parent class. So if you needed to
pass a different dependency just to the NewsletterManager the config would look like this:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/HelloBundle/Resources/config/services.yml
        parameters:
            # ...
            newsletter_manager.class: Acme\HelloBundle\Mail\NewsletterManager
            greeting_card_manager.class: Acme\HelloBundle\Mail\GreetingCardManager
            mail_manager.class: Acme\HelloBundle\Mail\MailManager
        services:
            my_mailer:
                # ...
            my_alternative_mailer:
                # ...
            my_email_formatter:
                # ...
            mail_manager:
                class:     %mail_manager.class%
                abstract:  true
                calls:
                    - [ setMailer, [ @my_mailer ] ]
                    - [ setEmailFormatter, [ @my_email_formatter] ]
            
            newsletter_manager:
                class:     %newsletter_manager.class%
                parent: mail_manager
                calls:
                    - [ setMailer, [ @my_alternative_mailer ] ]
            
            greeting_card_manager:
                class:     %greeting_card_manager.class%
                parent: mail_manager
            
    .. code-block:: xml

        <!-- src/Acme/HelloBundle/Resources/config/services.xml -->
        <parameters>
            <!-- ... -->
            <parameter key="newsletter_manager.class">Acme\HelloBundle\Mail\NewsletterManager</parameter>
            <parameter key="greeting_card_manager.class">Acme\HelloBundle\Mail\GreetingCardManager</parameter>
            <parameter key="mail_manager.class">Acme\HelloBundle\Mail\MailManager</parameter>
        </parameters>

        <services>
            <service id="my_mailer" ... >
              <!-- ... -->
            </service>
            <service id="my_alternative_mailer" ... >
              <!-- ... -->
            </service>
            <service id="my_email_formatter" ... >
              <!-- ... -->
            </service>
            <service id="mail_manager" class="%mail_manager.class%" abstract="true">
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

        // src/Acme/HelloBundle/Resources/config/services.php
        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\DependencyInjection\Reference;

        // ...
        $container->setParameter('newsletter_manager.class', 'Acme\HelloBundle\Mail\NewsletterManager');
        $container->setParameter('greeting_card_manager.class', 'Acme\HelloBundle\Mail\GreetingCardManager');
        $container->setParameter('mail_manager.class', 'Acme\HelloBundle\Mail\MailManager');

        $container->setDefinition('my_mailer', ... );
        $container->setDefinition('my_alternative_mailer', ... );
        $container->setDefinition('my_email_formatter', ... );
        $container->setDefinition('mail_manager', new Definition(
            '%mail_manager.class%'
        ))->SetAbstract(
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
        $container->setDefinition('newsletter_manager', new DefinitionDecorator(
            'mail_manager'
        ))->setClass(
            '%greeting_card_manager.class%'
        );

The GreetingCardManager will receive the same dependencies as before, the NewsletterManager will
receive the usual EmailFormatter but the alternative Mailer.

Collections of Dependencies
---------------------------

Worth knowing is that the setter method will be called for the parent service and again 
replacing the dependency for the child rather than the Service Container deciding not to call it for the 
base class. Whilst this does not matter in the above example due to the simplistic nature of 
the setter it would in some cases, for example, if the setter added the passed dependencies 
to a collection. The following shows such a case, if the the parent class looks like this::

    namespace Acme\HelloBundle\Mail;

    use Acme\HelloBundle\Mailer;
    use Acme\HelloBundle\EmailFormatter;

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

        # src/Acme/HelloBundle/Resources/config/services.yml
        parameters:
            # ...
            newsletter_manager.class: Acme\HelloBundle\Mail\NewsletterManager
            mail_manager.class: Acme\HelloBundle\Mail\MailManager
        services:
            my_filter:
                # ...
            another_filter:
                # ...
            mail_manager:
                class:     %mail_manager.class%
                abstract:  true
                calls:
                    - [ setFilter, [ @my_filter ] ]
                    
            newsletter_manager:
                class:     %newsletter_manager.class%
                parent: mail_manager
                calls:
                    - [ setFilter, [ @another_filter ] ]
            
    .. code-block:: xml

        <!-- src/Acme/HelloBundle/Resources/config/services.xml -->
        <parameters>
            <!-- ... -->
            <parameter key="newsletter_manager.class">Acme\HelloBundle\Mail\NewsletterManager</parameter>
            <parameter key="mail_manager.class">Acme\HelloBundle\Mail\MailManager</parameter>
        </parameters>

        <services>
            <service id="my_filter" ... >
              <!-- ... -->
            </service>
            <service id="another_filter" ... >
              <!-- ... -->
            </service>
            <service id="mail_manager" class="%mail_manager.class%" abstract="true">
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

        // src/Acme/HelloBundle/Resources/config/services.php
        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\DependencyInjection\Reference;

        // ...
        $container->setParameter('newsletter_manager.class', 'Acme\HelloBundle\Mail\NewsletterManager');
        $container->setParameter('mail_manager.class', 'Acme\HelloBundle\Mail\MailManager');

        $container->setDefinition('my_filter', ... );
        $container->setDefinition('another_filter', ... );
        $container->setDefinition('mail_manager', new Definition(
            '%mail_manager.class%'
        ))->SetAbstract(
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

This would lead to the $filters array containing both a my_filter and a another_filter object. This is 
great if you just want to add additional filters to the subclasses. If you want to replace the 
filters passed to the subclass then removing the parent setting from the config will 
prevent the base class call to setFilter.
