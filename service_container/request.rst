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

Now, just inject the ``request_stack``, which behaves like any normal service.
If you're using the :ref:`default services.yml configuration <service-container-services-load-example>`,
this will happen automatically via autowiring.

.. tip::

    In a controller you can get the ``Request`` object by having it passed in as an
    argument to your action method. See :ref:`controller-request-argument` for
    details.
