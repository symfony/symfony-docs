.. index::
   single: Internals; Kernel

Kernel
======

:class:`Symfony\\Component\\HttpKernel\\HttpKernel` is the class responsible
for handling client requests. Its main goal is to "convert"
:class:`Symfony\\Component\\HttpFoundation\\Request` objects to
:class:`Symfony\\Component\\HttpFoundation\\Response` ones.

All Symfony2 Kernels implement
:class:`Symfony\\Component\\HttpKernel\\HttpKernelInterface`::

    function handle(Request $request = null, $type = self::MASTER_REQUEST, $raw = false);

.. index::
   single: Internals; Controller Resolver

Controllers
-----------

To convert a Request to a Response, the Kernel relies on a "Controller". A
Controller can be any valid PHP callable.

The Kernel delegates the selection of the Controller to execute to an
implementation of
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
   ``_controller`` Request attribute (see below).

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

The ``handle()`` method takes a Request and *always* returns a Response. To
convert the Request, ``handle()`` relies on the Resolver and an ordered chain
of Event notifications (see the next section for more information about each
Event):

1. Before doing anything else, the ``core.request`` event is notified -- if
   one of the listener returns a Response, it jumps to step 8 directly;

2. The Resolver is called to determine the Controller to execute;

3. Listeners of the ``core.controller`` event can now manipulate the
   Controller callable the way they want (change it, wrap it, ...);

4. The Kernel checks that the Controller is actually a valid PHP callable;

5. The Resolver is called to determine the arguments to pass to the Controller;

6. The Kernel calls the Controller;

7. Listeners of the ``core.view`` event can change the Controller return value
   (to convert it to a Response for instance);

8. Listeners of the ``core.response`` event can manipulate the Response
   (content and headers);

9. The Response is returned.

If an Exception is thrown during processing, the ``core.exception`` is
notified and listeners are given a change to convert the Exception to a
Response. If that works, the ``core.response`` event is notified; if not the
Exception is re-thrown.

If you don't want Exceptions to be caught (for embedded requests for instance),
disable the ``core.exception`` event by passing ``true`` as the third argument
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

All events have a ``request_type`` parameter which allows listeners to know
the type of the request. For instance, if a listener must only be active for
master requests, add the following code at the beginning of your listener
method::

    if (HttpKernelInterface::MASTER_REQUEST !== $event->getParameter('request_type')) {
        // return immediately
        // if the event is a filter, return the filtered value instead
        return;
    }

.. tip::
   If you are not yet familiar with the Symfony2 Event Dispatcher, read the
   :doc:`dedicated chapter </guides/event/overview>` first.

.. index::
   single: Event; core.request

``core.request`` Event
~~~~~~~~~~~~~~~~~~~~~~

*Type*: ``notifyUntil()``

*Parameters*: ``request_type`` and ``request``

As the event is notified with the ``notifyUntil()`` method, if a listener
returns a Response object, other listeners won't be called.

This event is used by ``FrameworkBundle`` to populate the ``_controller``
Request attribute, via the
:class:`Symfony\\Bundle\\FrameworkBundle\\RequestListener`. RequestListener
uses a :class:`Symfony\\Component\\Routing\\RouterInterface` object to match
the Request and determine the Controller name (stored in the ``_controller``
Request attribute).

.. index::
   single: Event; core.controller

``core.controller`` Event
~~~~~~~~~~~~~~~~~~~~~~~~~

*Type*: ``filter``

*Arguments*: ``request_type`` and ``request``

*Value to filter*: The Controller value

This event is not used by ``FrameworkBundle``.

.. index::
   single: Event; core.view

``core.view`` Event
~~~~~~~~~~~~~~~~~~~

*Type*: ``filter``

*Arguments*: ``request_type`` and ``request``

*Value to filter*: The Controller returned value

This event is not used by ``FrameworkBundle``. It can be used to implement a
view sub-system.

.. index::
   single: Event; core.response

``core.response`` Event
~~~~~~~~~~~~~~~~~~~~~~~

*Type*: ``filter``

*Arguments*: ``request_type`` and ``request``

*Value to filter*: The Response instance

``FrameworkBundle`` registers several listeners:

* :class:`Symfony\\Component\\HttpKernel\\Profiler\\ProfilerListener`: collects
  data for the current request;

* :class:`Symfony\\Bundle\\WebProfilerBundle\\WebDebugToolbarListener`: injects
  the Web Debug Toolbar;

* :class:`Symfony\\Component\\HttpKernel\\ResponseListener`: fixes the
  Response ``Content-Type``;

* :class:`Symfony\\Component\\HttpKernel\\Cache\\EsiListener`: adds a
  ``Surrogate-Control`` HTTP header when the Response needs to be parsed for
  ESI tags.

.. index::
   single: Event; core.exception

``core.exception`` Event
~~~~~~~~~~~~~~~~~~~~~~~~

*Type*: ``notifyUntil``

*Arguments*: ``request_type``, ``request``, and ``exception``

``FrameworkBundle`` registers a
:class:`Symfony\\Bundle\\FrameworkBundle\\Controller\\ExceptionListener` that
forwards the Request to a given Controller (the value of the
``exception_listener.controller`` parameter -- must be in the
``class::method`` notation).

.. _kernel_listener_tag:

Enabling Custom Listeners
-------------------------

To enable a custom listener, add it as a regular service in one of your
configuration, and tag it with ``kernel.listener``:

.. configuration-block::

    .. code-block:: yaml

        services:
            kernel.listener.your_listener_name:
                class: Fully\Qualified\Listener\Class\Name
                tags:
                    - { name: kernel.listener }

    .. code-block:: xml

        <service id="kernel.listener.your_listener_name" class="Fully\Qualified\Listener\Class\Name">
            <tag name="kernel.listener" />
        </service>

    .. code-block:: php

        $container
            ->register('kernel.listener.your_listener_name', 'Fully\Qualified\Listener\Class\Name')
            ->addTag('kernel.listener')
        ;

The Listener must have a ``register()`` method that takes an
``EventDispatcher`` as its argument and registers itself::

    /**
     * Registers a core.* listener.
     *
     * @param EventDispatcher $dispatcher An EventDispatcher instance
     */
    public function register(EventDispatcher $dispatcher)
    {
        $dispatcher->connect('core.*', array($this, 'xxxxxxx'));
    }
