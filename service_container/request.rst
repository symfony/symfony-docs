.. index::
    single: DependencyInjection; Request
    single: Service Container; Request

How to Retrieve the Request from the Service Container
======================================================

Whenever you need to access the current request in a service, you can either
add it as an argument to the methods that need the request or inject the
``request_stack`` service and access the ``Request`` by calling the
:method:`Symfony\\Component\\HttpFoundation\\RequestStack::getCurrentRequest`
method::

    namespace AppBundle\Newsletter;

    use Symfony\Component\HttpFoundation\RequestStack;

    class NewsletterManager
    {
        protected $requestStack;

        public function __construct(RequestStack $requestStack)
        {
            $this->requestStack = $requestStack;
        }

        public function anyMethod()
        {
            $request = $this->requestStack->getCurrentRequest();
            // ... do something with the request
        }

        // ...
    }

Now, just inject the ``request_stack``, which behaves like any normal service:

.. configuration-block::

    .. code-block:: yaml

        # src/AppBundle/Resources/config/services.yml
        services:
            newsletter_manager:
                class:     AppBundle\Newsletter\NewsletterManager
                arguments: ["@request_stack"]

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service
                    id="newsletter_manager"
                    class="AppBundle\Newsletter\NewsletterManager"
                >
                    <argument type="service" id="request_stack"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // src/AppBundle/Resources/config/services.php
        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\DependencyInjection\Reference;

        // ...
        $container->setDefinition('newsletter_manager', new Definition(
            'AppBundle\Newsletter\NewsletterManager',
            array(new Reference('request_stack'))
        ));

.. tip::

    If you define a controller as a service then you can get the ``Request``
    object without injecting the container by having it passed in as an
    argument of your action method. See
    :ref:`book-controller-request-argument` for details.
