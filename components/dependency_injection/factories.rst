.. index::
   single: Dependency Injection; Factories

Using a Factory to Create Services
==================================

Symfony2's Service Container provides a powerful way of controlling the
creation of objects, allowing you to specify arguments passed to the constructor
as well as calling methods and setting parameters. Sometimes, however, this
will not provide you with everything you need to construct your objects.
For this situation, you can use a factory to create the object and tell the
service container to call a method on the factory rather than directly instantiating
the object.

Suppose you have a factory that configures and returns a new NewsletterManager
object::

    class NewsletterFactory
    {
        public function get()
        {
            $newsletterManager = new NewsletterManager();

            // ...

            return $newsletterManager;
        }
    }

To make the ``NewsletterManager`` object available as a service, you can
configure the service container to use the ``NewsletterFactory`` factory
class:

.. configuration-block::

    .. code-block:: yaml

        parameters:
            # ...
            newsletter_manager.class: NewsletterManager
            newsletter_factory.class: NewsletterFactory
        services:
            newsletter_manager:
                class:          "%newsletter_manager.class%"
                factory_class:  "%newsletter_factory.class%"
                factory_method: get

    .. code-block:: xml

        <parameters>
            <!-- ... -->
            <parameter key="newsletter_manager.class">NewsletterManager</parameter>
            <parameter key="newsletter_factory.class">NewsletterFactory</parameter>
        </parameters>

        <services>
            <service id="newsletter_manager"
                     class="%newsletter_manager.class%"
                     factory-class="%newsletter_factory.class%"
                     factory-method="get"
            />
        </services>

    .. code-block:: php

        use Symfony\Component\DependencyInjection\Definition;

        // ...
        $container->setParameter('newsletter_manager.class', 'NewsletterManager');
        $container->setParameter('newsletter_factory.class', 'NewsletterFactory');

        $container->setDefinition('newsletter_manager', new Definition(
            '%newsletter_manager.class%'
        ))->setFactoryClass(
            '%newsletter_factory.class%'
        )->setFactoryMethod(
            'get'
        );

When you specify the class to use for the factory (via ``factory_class``)
the method will be called statically. If the factory itself should be instantiated
and the resulting object's method called (as in this example), configure the
factory itself as a service:

.. configuration-block::

    .. code-block:: yaml

        parameters:
            # ...
            newsletter_manager.class: NewsletterManager
            newsletter_factory.class: NewsletterFactory
        services:
            newsletter_factory:
                class:            "%newsletter_factory.class%"
            newsletter_manager:
                class:            "%newsletter_manager.class%"
                factory_service:  newsletter_factory
                factory_method:   get

    .. code-block:: xml

        <parameters>
            <!-- ... -->
            <parameter key="newsletter_manager.class">NewsletterManager</parameter>
            <parameter key="newsletter_factory.class">NewsletterFactory</parameter>
        </parameters>

        <services>
            <service id="newsletter_factory" class="%newsletter_factory.class%"/>
            <service id="newsletter_manager"
                     class="%newsletter_manager.class%"
                     factory-service="newsletter_factory"
                     factory-method="get"
            />
        </services>

    .. code-block:: php

        use Symfony\Component\DependencyInjection\Definition;

        // ...
        $container->setParameter('newsletter_manager.class', 'NewsletterManager');
        $container->setParameter('newsletter_factory.class', 'NewsletterFactory');

        $container->setDefinition('newsletter_factory', new Definition(
            '%newsletter_factory.class%'
        ))
        $container->setDefinition('newsletter_manager', new Definition(
            '%newsletter_manager.class%'
        ))->setFactoryService(
            'newsletter_factory'
        )->setFactoryMethod(
            'get'
        );

.. note::

   The factory service is specified by its id name and not a reference to
   the service itself. So, you do not need to use the @ syntax.

Passing Arguments to the Factory Method
---------------------------------------

If you need to pass arguments to the factory method, you can use the ``arguments``
options inside the service container. For example, suppose the ``get`` method
in the previous example takes the ``templating`` service as an argument:

.. configuration-block::

    .. code-block:: yaml

        parameters:
            # ...
            newsletter_manager.class: NewsletterManager
            newsletter_factory.class: NewsletterFactory
        services:
            newsletter_factory:
                class:            "%newsletter_factory.class%"
            newsletter_manager:
                class:            "%newsletter_manager.class%"
                factory_service:  newsletter_factory
                factory_method:   get
                arguments:
                    - "@templating"

    .. code-block:: xml

        <parameters>
            <!-- ... -->
            <parameter key="newsletter_manager.class">NewsletterManager</parameter>
            <parameter key="newsletter_factory.class">NewsletterFactory</parameter>
        </parameters>

        <services>
            <service id="newsletter_factory" class="%newsletter_factory.class%"/>
            <service id="newsletter_manager"
                     class="%newsletter_manager.class%"
                     factory-service="newsletter_factory"
                     factory-method="get"
            >
                <argument type="service" id="templating" />
            </service>
        </services>

    .. code-block:: php

        use Symfony\Component\DependencyInjection\Definition;

        // ...
        $container->setParameter('newsletter_manager.class', 'NewsletterManager');
        $container->setParameter('newsletter_factory.class', 'NewsletterFactory');

        $container->setDefinition('newsletter_factory', new Definition(
            '%newsletter_factory.class%'
        ))
        $container->setDefinition('newsletter_manager', new Definition(
            '%newsletter_manager.class%',
            array(new Reference('templating'))
        ))->setFactoryService(
            'newsletter_factory'
        )->setFactoryMethod(
            'get'
        );
