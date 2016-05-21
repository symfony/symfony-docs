Symfony Framework Events
========================

When the Symfony Framework (or anything using the :class:`Symfony\\Component\\HttpKernel\\HttpKernel`)
handles a request, a few core events are dispatched so that you can add
listeners throughout the process. These are called the "kernel events".
For a larger explanation, see :doc:`/components/http_kernel/introduction`.

Kernel Events
-------------

Each event dispatched by the kernel is a subclass of
:class:`Symfony\\Component\\HttpKernel\\Event\\KernelEvent`. This means
that each event has access to the following information:

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
determined.

.. seealso::

    Read more on the :ref:`kernel.request event <component-http-kernel-kernel-request>`.

These are the built-in Symfony listeners registered to this event:

=============================================================================  ========
Listener Class Name                                                            Priority
=============================================================================  ========
:class:`Symfony\\Component\\HttpKernel\\EventListener\\ProfilerListener`       1024
:class:`Symfony\\Bundle\\FrameworkBundle\\EventListener\\TestSessionListener`  192
:class:`Symfony\\Bundle\\FrameworkBundle\\EventListener\\SessionListener`      128
:class:`Symfony\\Component\\HttpKernel\\EventListener\\RouterListener`         32
:class:`Symfony\\Component\\HttpKernel\\EventListener\\LocaleListener`         16
:class:`Symfony\\Component\\Security\\Http\\Firewall`                          8
=============================================================================  ========

``kernel.controller``
~~~~~~~~~~~~~~~~~~~~~

**Event Class**: :class:`Symfony\\Component\\HttpKernel\\Event\\FilterControllerEvent`

This event can be an entry point used to modify the controller that should be executed::

    use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();
        // ...

        // the controller can be changed to any PHP callable
        $event->setController($controller);
    }

.. seealso::

    Read more on the :ref:`kernel.controller event <component-http-kernel-kernel-controller>`.

This is the built-in Symfony listener related to this event:

==============================================================================  ========
Listener Class Name                                                             Priority
==============================================================================  ========
:class:`Symfony\\Bundle\\FrameworkBundle\\DataCollector\\RequestDataCollector`  0
==============================================================================  ========

``kernel.view``
~~~~~~~~~~~~~~~

**Event Class**: :class:`Symfony\\Component\\HttpKernel\\Event\\GetResponseForControllerResultEvent`

This event is not used by the FrameworkBundle, but it can be used to implement
a view sub-system. This event is called *only* if the Controller does *not*
return a ``Response`` object. The purpose of the event is to allow some
other return value to be converted into a ``Response``.

The value returned by the Controller is accessible via the ``getControllerResult``
method::

    use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
    use Symfony\Component\HttpFoundation\Response;

    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $val = $event->getControllerResult();
        $response = new Response();

        // ... somehow customize the Response from the return value

        $event->setResponse($response);
    }

.. seealso::

    Read more on the :ref:`kernel.view event <component-http-kernel-kernel-view>`.

``kernel.response``
~~~~~~~~~~~~~~~~~~~

**Event Class**: :class:`Symfony\\Component\\HttpKernel\\Event\\FilterResponseEvent`

The purpose of this event is to allow other systems to modify or replace
the ``Response`` object after its creation::

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $response = $event->getResponse();

        // ... modify the response object
    }

The FrameworkBundle registers several listeners:

:class:`Symfony\\Component\\HttpKernel\\EventListener\\ProfilerListener`
    Collects data for the current request.

:class:`Symfony\\Bundle\\WebProfilerBundle\\EventListener\\WebDebugToolbarListener`
    Injects the Web Debug Toolbar.

:class:`Symfony\\Component\\HttpKernel\\EventListener\\ResponseListener`
    Fixes the Response ``Content-Type`` based on the request format.

:class:`Symfony\\Component\\HttpKernel\\EventListener\\EsiListener`
    Adds a ``Surrogate-Control`` HTTP header when the Response needs to
    be parsed for ESI tags.

.. seealso::

    Read more on the :ref:`kernel.response event <component-http-kernel-kernel-response>`.

These are the built-in Symfony listeners registered to this event:

===================================================================================  ========
Listener Class Name                                                                  Priority
===================================================================================  ========
:class:`Symfony\\Component\\HttpKernel\\EventListener\\EsiListener`                  0
:class:`Symfony\\Component\\HttpKernel\\EventListener\\ResponseListener`             0
:class:`Symfony\\Bundle\\SecurityBundle\\EventListener\\ResponseListener`            0
:class:`Symfony\\Component\\HttpKernel\\EventListener\\ProfilerListener`             -100
:class:`Symfony\\Bundle\\FrameworkBundle\\EventListener\\TestSessionListener`        -128
:class:`Symfony\\Bundle\\WebProfilerBundle\\EventListener\\WebDebugToolbarListener`  -128
:class:`Symfony\\Component\\HttpKernel\\EventListener\\StreamedResponseListener`     -1024
===================================================================================  ========

``kernel.finish_request``
~~~~~~~~~~~~~~~~~~~~~~~~~

**Event Class**: :class:`Symfony\\Component\\HttpKernel\\Event\\FinishRequestEvent`

The purpose of this event is to allow you to reset the global and environmental
state of the application after a sub-request has finished (for example, the
translator listener resets the translator's locale to the one of the parent
request)::

    public function onKernelFinishRequest(FinishRequestEvent $event)
    {
        if (null === $parentRequest = $this->requestStack->getParentRequest()) {
            return;
        }

        //Reset the locale of the subrequest to the locale of the parent request
        $this->setLocale($parentRequest);
    }

These are the built-in Symfony listeners related to this event:

==========================================================================  ========
Listener Class Name                                                         Priority
==========================================================================  ========
:class:`Symfony\\Component\\HttpKernel\\EventListener\\LocaleListener`      0
:class:`Symfony\\Component\\HttpKernel\\EventListener\\TranslatorListener`  0
:class:`Symfony\\Component\\HttpKernel\\EventListener\\RouterListener`      0
:class:`Symfony\\Component\\Security\\Http\\Firewall`                       0
==========================================================================  ========

``kernel.terminate``
~~~~~~~~~~~~~~~~~~~~

**Event Class**: :class:`Symfony\\Component\\HttpKernel\\Event\\PostResponseEvent`

The purpose of this event is to perform tasks after the response was already
served to the client.

.. seealso::

    Read more on the :ref:`kernel.terminate event <component-http-kernel-kernel-terminate>`.

This is the built-in Symfony listener related to this event:

=========================================================================  ========
Listener Class Name                                                        Priority
=========================================================================  ========
`EmailSenderListener`_                                                     0
=========================================================================  ========


.. _kernel-kernel.exception:

``kernel.exception``
~~~~~~~~~~~~~~~~~~~~

**Event Class**: :class:`Symfony\\Component\\HttpKernel\\Event\\GetResponseForExceptionEvent`

The TwigBundle registers an :class:`Symfony\\Component\\HttpKernel\\EventListener\\ExceptionListener`
that forwards the ``Request`` to a given controller defined by the
``exception_listener.controller`` parameter.

A listener on this event can create and set a ``Response`` object, create
and set a new ``Exception`` object, or do nothing::

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

    As Symfony ensures that the Response status code is set to the most
    appropriate one depending on the exception, setting the status on the
    response won't work. If you want to overwrite the status code (which you
    should not without a good reason), set the ``X-Status-Code`` header::

        $response = new Response(
            'Error',
            404, // this status code will be ignored
            array(
                'X-Status-Code' => 200 // this status code will actually be sent to the client
            )
        );

.. seealso::

    Read more on the :ref:`kernel.exception event <component-http-kernel-kernel-exception>`.

These are the built-in Symfony listeners registered to this event:

=========================================================================  ========
Listener Class Name                                                        Priority
=========================================================================  ========
:class:`Symfony\\Component\\HttpKernel\\EventListener\\ProfilerListener`   0
:class:`Symfony\\Component\\HttpKernel\\EventListener\\ExceptionListener`  -128
=========================================================================  ========

.. _`EmailSenderListener`: https://github.com/symfony/swiftmailer-bundle/blob/master/EventListener/EmailSenderListener.php
