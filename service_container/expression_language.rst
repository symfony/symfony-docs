.. index::
    single: DependencyInjection; ExpressionLanguage
    single: DependencyInjection; Expressions
    single: Service Container; ExpressionLanguage
    single: Service Container; Expressions

How to Inject Values Based on Complex Expressions
=================================================

The service container also supports an "expression" that allows you to inject
very specific values into a service.

For example, suppose you have a service (not shown here), called ``AppBundle\Mail\MailerConfiguration``,
which has a ``getMailerMethod()`` method on it. This returns a string - like ``sendmail``
based on some configuration.

Suppose that you want to pass the result of this method as a constructor argument
to another service: ``AppBundle\Mailer``. One way to do this is with an expression:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        services:
            # ...
            
            AppBundle\Mail\MailerConfiguration: ~
            
            AppBundle\Mailer:
                arguments: ["@=service('AppBundle\Mail\MailerConfiguration').getMailerMethod()"]

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... -->

                <service id="AppBundle\Mail\MailerConfiguration"></service>

                <service id="AppBundle\Mailer">
                    <argument type="expression">service('AppBundle\Mail\MailerConfiguration').getMailerMethod()</argument>
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/config.php
        use AppBundle\Mail\MailerConfiguration;
        use AppBundle\Mailer;
        use Symfony\Component\ExpressionLanguage\Expression;
        
        $container->autowire(AppBundle\Mail\MailerConfiguration::class);

        $container->autowire(Mailer::class)
            ->addArgument(new Expression('service("AppBundle\Mail\MailerConfiguration").getMailerMethod()'));

To learn more about the expression language syntax, see :doc:`/components/expression_language/syntax`.

In this context, you have access to 2 functions:

``service``
    Returns a given service (see the example above).
``parameter``
    Returns a specific parameter value (syntax is just like ``service``).

You also have access to the :class:`Symfony\\Component\\DependencyInjection\\ContainerBuilder`
via a ``container`` variable. Here's another example:

.. configuration-block::

    .. code-block:: yaml

        services:
            AppBundle\Mailer:
                arguments: ["@=container.hasParameter('some_param') ? parameter('some_param') : 'default_value'"]

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="AppBundle\Mailer">
                    <argument type="expression">container.hasParameter('some_param') ? parameter('some_param') : 'default_value'</argument>
                </service>
            </services>
        </container>

    .. code-block:: php

        use AppBundle\Mailer;
        use Symfony\Component\ExpressionLanguage\Expression;

        $container->autowire(Mailer::class)
            ->addArgument(new Expression(
                "container.hasParameter('some_param') ? parameter('some_param') : 'default_value'"
            ));

Expressions can be used in ``arguments``, ``properties``, as arguments with
``configurator`` and as arguments to ``calls`` (method calls).
