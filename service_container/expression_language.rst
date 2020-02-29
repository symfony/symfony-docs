.. index::
    single: DependencyInjection; ExpressionLanguage
    single: DependencyInjection; Expressions
    single: Service Container; ExpressionLanguage
    single: Service Container; Expressions

How to Inject Values Based on Complex Expressions
=================================================

The service container also supports an "expression" that allows you to inject
very specific values into a service.

For example, suppose you have a service (not shown here), called ``App\Mail\MailerConfiguration``,
which has a ``getMailerMethod()`` method on it. This returns a string - like ``sendmail``
based on some configuration.

Suppose that you want to pass the result of this method as a constructor argument
to another service: ``App\Mailer``. One way to do this is with an expression:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            App\Mail\MailerConfiguration: ~

            App\Mailer:
                # the '@=' prefix is required when using expressions for arguments in YAML files
                arguments: ['@=service("App\\Mail\\MailerConfiguration").getMailerMethod()']
                # when using double-quoted strings, the backslash needs to be escaped twice (see https://yaml.org/spec/1.2/spec.html#id2787109)
                # arguments: ["@=service('App\\\\Mail\\\\MailerConfiguration').getMailerMethod()"]

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... -->

                <service id="App\Mail\MailerConfiguration"></service>

                <service id="App\Mailer">
                    <argument type="expression">service('App\\Mail\\MailerConfiguration').getMailerMethod()</argument>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Mail\MailerConfiguration;
        use App\Mailer;

        return function(ContainerConfigurator $configurator) {
            // ...

            $services->set(MailerConfiguration::class);

            $services->set(Mailer::class)
                ->args([expr(sprintf('service("%s").getMailerMethod()', MailerConfiguration::class))]);
        };


To learn more about the expression language syntax, see :doc:`/components/expression_language/syntax`.

In this context, you have access to 2 functions:

``service``
    Returns a given service (see the example above).
``parameter``
    Returns a specific parameter value (syntax is just like ``service``).

You also have access to the :class:`Symfony\\Component\\DependencyInjection\\Container`
via a ``container`` variable. Here's another example:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Mailer:
                # the '@=' prefix is required when using expressions for arguments in YAML files
                arguments: ["@=container.hasParameter('some_param') ? parameter('some_param') : 'default_value'"]

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Mailer">
                    <argument type="expression">container.hasParameter('some_param') ? parameter('some_param') : 'default_value'</argument>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Mailer;

        return function(ContainerConfigurator $configurator) {
            $services = $configurator->services();

            $services->set(Mailer::class)
                ->args([expr("container.hasParameter('some_param') ? parameter('some_param') : 'default_value'")]);
        };

Expressions can be used in ``arguments``, ``properties``, as arguments with
``configurator`` and as arguments to ``calls`` (method calls).
