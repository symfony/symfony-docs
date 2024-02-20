How to Retrieve the Request from the Service Container
======================================================

Whenever you need to access the current request in a service, you can either
add it as an argument to the methods that need the request or inject the
``request_stack`` service and access the ``Request`` by calling the
:method:`Symfony\\Component\\HttpFoundation\\RequestStack::getCurrentRequest`
method::

    // src/Newsletter/NewsletterManager.php
    namespace App\Newsletter;

    use Symfony\Component\HttpFoundation\RequestStack;

    class NewsletterManager
    {
        public function __construct(
            protected RequestStack $requestStack,
        ) {
        }

        public function anyMethod(): void
        {
            $request = $this->requestStack->getCurrentRequest();
            // ... do something with the request
        }

        // ...
    }

Now, inject the ``request_stack``, which behaves like any normal service.
If you're using the :ref:`default services.yaml configuration <service-container-services-load-example>`,
this will happen automatically via autowiring.

.. tip::

    In a controller you can get the ``Request`` object by having it passed in as an
    argument to your action method. See :ref:`controller-request-argument` for
    details.
