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
            app.newsletter_manager:
                class:   AppBundle\Email\NewsletterManager
                # call a static method
                factory: ['AppBundle\Email\NewsletterManager', create]

            app.newsletter_manager_factory:
                class: AppBundle\Email\NewsletterManagerFactory

            app.newsletter_manager:
                class:   AppBundle\Email\NewsletterManager
                # call a method on the specified service
                factory: 'app.newsletter_manager_factory:createNewsletterManager'

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="app.newsletter_manager" class="AppBundle\Email\NewsletterManager">
                    <!-- call a static method -->
                    <factory class="AppBundle\Email\NewsletterManager" method="create" />
                </service>

                <service id="app.newsletter_manager_factory"
                    class="AppBundle\Email\NewsletterManagerFactory"
                />

                <service id="app.newsletter_manager" class="AppBundle\Email\NewsletterManager">
                    <!-- call a method on the specified service -->
                    <factory service="app.newsletter_manager_factory"
                        method="createNewsletterManager"
                    />
                </service>
            </services>
        </container>

    .. code-block:: php

        use AppBundle\Email\NewsletterManager;
        use AppBundle\Email\NewsletterManagerFactory;
        use Symfony\Component\DependencyInjection\Definition;
        // ...

        $definition = new Definition(NewsletterManager::class);
        // call a static method
        $definition->setFactory(array(NewsletterManager::class, 'create'));

        $container->setDefinition('app.newsletter_manager', $definition);

        $container->register('app.newsletter_manager_factory', NewsletterManagerFactory::class);

        $newsletterManager = new Definition(NewsletterManager::class);

        // call a method on the specified service
        $newsletterManager->setFactory(array(
            new Reference('app.newsletter_manager_factory'),
            'createNewsletterManager'
        ));

        $container->setDefinition('app.newsletter_manager', $newsletterManager);

.. note::

    When using a factory to create services, the value chosen for the ``class``
    option has no effect on the resulting service. The actual class name
    only depends on the object that is returned by the factory. However,
    the configured class name may be used by compiler passes and therefore
    should be set to a sensible value.

.. note::

    The traditional configuration syntax in YAML files used an array to define
    the factory service and the method name:

    .. code-block:: yaml

        app.newsletter_manager:
            # new syntax
            factory: 'app.newsletter_manager_factory:createNewsletterManager'
            # old syntax
            factory: ['@app.newsletter_manager_factory', createNewsletterManager]

Passing Arguments to the Factory Method
---------------------------------------

If you need to pass arguments to the factory method, you can use the ``arguments``
options inside the service container. For example, suppose the ``createNewsletterManager()``
method in the previous example takes the ``templating`` service as an argument:

.. configuration-block::

    .. code-block:: yaml

        services:
            # ...

            app.newsletter_manager:
                class:     AppBundle\Email\NewsletterManager
                factory:   'newsletter_manager_factory:createNewsletterManager'
                arguments: ['@templating']

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... -->

                <service id="app.newsletter_manager" class="AppBundle\Email\NewsletterManager">
                    <factory service="app.newsletter_manager_factory" method="createNewsletterManager"/>
                    <argument type="service" id="templating"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        use AppBundle\Email\NewsletterManager;
        use Symfony\Component\DependencyInjection\Reference;
        use Symfony\Component\DependencyInjection\Definition;

        // ...
        $newsletterManager = new Definition(NewsletterManager::class, array(
            new Reference('templating')
        ));
        $newsletterManager->setFactory(array(
            new Reference('app.newsletter_manager_factory'),
            'createNewsletterManager'
        ));
        $container->setDefinition('app.newsletter_manager', $newsletterManager);
