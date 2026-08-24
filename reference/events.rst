Built-in Symfony Events
=======================

The Symfony framework is an HTTP Request-Response one.
During the handling of an HTTP request, the framework (or any
application using the :doc:`HttpKernel component </components/http_kernel>`)
dispatches some :doc:`events </event_dispatcher>` which you can use to modify
how the request is handled and how the response is returned.

Kernel Events
-------------

Each event dispatched by the HttpKernel component is a subclass of
:class:`Symfony\\Component\\HttpKernel\\Event\\KernelEvent`, which provides the
following information:

:method:`Symfony\\Component\\HttpKernel\\Event\\KernelEvent::getRequestType`
    Returns the *type* of the request (``HttpKernelInterface::MAIN_REQUEST``
    or ``HttpKernelInterface::SUB_REQUEST``).

:method:`Symfony\\Component\\HttpKernel\\Event\\KernelEvent::getKernel`
    Returns the Kernel handling the request.

:method:`Symfony\\Component\\HttpKernel\\Event\\KernelEvent::getRequest`
    Returns the current ``Request`` being handled.

:method:`Symfony\\Component\\HttpKernel\\Event\\KernelEvent::isMainRequest`
    Checks if this is a main request.

.. _kernel-core-request:

``kernel.request``
~~~~~~~~~~~~~~~~~~

**Event Class**: :class:`Symfony\\Component\\HttpKernel\\Event\\RequestEvent`

This event is dispatched very early in Symfony, before the controller is
determined. It's useful to add information to the Request or return a Response
early to stop the handling of the request.

.. seealso::

    Read more on the :ref:`kernel.request event <component-http-kernel-kernel-request>`.

Execute this command to find out which listeners are registered for this event and
their priorities:

.. code-block:: terminal

    $ php bin/console debug:event-dispatcher kernel.request

``kernel.controller``
~~~~~~~~~~~~~~~~~~~~~

**Event Class**: :class:`Symfony\\Component\\HttpKernel\\Event\\ControllerEvent`

This event is dispatched after the controller has been resolved but before executing
it. It's useful to initialize things later needed by the
controller, such as :ref:`value resolvers <managing-value-resolvers>`, and
even to change the controller entirely::

    use Symfony\Component\HttpKernel\Event\ControllerEvent;

    public function onKernelController(ControllerEvent $event): void
    {
        // ...

        // the controller can be changed to any PHP callable
        $event->setController($myCustomController);
    }

You can also use ``getAttributes()`` to retrieve PHP attributes declared on the
controller:

.. code-block:: php-attributes

    use Symfony\Component\HttpKernel\Event\ControllerEvent;

    public function onKernelController(ControllerEvent $event): void
    {
        // get all attributes, grouped by class name
        $allAttributes = $event->getAttributes();

        // get all attributes as a flat list (not grouped)
        $flatAttributes = $event->getAttributes('*');

        // get attributes of a specific class
        $cacheAttributes = $event->getAttributes(Cache::class);
    }

Controller attributes are stored in the ``_controller_attributes`` request
attribute, which means they can be overridden at runtime to change the behavior
of attribute-based listeners without modifying the controller source code.

.. versionadded:: 8.1

    Storing controller attributes in the ``_controller_attributes`` request
    attribute was introduced in Symfony 8.1.

The :class:`Symfony\\Component\\HttpKernel\\Event\\ControllerEvent` class provides
the :method:`Symfony\\Component\\HttpKernel\\Event\\ControllerEvent::evaluate` method,
which standardizes how ``Expression`` and ``Closure`` values found in controller
attributes are evaluated. If the given value is a ``Closure``, it is called with
``($args, $request, $controller)`` as arguments. If it is an
:class:`Symfony\\Component\\ExpressionLanguage\\Expression`, it is evaluated with
the variables ``request``, ``args`` and ``this`` (the controller object). Any
other value is returned unchanged::

    use Symfony\Component\HttpKernel\Event\ControllerEvent;

    public function onKernelController(ControllerEvent $event): void
    {
        // evaluate a Closure defined in a controller attribute
        $result = $event->evaluate(
            static function (array $args, Request $request, object $controller): bool {
                return $request->attributes->get('_route') === 'admin';
            }
        );

        // evaluate an Expression defined in a controller attribute
        // available variables: "request", "args", and "this" (the controller)
        $result = $event->evaluate(new Expression('request.getMethod() == "POST"'));
    }

.. versionadded:: 8.1

    The :method:`Symfony\\Component\\HttpKernel\\Event\\ControllerEvent::evaluate`
    method was introduced in Symfony 8.1.

.. seealso::

    Read more on the :ref:`kernel.controller event <component-http-kernel-kernel-controller>`.

Execute this command to find out which listeners are registered for this event and
their priorities:

.. code-block:: terminal

    $ php bin/console debug:event-dispatcher kernel.controller

``kernel.controller_arguments``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**Event Class**: :class:`Symfony\\Component\\HttpKernel\\Event\\ControllerArgumentsEvent`

This event is dispatched just before a controller is called. It's useful to
configure the arguments that are going to be passed to the controller.
Typically, this is used to map URL routing parameters to their corresponding
named arguments; or pass the current request when the ``Request`` type-hint is
found::

    use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;

    public function onKernelControllerArguments(ControllerArgumentsEvent $event): void
    {
        // ...

        // get controller and request arguments
        $namedArguments = $event->getRequest()->attributes->all();
        $controllerArguments = $event->getArguments();

        // set the controller arguments to modify the original arguments or add new ones
        $event->setArguments($newArguments);
    }

The :class:`Symfony\\Component\\HttpKernel\\Event\\ControllerArgumentsEvent` also
provides the :method:`Symfony\\Component\\HttpKernel\\Event\\ControllerEvent::evaluate`
method (inherited from ``ControllerEvent``) to evaluate ``Expression`` and
``Closure`` values found in controller attributes.

.. versionadded:: 8.1

    The :method:`Symfony\\Component\\HttpKernel\\Event\\ControllerEvent::evaluate`
    method was introduced in Symfony 8.1.

Execute this command to find out which listeners are registered for this event and
their priorities:

.. code-block:: terminal

    $ php bin/console debug:event-dispatcher kernel.controller_arguments

.. _controller-metadata-in-events:

Accessing Controller Metadata in Events
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

All kernel events dispatched after controller resolution (``kernel.controller_arguments``,
``kernel.view``, ``kernel.response``, ``kernel.finish_request`` and
``kernel.exception``) provide access to the controller metadata through the
``controllerMetadata`` public property. This allows listeners to inspect the
controller's attributes and arguments without using reflection on the controller::

    use Symfony\Component\HttpKernel\Event\ResponseEvent;

    public function onKernelResponse(ResponseEvent $event): void
    {
        $metadata = $event->controllerMetadata;

        if (null === $metadata) {
            return;
        }

        // get the PHP attributes declared on the controller
        $attributes = $metadata->getAttributes();

        // if the event happens after controller_arguments, you also get
        // access to the resolved controller arguments
        $namedArguments = $metadata->getNamedArguments();
    }

.. versionadded:: 8.1

    The ``controllerMetadata`` property was introduced in Symfony 8.1.

``kernel.view``
~~~~~~~~~~~~~~~

**Event Class**: :class:`Symfony\\Component\\HttpKernel\\Event\\ViewEvent`

This event is dispatched after the controller has been executed but *only* if
the controller does *not* return a :class:`Symfony\\Component\\HttpFoundation\\Response`
object. It's useful to transform the returned value (e.g. a string with some
HTML contents) into the ``Response`` object needed by Symfony::

    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpKernel\Event\ViewEvent;

    public function onKernelView(ViewEvent $event): void
    {
        $value = $event->getControllerResult();
        $response = new Response();

        // ... somehow customize the Response from the return value

        $event->setResponse($response);
    }

.. seealso::

    Read more on the :ref:`kernel.view event <component-http-kernel-kernel-view>`.

Execute this command to find out which listeners are registered for this event and
their priorities:

.. code-block:: terminal

    $ php bin/console debug:event-dispatcher kernel.view

``kernel.response``
~~~~~~~~~~~~~~~~~~~

**Event Class**: :class:`Symfony\\Component\\HttpKernel\\Event\\ResponseEvent`

This event is dispatched after the controller or any ``kernel.view`` listener
returns a ``Response`` object. It's useful to modify or replace the response
before sending it back (e.g. add/modify HTTP headers, add cookies, etc.)::

    use Symfony\Component\HttpKernel\Event\ResponseEvent;

    public function onKernelResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();

        // ... modify the response object
    }

You can use ``getControllerAttributes()`` to retrieve PHP attributes declared
on the controller that handled the request:

.. code-block:: php-attributes

    use Symfony\Component\HttpKernel\Event\ResponseEvent;

    public function onKernelResponse(ResponseEvent $event): void
    {
        // get all controller attributes, grouped by class name
        $allAttributes = $event->getControllerAttributes();

        // get all controller attributes as a flat list (not grouped)
        $flatAttributes = $event->getControllerAttributes('*');

        // get attributes of a specific class
        $cacheAttributes = $event->getControllerAttributes(Cache::class);
    }

.. versionadded:: 8.1

    The ``ResponseEvent::getControllerAttributes()`` method was introduced in
    Symfony 8.1.

.. seealso::

    Read more on the :ref:`kernel.response event <component-http-kernel-kernel-response>`.

Execute this command to find out which listeners are registered for this event and
their priorities:

.. code-block:: terminal

    $ php bin/console debug:event-dispatcher kernel.response

``kernel.finish_request``
~~~~~~~~~~~~~~~~~~~~~~~~~

**Event Class**: :class:`Symfony\\Component\\HttpKernel\\Event\\FinishRequestEvent`

This event is dispatched after the ``kernel.response`` event. It's useful to reset
the global state of the application (for example, the translator listener resets
the translator's locale to the one of the parent request)::

    use Symfony\Component\HttpKernel\Event\FinishRequestEvent;

    public function onKernelFinishRequest(FinishRequestEvent $event): void
    {
        if (null === $parentRequest = $this->requestStack->getParentRequest()) {
            return;
        }

        // reset the locale of the subrequest to the locale of the parent request
        $this->setLocale($parentRequest);
    }

Execute this command to find out which listeners are registered for this event and
their priorities:

.. code-block:: terminal

    $ php bin/console debug:event-dispatcher kernel.finish_request

``kernel.terminate``
~~~~~~~~~~~~~~~~~~~~

**Event Class**: :class:`Symfony\\Component\\HttpKernel\\Event\\TerminateEvent`

This event is dispatched after the response has been sent (after the execution
of the :method:`Symfony\\Component\\HttpKernel\\HttpKernel::handle` method).
It's useful to perform slow or complex tasks that don't need to be completed to
send the response (e.g. sending emails).

.. seealso::

    Read more on the :ref:`kernel.terminate event <component-http-kernel-kernel-terminate>`.

Execute this command to find out which listeners are registered for this event and
their priorities:

.. code-block:: terminal

    $ php bin/console debug:event-dispatcher kernel.terminate

.. _kernel-kernel.exception:

``kernel.exception``
~~~~~~~~~~~~~~~~~~~~

**Event Class**: :class:`Symfony\\Component\\HttpKernel\\Event\\ExceptionEvent`

This event is dispatched as soon as an error occurs during the handling of the
HTTP request. It's useful to recover from errors or modify the exception details
sent as response::

    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpKernel\Event\ExceptionEvent;

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $response = new Response();
        // setup the Response object based on the caught exception
        $event->setResponse($response);

        // you can alternatively set a new Exception
        // $exception = new \Exception('Some special exception');
        // $event->setThrowable($exception);
    }

.. note::

    The TwigBundle registers an :class:`Symfony\\Component\\HttpKernel\\EventListener\\ErrorListener`
    that forwards the ``Request`` to a given controller defined by the
    ``exception_listener.controller`` parameter.

Symfony uses the following logic to determine the HTTP status code of the
response:

* If :method:`Symfony\\Component\\HttpFoundation\\Response::isClientError`,
  :method:`Symfony\\Component\\HttpFoundation\\Response::isServerError` or
  :method:`Symfony\\Component\\HttpFoundation\\Response::isRedirect` is true,
  then the status code on your ``Response`` object is used;

* If the original exception implements
  :class:`Symfony\\Component\\HttpKernel\\Exception\\HttpExceptionInterface`,
  then ``getStatusCode()`` is called on the exception and used (the headers
  from ``getHeaders()`` are also added);

* If both of the above aren't true, then a 500 status code is used.

.. note::

    If you want to overwrite the status code of the exception response, which
    you should not without a good reason, call
    ``ExceptionEvent::allowCustomResponseCode()`` first and then
    set the status code on the response::

        $event->allowCustomResponseCode();
        $response = new Response('No Content', 204);
        $event->setResponse($response);

    The status code sent to the client in the above example will be ``204``. If
    ``$event->allowCustomResponseCode()`` is omitted, then the kernel will set
    an appropriate status code based on the type of exception thrown.

.. seealso::

    Read more on the :ref:`kernel.exception event <component-http-kernel-kernel-exception>`.

Execute this command to find out which listeners are registered for this event and
their priorities:

.. code-block:: terminal

    $ php bin/console debug:event-dispatcher kernel.exception

.. _controller-attribute-events:

Controller Attribute Events
---------------------------

.. versionadded:: 8.1

    Controller attribute events were introduced in Symfony 8.1.

In addition to the kernel events listed above, the HttpKernel component
automatically dispatches events named after PHP attributes found on the resolved
controller. This allows you to write listeners that target a specific controller
attribute instead of listening to a generic kernel event and manually inspecting
it for the attribute's presence.

Each attribute event name follows the convention ``{kernelEvent}.{AttributeClassName}``.
For example, when a controller has a ``#[Cache]`` attribute, the following event
is dispatched:

.. code-block:: text

    kernel.controller_arguments.Symfony\Component\HttpKernel\Attribute\Cache

**Event Class**: :class:`Symfony\\Component\\HttpKernel\\Event\\ControllerAttributeEvent`

The ``ControllerAttributeEvent`` provides two properties:

``$event->attribute``
    The PHP attribute instance found on the controller.

``$event->kernelEvent``
    The underlying kernel event (e.g.
    :class:`Symfony\\Component\\HttpKernel\\Event\\ControllerArgumentsEvent`),
    giving access to the request, response and other contextual data.

This mechanism is supported by all kernel events **except** ``kernel.request``
(the controller is not yet resolved) and ``kernel.terminate``.

The following example shows a listener using this mechanism::

    use App\Attribute\MyRateLimit;
    use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
    use Symfony\Component\HttpKernel\KernelEvents;

    #[AsEventListener(event: KernelEvents::CONTROLLER_ARGUMENTS.'.'.MyRateLimit::class)]
    class MyRateLimitListener
    {
        public function __invoke(object $event): void
        {
            // $event is a ControllerAttributeEvent instance
            $attribute = $event->attribute;
            $request = $event->kernelEvent->getRequest();

            // implement rate limiting logic...
        }
    }

Execute this command to find out which listeners are registered for attribute
events on a given kernel event:

.. code-block:: terminal

    $ php bin/console debug:event-dispatcher kernel.controller_arguments

.. seealso::

    Read more on the :ref:`controller attribute events <http-kernel-controller-attribute-events>`
    in the HttpKernel component documentation.
