.. index::
   single: DependencyInjection; Factories

Using a Factory to Create Services
==================================

Symfony's Service Container provides a powerful way of controlling the
creation of objects, allowing you to specify arguments passed to the constructor
as well as calling methods and setting parameters. Sometimes, however, this
will not provide you with everything you need to construct your objects.
For this situation, you can use a factory to create the object and tell
the service container to call a method on the factory rather than directly
instantiating the class.

Suppose you have a factory that configures and returns a new ``NewsletterManager``
object::

    class NewsletterManagerFactory
    {
        public static function createNewsletterManager()
        {
            $newsletterManager = new NewsletterManager();

            // ...

            return $newsletterManager;
        }
    }

To make the ``NewsletterManager`` object available as a service, you can
configure the service container to use the
``NewsletterManagerFactory::createNewsletterManager()`` factory method:

.. configuration-block::

    .. code-block:: yaml

        services:
            newsletter_manager:
                class:   NewsletterManager
                factory: [NewsletterManagerFactory, createNewsletterManager]

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="newsletter_manager" class="NewsletterManager">
                    <factory class="NewsletterManagerFactory" method="createNewsletterManager" />
                </service>
            </services>
        </services>

    .. code-block:: php

        use Symfony\Component\DependencyInjection\Definition;

        // ...
        $definition = new Definition('NewsletterManager');
        $definition->setFactory(array('NewsletterManagerFactory', 'createNewsletterManager'));

        $container->setDefinition('newsletter_manager', $definition);

.. note::

    When using a factory to create services, the value chosen for the ``class``
    option has no effect on the resulting service. The actual class name
    only depends on the object that is returned by the factory. However,
    the configured class name may be used by compiler passes and therefore
    should be set to a sensible value.

Now, the method will be called statically. If the factory class itself should
be instantiated and the resulting object's method called, configure the factory
itself as a service. In this case, the method (e.g. get) should be changed to
be non-static.

.. configuration-block::

    .. code-block:: yaml

        services:
            newsletter_manager.factory:
                class: NewsletterManagerFactory
            newsletter_manager:
                class:   NewsletterManager
                factory: ["@newsletter_manager.factory", createNewsletterManager]

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="newsletter_manager.factory" class="NewsletterManagerFactory" />

                <service id="newsletter_manager" class="NewsletterManager">
                    <factory service="newsletter_manager.factory" method="createNewsletterManager" />
                </service>
            </services>
        </container>

    .. code-block:: php

        use Symfony\Component\DependencyInjection\Reference;
        use Symfony\Component\DependencyInjection\Definition;

        // ...
        $container->register('newsletter_manager.factory', 'NewsletterManagerFactory');

        $newsletterManager = new Definition();
        $newsletterManager->setFactory(array(
            new Reference('newsletter_manager.factory'),
            'createNewsletterManager'
        ));
        $container->setDefinition('newsletter_manager', $newsletterManager);

Passing Arguments to the Factory Method
---------------------------------------

If you need to pass arguments to the factory method, you can use the ``arguments``
options inside the service container. For example, suppose the ``createNewsletterManager``
method in the previous example takes the ``templating`` service as an argument:

.. configuration-block::

    .. code-block:: yaml

        services:
            newsletter_manager.factory:
                class: NewsletterManagerFactory

            newsletter_manager:
                class:   NewsletterManager
                factory: ["@newsletter_manager.factory", createNewsletterManager]
                arguments:
                    - '@templating'

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="newsletter_manager.factory" class="NewsletterManagerFactory"/>

                <service id="newsletter_manager" class="NewsletterManager">
                    <factory service="newsletter_manager.factory" method="createNewsletterManager"/>
                    <argument type="service" id="templating"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        use Symfony\Component\DependencyInjection\Reference;
        use Symfony\Component\DependencyInjection\Definition;

        // ...
        $container->register('newsletter_manager.factory', 'NewsletterManagerFactory');

        $newsletterManager = new Definition(
            'NewsletterManager',
            array(new Reference('templating'))
        );
        $newsletterManager->setFactory(array(
            new Reference('newsletter_manager.factory'),
            'createNewsletterManager'
        ));
        $container->setDefinition('newsletter_manager', $newsletterManager);
