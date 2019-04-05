Built-in Symfony Events
=======================

During the handling of an HTTP request, the Symfony framework (or any
application using the :doc:`HttpKernel component </components/http_kernel>`)
dispatches some :doc:`events </event_dispatcher>` which you can use to modify
how the request is handled.

Kernel Events
-------------

Each event dispatched by the HttpKernel component is a subclass of
:class:`Symfony\\Component\\HttpKernel\\Event\\KernelEvent`, which provides the
following information:

:method:`Symfony\\Component\\HttpKernel\\Event\\KernelEvent::getRequestType`
    Returns the *type* of the request (``HttpKernelInterface::MASTER_REQUEST``
    or ``HttpKernelInterface::SUB_REQUEST``).

:method:`Symfony\\Component\\HttpKernel\\Event\\KernelEvent::getKernel`
    Returns the Kernel handling the request.

:method:`Symfony\\Component\\HttpKernel\\Event\\KernelEvent::getRequest`
    Returns the current ``Request`` being handled.

.. _kernel-core-request:

``kernel.request``
~~~~~~~~~~~~~~~~~~

**Event Class**: :class:`Symfony\\Component\\HttpKernel\\Event\\GetResponseEvent`

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

**Event Class**: :class:`Symfony\\Component\\HttpKernel\\Event\\FilterControllerEvent`

This event is dispatched after the controller to be executed has been resolved
but before executing it. It's useful to initialize things later needed by the
controller, such as `param converters`_, and even to change the controller
entirely::

    use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

    public function onKernelController(FilterControllerEvent $event)
    {
        // ...

        // the controller can be changed to any PHP callable
        $event->setController($myCustomController);
    }

.. seealso::

    Read more on the :ref:`kernel.controller event <component-http-kernel-kernel-controller>`.

Execute this command to find out which listeners are registered for this event and
their priorities:

.. code-block:: terminal

    $ php bin/console debug:event-dispatcher kernel.controller

``kernel.controller_arguments``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**Event Class**: :class:`Symfony\\Component\\HttpKernel\\Event\\FilterControllerArgumentsEvent`

This event is dispatched just before a controller is called. It's useful to
configure the arguments that are going to be passed to the controller.
Typically, this is used to map URL routing parameters to their corresponding
named arguments; or pass the current request when the ``Request`` type-hint is
found::

    public function onKernelControllerArguments(FilterControllerArgumentsEvent $event)
    {
        // ...

        // get controller and request arguments
        $namedArguments = $event->getRequest()->attributes->all();
        $controllerArguments = $event->getArguments();

        // set the controller arguments to modify the original arguments or add new ones
        $event->setArguments($newArguments);
    }

Execute this command to find out which listeners are registered for this event and
their priorities:

.. code-block:: terminal

    $ php bin/console debug:event-dispatcher kernel.controller_arguments

``kernel.view``
~~~~~~~~~~~~~~~

**Event Class**: :class:`Symfony\\Component\\HttpKernel\\Event\\GetResponseForControllerResultEvent`

This event is dispatched after the controller has been executed but *only* if
the controller does *not* return a :class:`Symfony\\Component\\HttpFoundation\\Response`
object. It's useful to transform the returned value (e.g. a string with some
HTML contents) into the ``Response`` object needed by Symfony::

    use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
    use Symfony\Component\HttpFoundation\Response;

    public function onKernelView(GetResponseForControllerResultEvent $event)
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

**Event Class**: :class:`Symfony\\Component\\HttpKernel\\Event\\FilterResponseEvent`

This event is dispatched after the controller or any ``kernel.view`` listener
returns a ``Response`` object. It's useful to modify or replace the response
before sending it back (e.g. add/modify HTTP headers, add cookies, etc.)::

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $response = $event->getResponse();

        // ... modify the response object
    }

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

    public function onKernelFinishRequest(FinishRequestEvent $event)
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

**Event Class**: :class:`Symfony\\Component\\HttpKernel\\Event\\PostResponseEvent`

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

**Event Class**: :class:`Symfony\\Component\\HttpKernel\\Event\\GetResponseForExceptionEvent`

This event is dispatched as soon as an error occurs during the handling of the
HTTP request. It's useful to recover from errors or modify the exception details
sent as response::

    use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
    use Symfony\Component\HttpFoundation\Response;

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        $response = new Response();
        // setup the Response object based on the caught exception
        $event->setResponse($response);

        // you can alternatively set a new Exception
        // $exception = new \Exception('Some special exception');
        // $event->setException($exception);
    }

.. note::

    The TwigBundle registers an :class:`Symfony\\Component\\HttpKernel\\EventListener\\ExceptionListener`
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
    ``GetResponseForExceptionEvent::allowCustomResponseCode()`` first and then
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

.. _`param converters`: https://symfony.com/doc/master/bundles/SensioFrameworkExtraBundle/annotations/converters.html
