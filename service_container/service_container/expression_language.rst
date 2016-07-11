.. index::
    single: DependencyInjection; ExpressionLanguage
    single: DependencyInjection; Expressions
    single: Service Container; ExpressionLanguage
    single: Service Container; Expressions

How to Inject Values Based on Complex Expressions
=================================================

The service container also supports an "expression" that allows you to inject
very specific values into a service.

For example, suppose you have a third service (not shown here), called ``mailer_configuration``,
which has a ``getMailerMethod()`` method on it, which will return a string
like ``sendmail`` based on some configuration. Remember that the first argument
to the ``my_mailer`` service is the simple string ``sendmail``:

.. include:: includes/_service_container_my_mailer.rst.inc

But instead of hardcoding this, how could we get this value from the ``getMailerMethod()``
of the new ``mailer_configuration`` service? One way is to use an expression:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        services:
            my_mailer:
                class:        AppBundle\Mailer
                arguments:    ["@=service('mailer_configuration').getMailerMethod()"]

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd"
            >

            <services>
                <service id="my_mailer" class="AppBundle\Mailer">
                    <argument type="expression">service('mailer_configuration').getMailerMethod()</argument>
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/config.php
        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\ExpressionLanguage\Expression;

        $container->setDefinition('my_mailer', new Definition(
            'AppBundle\Mailer',
            array(new Expression('service("mailer_configuration").getMailerMethod()'))
        ));

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
            my_mailer:
                class:     AppBundle\Mailer
                arguments: ["@=container.hasParameter('some_param') ? parameter('some_param') : 'default_value'"]

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd"
            >

            <services>
                <service id="my_mailer" class="AppBundle\Mailer">
                    <argument type="expression">container.hasParameter('some_param') ? parameter('some_param') : 'default_value'</argument>
                </service>
            </services>
        </container>

    .. code-block:: php

        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\ExpressionLanguage\Expression;

        $container->setDefinition('my_mailer', new Definition(
            'AppBundle\Mailer',
            array(new Expression(
                "container.hasParameter('some_param') ? parameter('some_param') : 'default_value'"
            ))
        ));

Expressions can be used in ``arguments``, ``properties``, as arguments with
``configurator`` and as arguments to ``calls`` (method calls).
