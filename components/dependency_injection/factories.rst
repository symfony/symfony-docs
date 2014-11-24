.. index::
   single: DependencyInjection; Factories

Using a Factory to Create Services
==================================

Symfony's Service Container provides a powerful way of controlling the
creation of objects, allowing you to specify arguments passed to the constructor
as well as calling methods and setting parameters. Sometimes, however, this
will not provide you with everything you need to construct your objects.
For this situation, you can use a factory to create the object and tell the
service container to call a method on the factory rather than directly instantiating
the class.

Suppose you have a factory that configures and returns a new ``NewsletterManager``
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

        services:
            newsletter_manager:
                class:          NewsletterManager
                factory_class:  NewsletterFactory
                factory_method: get

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service
                    id="newsletter_manager"
                    class="NewsletterManager"
                    factory-class="NewsletterFactory"
                    factory-method="get" />
            </services>
        </services>

    .. code-block:: php

        use Symfony\Component\DependencyInjection\Definition;

        $definition = new Definition('NewsletterManager');
        $definition->setFactoryClass('NewsletterFactory');
        $definition->setFactoryMethod('get');

        $container->setDefinition('newsletter_manager', $definition);

When you specify the class to use for the factory (via ``factory_class``)
the method will be called statically. If the factory itself should be instantiated
and the resulting object's method called, configure the factory itself as a service.
In this case, the method (e.g. get) should be changed to be non-static:

.. configuration-block::

    .. code-block:: yaml

        services:
            newsletter_factory:
                class:            NewsletterFactory
            newsletter_manager:
                class:            NewsletterManager
                factory_service:  newsletter_factory
                factory_method:   get

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="newsletter_factory" class="NewsletterFactory"/>

                <service
                    id="newsletter_manager"
                    class="NewsletterManager"
                    factory-service="newsletter_factory"
                    factory-method="get" />
            </services>
        </container>

    .. code-block:: php

        use Symfony\Component\DependencyInjection\Definition;

        $container->setDefinition('newsletter_factory', new Definition(
            'NewsletterFactory'
        ));
        $container->setDefinition('newsletter_manager', new Definition(
            'NewsletterManager'
        ))->setFactoryService(
            'newsletter_factory'
        )->setFactoryMethod(
            'get'
        );

.. note::

   The factory service is specified by its id name and not a reference to
   the service itself. So, you do not need to use the @ syntax for this in
   YAML configurations.

Passing Arguments to the Factory Method
---------------------------------------

If you need to pass arguments to the factory method, you can use the ``arguments``
options inside the service container. For example, suppose the ``get`` method
in the previous example takes the ``templating`` service as an argument:

.. configuration-block::

    .. code-block:: yaml

        services:
            newsletter_factory:
                class:            NewsletterFactory
            newsletter_manager:
                class:            NewsletterManager
                factory_service:  newsletter_factory
                factory_method:   get
                arguments:
                    - "@templating"

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="newsletter_factory" class="NewsletterFactory"/>

                <service
                    id="newsletter_manager"
                    class="NewsletterManager"
                    factory-service="newsletter_factory"
                    factory-method="get">

                    <argument type="service" id="templating" />
                </service>
            </services>
        </container>

    .. code-block:: php

        use Symfony\Component\DependencyInjection\Definition;

        $container->setDefinition('newsletter_factory', new Definition(
            'NewsletterFactory'
        ));
        $container->setDefinition('newsletter_manager', new Definition(
            'NewsletterManager',
            array(new Reference('templating'))
        ))->setFactoryService(
            'newsletter_factory'
        )->setFactoryMethod(
            'get'
        );
