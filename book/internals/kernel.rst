.. index::
   single: Internals; Kernel

Kernel
======

The :class:`Symfony\\Component\\HttpKernel\\HttpKernel` class is the central
class of Symfony2 and is responsible for handling client requests. Its main
goal is to "convert" a :class:`Symfony\\Component\\HttpFoundation\\Request`
object to :class:`Symfony\\Component\\HttpFoundation\\Response` object.

Every Symfony2 Kernel implements
:class:`Symfony\\Component\\HttpKernel\\HttpKernelInterface`::

    function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)

.. index::
   single: Internals; Controller Resolver

Controllers
-----------

To convert a Request to a Response, the Kernel relies on a "Controller". A
Controller can be any valid PHP callable.

The Kernel delegates the selection of what Controller should be executed
to an implementation of
:class:`Symfony\\Component\\HttpKernel\\Controller\\ControllerResolverInterface`::

    public function getController(Request $request);

    public function getArguments(Request $request, $controller);

The
:method:`Symfony\\Component\\HttpKernel\\Controller\\ControllerResolverInterface::getController`
method returns the Controller (a PHP callable) associated with the given
Request. The default implementation
(:class:`Symfony\\Component\\HttpKernel\\Controller\\ControllerResolver`)
looks for a ``_controller`` request attribute that represents the controller
name (a "class::method" string, like
``Bundle\BlogBundle\PostController:indexAction``).

.. tip::

    The default implementation uses the
    :class:`Symfony\\Bundle\\FrameworkBundle\\RequestListener` to define the
    ``_controller`` Request attribute (see :ref:`kernel-onCoreRequest`).

The
:method:`Symfony\\Component\\HttpKernel\\Controller\\ControllerResolverInterface::getArguments`
method returns an array of arguments to pass to the Controller callable. The
default implementation automatically resolves the method arguments, based on
the Request attributes.

.. sidebar:: Matching Controller method arguments from Request attributes

    For each method argument, Symfony2 tries to get the value of a Request
    attribute with the same name. If it is not defined, the argument default
    value is used if defined::

        // Symfony2 will look for an 'id' attribute (mandatory)
        // and an 'admin' one (optional)
        public function showAction($id, $admin = true)
        {
            // ...
        }

.. index::
  single: Internals; Request Handling

Handling Requests
-----------------

The ``handle()`` method takes a ``Request`` and *always* returns a ``Response``.
To convert the ``Request``, ``handle()`` relies on the Resolver and an ordered
chain of Event notifications (see the next section for more information about
each Event):

1. Before doing anything else, the ``onCoreRequest`` event is notified -- if
   one of the listener returns a ``Response``, it jumps to step 8 directly;

2. The Resolver is called to determine the Controller to execute;

3. Listeners of the ``onCoreController`` event can now manipulate the
   Controller callable the way they want (change it, wrap it, ...);

4. The Kernel checks that the Controller is actually a valid PHP callable;

5. The Resolver is called to determine the arguments to pass to the Controller;

6. The Kernel calls the Controller;

7. If the Controller does not return a ``Response``, listeners of the
   ``onCoreView`` event can convert the Controller return value to a ``Response``;

8. Listeners of the ``onCoreResponse`` event can manipulate the ``Response``
   (content and headers);

9. The Response is returned.

If an Exception is thrown during processing, the ``onCoreException`` is
notified and listeners are given a change to convert the Exception to a
Response. If that works, the ``onCoreResponse`` event is notified; if not the
Exception is re-thrown.

If you don't want Exceptions to be caught (for embedded requests for instance),
disable the ``onCoreException`` event by passing ``false`` as the third argument
to the ``handle()`` method.

.. index::
  single: Internals; Internal Requests

Internal Requests
-----------------

At any time during the handling of a request (the 'master' one), a sub-request
can be handled. You can pass the request type to the ``handle()`` method (its
second argument):

* ``HttpKernelInterface::MASTER_REQUEST``;
* ``HttpKernelInterface::SUB_REQUEST``.

The type is passed to all events and listeners can act accordingly (some
processing must only occur on the master request).

.. index::
   pair: Kernel; Event

Events
------

Each event thrown by the Kernel is a subclass of
:class:`Symfony\Component\HttpKernel\Event\KernelEvent`. This means that
each event has access to the same basic information:

* ``getRequestType()`` - returns the *type* of the request (master or sub request);

* ``getKernel()`` - returns the Kernel handling the request;

* ``getRequest()`` - returns the current ``Request`` being handled.

``getRequestType()``
~~~~~~~~~~~~~~~~~~~~

The ``getRequestType()`` method allows listeners to know the type of the
request. For instance, if a listener must only be active for master requests,
add the following code at the beginning of your listener method::

    if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
        // return immediately
        return;
    }

.. tip::

    If you are not yet familiar with the Symfony2 Event Dispatcher, read the
    :doc:`dedicated chapter </book/internals/event_dispatcher>` first.

.. index::
   single: Event; onCoreRequest

.. _kernel-onCoreRequest:

``onCoreRequest`` Event
~~~~~~~~~~~~~~~~~~~~~~~

*Event Class*: :class:`Symfony\\Component\\HttpKernel\\Event\\GetResponseEvent`

The goal of this event is to either return a ``Response`` object immediately
or setup variables so that a Controller can be called after the event. Any
listener can return a ``Response`` object via the ``setResponse()`` method
on the event. In this case, all other listeners won't be called.

This event is used by ``FrameworkBundle`` to populate the ``_controller``
``Request`` attribute, via the
:class:`Symfony\\Bundle\\FrameworkBundle\\RequestListener`. RequestListener
uses a :class:`Symfony\\Component\\Routing\\RouterInterface` object to match
the ``Request`` and determine the Controller name (stored in the ``_controller``
``Request`` attribute).

.. index::
   single: Event; onCoreController

``onCoreController`` Event
~~~~~~~~~~~~~~~~~~~~~~~~~~

*Event Class*: :class:`Symfony\Component\HttpKernel\Event\FilterControllerEvent`

This event is not used by ``FrameworkBundle``, but can be an entry point used
to modify the controller that should be executed:

.. code-block:: php

    use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

    public function onCoreController(FilterControllerEvent $event)
    {
        $controller = $event->getController();
        // ...

        // the controller can be changed to any PHP callable
        $event->setController($controller);
    }

.. index::
   single: Event; onCoreView

``onCoreView`` Event
~~~~~~~~~~~~~~~~~~~~

*Event Class*: :class:`Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent`

This event is not used by ``FrameworkBundle``, but it can be used to implement
a view sub-system. This event is called *only* called if the Controller does
*not* return a ``Response`` object. The purpose of the event is to allow some
other return value to be converted into a ``Response``.

The value returned by the Controller is accessible via the ``getControllerResult``
method::

    use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
    use Symfony\Component\HttpFoundation\Response;

    public function onCoreView(GetResponseForControllerResultEvent $event)
    {
        $val = $event->getReturnValue();
        $response = new Response();
        // some how customize the Response from the return value

        $event->setResponse($response);
    }

.. index::
   single: Event; onCoreResponse

``onCoreResponse`` Event
~~~~~~~~~~~~~~~~~~~~~~~~

*Event Class*: :class:`Symfony\Component\HttpKernel\Event\FilterResponseEvent`

The purpose of this event is to allow other systems to modify or replace
the ``Response`` object after its creation:

.. code-block:: php

    public function onCoreResponse(FilterResponseEvent $event)
    {
        $response = $event->getResponse();
        // .. modify the response object
    }

The ``FrameworkBundle`` registers several listeners:

* :class:`Symfony\\Component\\HttpKernel\\Profiler\\ProfilerListener`:
  collects data for the current request;

* :class:`Symfony\\Bundle\\WebProfilerBundle\\WebDebugToolbarListener`:
  injects the Web Debug Toolbar;

* :class:`Symfony\\Component\\HttpKernel\\ResponseListener`: fixes the
  Response ``Content-Type`` based on the request format;

* :class:`Symfony\\Component\\HttpKernel\\Cache\\EsiListener`: adds a
  ``Surrogate-Control`` HTTP header when the Response needs to be parsed for
  ESI tags.

.. index::
   single: Event; onCoreException

.. _kernel-onCoreException:

``onCoreException`` Event
~~~~~~~~~~~~~~~~~~~~~~~~~

*Event Class*: :class:`Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent`

``FrameworkBundle`` registers a
:class:`Symfony\\Bundle\\FrameworkBundle\\Controller\\ExceptionListener` that
forwards the ``Request`` to a given Controller (the value of the
``exception_listener.controller`` parameter -- must be in the
``class::method`` notation).

A listener on this event can create and set a ``Response`` object, create
and set a new ``Exception`` object, or do nothing:

.. code-block:: php

    use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
    use Symfony\Component\HttpFoundation\Response;

    public function onCoreException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        $response = new Response();
        // setup the Response object based on the caught exception
        $event->setResponse($response);

        // you can alternatively set a new Exception
        // $exception = new \Exception('Some special exception');
        // $event->setException($exception);
    }