.. index::
    single: DependencyInjection; Request
    single: Service Container; Request

How to Retrieve the Request from the Service Container
======================================================

As of Symfony 2.4, instead of injecting the ``request`` service, you should
inject the ``request_stack`` service and access the ``Request`` by calling
the :method:`Symfony\\Component\\HttpFoundation\\RequestStack::getCurrentRequest`
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
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

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
        use AppBundle\Newsletter\NewsletterManager;
        use Symfony\Component\DependencyInjection\Reference;

        // ...
        $container->register('newsletter_manager', NewsletterManager::class)
            ->addArgument(new Reference('request_stack'));

.. sidebar:: Why not Inject the ``request`` Service?

    Almost all Symfony2 built-in services behave in the same way: a single
    instance is created by the container which it returns whenever you get it or
    when it is injected into another service. There is one exception in a standard
    Symfony2 application: the ``request`` service.

    If you try to inject the ``request`` into a service, you will probably receive
    a
    :class:`Symfony\\Component\\DependencyInjection\\Exception\\ScopeWideningInjectionException`
    exception. That's because the ``request`` can **change** during the life-time
    of a container (when a sub-request is created for instance).


.. tip::

    If you define a controller as a service then you can get the ``Request``
    object without injecting the container by having it passed in as an
    argument of your action method. See :ref:`controller-request-argument` for
    details.
