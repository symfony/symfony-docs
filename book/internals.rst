.. index::
   single: Internals

Internals
=========

Looks like you want to understand how Symfony2 works and how to extend it.
That makes me very happy! This section is an in-depth explanation of the
Symfony2 internals.

.. note::

    You need to read this section only if you want to understand how Symfony2
    works behind the scene, or if you want to extend Symfony2.

Overview
--------

The Symfony2 code is made of several independent layers. Each layer is built
on top of the previous one.

.. tip::

    Autoloading is not managed by the framework directly; it's done
    independently with the help of the
    :class:`Symfony\\Component\\ClassLoader\\UniversalClassLoader` class
    and the ``src/autoload.php`` file. Read the :doc:`dedicated chapter
    </components/class_loader>` for more information.

``HttpFoundation`` Component
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The deepest level is the :namespace:`Symfony\\Component\\HttpFoundation`
component. HttpFoundation provides the main objects needed to deal with HTTP.
It is an Object-Oriented abstraction of some native PHP functions and
variables:

* The :class:`Symfony\\Component\\HttpFoundation\\Request` class abstracts
  the main PHP global variables like ``$_GET``, ``$_POST``, ``$_COOKIE``,
  ``$_FILES``, and ``$_SERVER``;

* The :class:`Symfony\\Component\\HttpFoundation\\Response` class abstracts
  some PHP functions like ``header()``, ``setcookie()``, and ``echo``;

* The :class:`Symfony\\Component\\HttpFoundation\\Session` class and
  :class:`Symfony\\Component\\HttpFoundation\\SessionStorage\\SessionStorageInterface`
  interface abstract session management ``session_*()`` functions.

``HttpKernel`` Component
~~~~~~~~~~~~~~~~~~~~~~~~

On top of HttpFoundation is the :namespace:`Symfony\\Component\\HttpKernel`
component. HttpKernel handles the dynamic part of HTTP; it is a thin wrapper
on top of the Request and Response classes to standardize the way requests are
handled. It also provides extension points and tools that makes it the ideal
starting point to create a Web framework without too much overhead.

It also optionally adds configurability and extensibility, thanks to the
Dependency Injection component and a powerful plugin system (bundles).

.. seealso::

    Read more about the :doc:`HttpKernel <kernel>` component. Read more about
    :doc:`Dependency Injection </book/service_container>` and :doc:`Bundles
    </cookbook/bundles/best_practices>`.

``FrameworkBundle`` Bundle
~~~~~~~~~~~~~~~~~~~~~~~~~~

The :namespace:`Symfony\\Bundle\\FrameworkBundle` bundle is the bundle that
ties the main components and libraries together to make a lightweight and fast
MVC framework. It comes with a sensible default configuration and conventions
to ease the learning curve.

.. index::
   single: Internals; Kernel

Kernel
------

The :class:`Symfony\\Component\\HttpKernel\\HttpKernel` class is the central
class of Symfony2 and is responsible for handling client requests. Its main
goal is to "convert" a :class:`Symfony\\Component\\HttpFoundation\\Request`
object to a :class:`Symfony\\Component\\HttpFoundation\\Response` object.

Every Symfony2 Kernel implements
:class:`Symfony\\Component\\HttpKernel\\HttpKernelInterface`::

    function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)

.. index::
   single: Internals; Controller Resolver

Controllers
~~~~~~~~~~~

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
    :class:`Symfony\\Bundle\\FrameworkBundle\\EventListener\\RouterListener`
    to define the ``_controller`` Request attribute (see :ref:`kernel-core-request`).

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
~~~~~~~~~~~~~~~~~

The ``handle()`` method takes a ``Request`` and *always* returns a ``Response``.
To convert the ``Request``, ``handle()`` relies on the Resolver and an ordered
chain of Event notifications (see the next section for more information about
each Event):

1. Before doing anything else, the ``kernel.request`` event is notified -- if
   one of the listeners returns a ``Response``, it jumps to step 8 directly;

2. The Resolver is called to determine the Controller to execute;

3. Listeners of the ``kernel.controller`` event can now manipulate the
   Controller callable the way they want (change it, wrap it, ...);

4. The Kernel checks that the Controller is actually a valid PHP callable;

5. The Resolver is called to determine the arguments to pass to the Controller;

6. The Kernel calls the Controller;

7. If the Controller does not return a ``Response``, listeners of the
   ``kernel.view`` event can convert the Controller return value to a ``Response``;

8. Listeners of the ``kernel.response`` event can manipulate the ``Response``
   (content and headers);

9. The Response is returned.

If an Exception is thrown during processing, the ``kernel.exception`` is
notified and listeners are given a chance to convert the Exception to a
Response. If that works, the ``kernel.response`` event is notified; if not, the
Exception is re-thrown.

If you don't want Exceptions to be caught (for embedded requests for
instance), disable the ``kernel.exception`` event by passing ``false`` as the
third argument to the ``handle()`` method.

.. index::
  single: Internals; Internal Requests

Internal Requests
~~~~~~~~~~~~~~~~~

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
~~~~~~

Each event thrown by the Kernel is a subclass of
:class:`Symfony\\Component\\HttpKernel\\Event\\KernelEvent`. This means that
each event has access to the same basic information:

* ``getRequestType()`` - returns the *type* of the request
  (``HttpKernelInterface::MASTER_REQUEST`` or ``HttpKernelInterface::SUB_REQUEST``);

* ``getKernel()`` - returns the Kernel handling the request;

* ``getRequest()`` - returns the current ``Request`` being handled.

``getRequestType()``
....................

The ``getRequestType()`` method allows listeners to know the type of the
request. For instance, if a listener must only be active for master requests,
add the following code at the beginning of your listener method::

    use Symfony\Component\HttpKernel\HttpKernelInterface;

    if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
        // return immediately
        return;
    }

.. tip::

    If you are not yet familiar with the Symfony2 Event Dispatcher, read the
    :ref:`event_dispatcher` section first.

.. index::
   single: Event; kernel.request

.. _kernel-core-request:

``kernel.request`` Event
........................

*Event Class*: :class:`Symfony\\Component\\HttpKernel\\Event\\GetResponseEvent`

The goal of this event is to either return a ``Response`` object immediately
or setup variables so that a Controller can be called after the event. Any
listener can return a ``Response`` object via the ``setResponse()`` method on
the event. In this case, all other listeners won't be called.

This event is used by ``FrameworkBundle`` to populate the ``_controller``
``Request`` attribute, via the
:class:`Symfony\\Bundle\\FrameworkBundle\\EventListener\\RouterListener`. RequestListener
uses a :class:`Symfony\\Component\\Routing\\RouterInterface` object to match
the ``Request`` and determine the Controller name (stored in the
``_controller`` ``Request`` attribute).

.. index::
   single: Event; kernel.controller

``kernel.controller`` Event
...........................

*Event Class*: :class:`Symfony\\Component\\HttpKernel\\Event\\FilterControllerEvent`

This event is not used by ``FrameworkBundle``, but can be an entry point used
to modify the controller that should be executed:

.. code-block:: php

    use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();
        // ...

        // the controller can be changed to any PHP callable
        $event->setController($controller);
    }

.. index::
   single: Event; kernel.view

``kernel.view`` Event
.....................

*Event Class*: :class:`Symfony\\Component\\HttpKernel\\Event\\GetResponseForControllerResultEvent`

This event is not used by ``FrameworkBundle``, but it can be used to implement
a view sub-system. This event is called *only* if the Controller does *not*
return a ``Response`` object. The purpose of the event is to allow some other
return value to be converted into a ``Response``.

The value returned by the Controller is accessible via the
``getControllerResult`` method::

    use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
    use Symfony\Component\HttpFoundation\Response;

    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $val = $event->getReturnValue();
        $response = new Response();
        // some how customize the Response from the return value

        $event->setResponse($response);
    }

.. index::
   single: Event; kernel.response

``kernel.response`` Event
.........................

*Event Class*: :class:`Symfony\\Component\\HttpKernel\\Event\\FilterResponseEvent`

The purpose of this event is to allow other systems to modify or replace the
``Response`` object after its creation:

.. code-block:: php

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $response = $event->getResponse();
        // .. modify the response object
    }

The ``FrameworkBundle`` registers several listeners:

* :class:`Symfony\\Component\\HttpKernel\\EventListener\\ProfilerListener`:
  collects data for the current request;

* :class:`Symfony\\Bundle\\WebProfilerBundle\\EventListener\\WebDebugToolbarListener`:
  injects the Web Debug Toolbar;

* :class:`Symfony\\Component\\HttpKernel\\EventListener\\ResponseListener`: fixes the
  Response ``Content-Type`` based on the request format;

* :class:`Symfony\\Component\\HttpKernel\\EventListener\\EsiListener`: adds a
  ``Surrogate-Control`` HTTP header when the Response needs to be parsed for
  ESI tags.

.. index::
   single: Event; kernel.exception

.. _kernel-kernel.exception:

``kernel.exception`` Event
..........................

*Event Class*: :class:`Symfony\\Component\\HttpKernel\\Event\\GetResponseForExceptionEvent`

``FrameworkBundle`` registers an
:class:`Symfony\\Component\\HttpKernel\\EventListener\\ExceptionListener` that
forwards the ``Request`` to a given Controller (the value of the
``exception_listener.controller`` parameter -- must be in the
``class::method`` notation).

A listener on this event can create and set a ``Response`` object, create
and set a new ``Exception`` object, or do nothing:

.. code-block:: php

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

.. index::
   single: Event Dispatcher

.. _`book-internals-event-dispatcher`:

The Event Dispatcher
--------------------

Objected Oriented code has gone a long way to ensuring code extensibility. By
creating classes that have well defined responsibilities, your code becomes
more flexible and a developer can extend them with subclasses to modify their
behaviors. But if he wants to share his changes with other developers who have
also made their own subclasses, code inheritance is moot.

Consider the real-world example where you want to provide a plugin system for
your project. A plugin should be able to add methods, or do something before
or after a method is executed, without interfering with other plugins. This is
not an easy problem to solve with single inheritance, and multiple inheritance
(were it possible with PHP) has its own drawbacks.

The Symfony2 Event Dispatcher implements the `Observer`_ pattern in a simple
and effective way to make all these things possible and to make your projects
truly extensible.

Take a simple example from the `Symfony2 HttpKernel component`_. Once a
``Response`` object has been created, it may be useful to allow other elements
in the system to modify it (e.g. add some cache headers) before it's actually
used. To make this possible, the Symfony2 kernel throws an event -
``kernel.response``. Here's how it works:

* A *listener* (PHP object) tells a central *dispatcher* object that it wants
  to listen to the ``kernel.response`` event;

* At some point, the Symfony2 kernel tells the *dispatcher* object to dispatch
  the ``kernel.response`` event, passing with it an ``Event`` object that has
  access to the ``Response`` object;

* The dispatcher notifies (i.e. calls a method on) all listeners of the
  ``kernel.response`` event, allowing each of them to make modifications to
  the ``Response`` object.

.. index::
   single: Event Dispatcher; Events

.. _event_dispatcher:

Events
~~~~~~

When an event is dispatched, it's identified by a unique name (e.g.
``kernel.response``), which any number of listeners might be listening to. An
:class:`Symfony\\Component\\EventDispatcher\\Event` instance is also created
and passed to all of the listeners. As you'll see later, the ``Event`` object
itself often contains data about the event being dispatched.

.. index::
   pair: Event Dispatcher; Naming conventions

Naming Conventions
..................

The unique event name can be any string, but optionally follows a few simple
naming conventions:

* use only lowercase letters, numbers, dots (``.``), and underscores (``_``);

* prefix names with a namespace followed by a dot (e.g. ``kernel.``);

* end names with a verb that indicates what action is being taken (e.g.
  ``request``).

Here are some examples of good event names:

* ``kernel.response``
* ``form.pre_set_data``

.. index::
   single: Event Dispatcher; Event Subclasses

Event Names and Event Objects
.............................

When the dispatcher notifies listeners, it passes an actual ``Event`` object
to those listeners. The base ``Event`` class is very simple: it contains a
method for stopping :ref:`event
propagation<event_dispatcher-event-propagation>`, but not much else.

Often times, data about a specific event needs to be passed along with the
``Event`` object so that the listeners have needed information. In the case of
the ``kernel.response`` event, the ``Event`` object that's created and passed to
each listener is actually of type
:class:`Symfony\\Component\\HttpKernel\\Event\\FilterResponseEvent`, a
subclass of the base ``Event`` object. This class contains methods such as
``getResponse`` and ``setResponse``, allowing listeners to get or even replace
the ``Response`` object.

The moral of the story is this: when creating a listener to an event, the
``Event`` object that's passed to the listener may be a special subclass that
has additional methods for retrieving information from and responding to the
event.

The Dispatcher
~~~~~~~~~~~~~~

The dispatcher is the central object of the event dispatcher system. In
general, a single dispatcher is created, which maintains a registry of
listeners. When an event is dispatched via the dispatcher, it notifies all
listeners registered with that event.

.. code-block:: php

    use Symfony\Component\EventDispatcher\EventDispatcher;

    $dispatcher = new EventDispatcher();

.. index::
   single: Event Dispatcher; Listeners

Connecting Listeners
~~~~~~~~~~~~~~~~~~~~

To take advantage of an existing event, you need to connect a listener to the
dispatcher so that it can be notified when the event is dispatched. A call to
the dispatcher ``addListener()`` method associates any valid PHP callable to
an event:

.. code-block:: php

    $listener = new AcmeListener();
    $dispatcher->addListener('foo.action', array($listener, 'onFooAction'));

The ``addListener()`` method takes up to three arguments:

* The event name (string) that this listener wants to listen to;

* A PHP callable that will be notified when an event is thrown that it listens
  to;

* An optional priority integer (higher equals more important) that determines
  when a listener is triggered versus other listeners (defaults to ``0``). If
  two listeners have the same priority, they are executed in the order that
  they were added to the dispatcher.

.. note::

    A `PHP callable`_ is a PHP variable that can be used by the
    ``call_user_func()`` function and returns ``true`` when passed to the
    ``is_callable()`` function. It can be a ``\Closure`` instance, a string
    representing a function, or an array representing an object method or a
    class method.

    So far, you've seen how PHP objects can be registered as listeners. You
    can also register PHP `Closures`_ as event listeners:

    .. code-block:: php

        use Symfony\Component\EventDispatcher\Event;

        $dispatcher->addListener('foo.action', function (Event $event) {
            // will be executed when the foo.action event is dispatched
        });

Once a listener is registered with the dispatcher, it waits until the event is
notified. In the above example, when the ``foo.action`` event is dispatched,
the dispatcher calls the ``AcmeListener::onFooAction`` method and passes the
``Event`` object as the single argument:

.. code-block:: php

    use Symfony\Component\EventDispatcher\Event;

    class AcmeListener
    {
        // ...

        public function onFooAction(Event $event)
        {
            // do something
        }
    }

.. tip::

    If you use the Symfony2 MVC framework, listeners can be registered via
    your :ref:`configuration <dic-tags-kernel-event-listener>`. As an added
    bonus, the listener objects are instantiated only when needed.

In many cases, a special ``Event`` subclass that's specific to the given event
is passed to the listener. This gives the listener access to special
information about the event. Check the documentation or implementation of each
event to determine the exact ``Symfony\Component\EventDispatcher\Event``
instance that's being passed. For example, the ``kernel.event`` event passes an
instance of ``Symfony\Component\HttpKernel\Event\FilterResponseEvent``:

.. code-block:: php

    use Symfony\Component\HttpKernel\Event\FilterResponseEvent

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $response = $event->getResponse();
        $request = $event->getRequest();

        // ...
    }

.. _event_dispatcher-closures-as-listeners:

.. index::
   single: Event Dispatcher; Creating and Dispatching an Event

Creating and Dispatching an Event
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In addition to registering listeners with existing events, you can create and
throw your own events. This is useful when creating third-party libraries and
also when you want to keep different components of your own system flexible
and decoupled.

The Static ``Events`` Class
...........................

Suppose you want to create a new Event - ``store.order`` - that is dispatched
each time an order is created inside your application. To keep things
organized, start by creating a ``StoreEvents`` class inside your application
that serves to define and document your event:

.. code-block:: php

    namespace Acme\StoreBundle;

    final class StoreEvents
    {
        /**
         * The store.order event is thrown each time an order is created
         * in the system.
         *
         * The event listener receives an Acme\StoreBundle\Event\FilterOrderEvent
         * instance.
         *
         * @var string
         */
        const onStoreOrder = 'store.order';
    }

Notice that this class doesn't actually *do* anything. The purpose of the
``StoreEvents`` class is just to be a location where information about common
events can be centralized. Notice also that a special ``FilterOrderEvent``
class will be passed to each listener of this event.

Creating an Event object
........................

Later, when you dispatch this new event, you'll create an ``Event`` instance
and pass it to the dispatcher. The dispatcher then passes this same instance
to each of the listeners of the event. If you don't need to pass any
information to your listeners, you can use the default
``Symfony\Component\EventDispatcher\Event`` class. Most of the time, however,
you *will* need to pass information about the event to each listener. To
accomplish this, you'll create a new class that extends
``Symfony\Component\EventDispatcher\Event``.

In this example, each listener will need access to some pretend ``Order``
object. Create an ``Event`` class that makes this possible:

.. code-block:: php

    namespace Acme\StoreBundle\Event;

    use Symfony\Component\EventDispatcher\Event;
    use Acme\StoreBundle\Order;

    class FilterOrderEvent extends Event
    {
        protected $order;

        public function __construct(Order $order)
        {
            $this->order = $order;
        }

        public function getOrder()
        {
            return $this->order;
        }
    }

Each listener now has access to the ``Order`` object via the ``getOrder`` 
method.

Dispatch the Event
..................

The :method:`Symfony\\Component\\EventDispatcher\\EventDispatcher::dispatch`
method notifies all listeners of the given event. It takes two arguments: the
name of the event to dispatch and the ``Event`` instance to pass to each
listener of that event:

.. code-block:: php

    use Acme\StoreBundle\StoreEvents;
    use Acme\StoreBundle\Order;
    use Acme\StoreBundle\Event\FilterOrderEvent;

    // the order is somehow created or retrieved
    $order = new Order();
    // ...

    // create the FilterOrderEvent and dispatch it
    $event = new FilterOrderEvent($order);
    $dispatcher->dispatch(StoreEvents::onStoreOrder, $event);

Notice that the special ``FilterOrderEvent`` object is created and passed to
the ``dispatch`` method. Now, any listener to the ``store.order`` event will
receive the ``FilterOrderEvent`` and have access to the ``Order`` object via
the ``getOrder`` method:

.. code-block:: php

    // some listener class that's been registered for onStoreOrder
    use Acme\StoreBundle\Event\FilterOrderEvent;

    public function onStoreOrder(FilterOrderEvent $event)
    {
        $order = $event->getOrder();
        // do something to or with the order
    }

Passing along the Event Dispatcher Object
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you have a look at the ``EventDispatcher`` class, you will notice that the
class does not act as a Singleton (there is no ``getInstance()`` static method).
That is intentional, as you might want to have several concurrent event
dispatchers in a single PHP request. But it also means that you need a way to
pass the dispatcher to the objects that need to connect or notify events.

The best practice is to inject the event dispatcher object into your objects,
aka dependency injection.

You can use constructor injection::

    class Foo
    {
        protected $dispatcher = null;

        public function __construct(EventDispatcher $dispatcher)
        {
            $this->dispatcher = $dispatcher;
        }
    }

Or setter injection::

    class Foo
    {
        protected $dispatcher = null;

        public function setEventDispatcher(EventDispatcher $dispatcher)
        {
            $this->dispatcher = $dispatcher;
        }
    }

Choosing between the two is really a matter of taste. Many tend to prefer the
constructor injection as the objects are fully initialized at construction
time. But when you have a long list of dependencies, using setter injection
can be the way to go, especially for optional dependencies.

.. tip::

    If you use dependency injection like we did in the two examples above, you
    can then use the `Symfony2 Dependency Injection component`_ to elegantly
    manage the injection of the ``event_dispatcher`` service for these objects.

        .. code-block:: yaml

            # src/Acme/HelloBundle/Resources/config/services.yml
            services:
                foo_service:
                    class: Acme/HelloBundle/Foo/FooService
                    arguments: [@event_dispatcher]
            
.. index::
   single: Event Dispatcher; Event subscribers

Using Event Subscribers
~~~~~~~~~~~~~~~~~~~~~~~

The most common way to listen to an event is to register an *event listener*
with the dispatcher. This listener can listen to one or more events and is
notified each time those events are dispatched.

Another way to listen to events is via an *event subscriber*. An event
subscriber is a PHP class that's able to tell the dispatcher exactly which
events it should subscribe to. It implements the
:class:`Symfony\\Component\\EventDispatcher\\EventSubscriberInterface`
interface, which requires a single static method called
``getSubscribedEvents``. Take the following example of a subscriber that
subscribes to the ``kernel.response`` and ``store.order`` events:

.. code-block:: php

    namespace Acme\StoreBundle\Event;

    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

    class StoreSubscriber implements EventSubscriberInterface
    {
        static public function getSubscribedEvents()
        {
            return array(
                'kernel.response' => 'onKernelResponse',
                'store.order'     => 'onStoreOrder',
            );
        }

        public function onKernelResponse(FilterResponseEvent $event)
        {
            // ...
        }

        public function onStoreOrder(FilterOrderEvent $event)
        {
            // ...
        }
    }

This is very similar to a listener class, except that the class itself can
tell the dispatcher which events it should listen to. To register a subscriber
with the dispatcher, use the
:method:`Symfony\\Component\\EventDispatcher\\EventDispatcher::addSubscriber`
method:

.. code-block:: php

    use Acme\StoreBundle\Event\StoreSubscriber;

    $subscriber = new StoreSubscriber();
    $dispatcher->addSubscriber($subscriber);

The dispatcher will automatically register the subscriber for each event
returned by the ``getSubscribedEvents`` method. This method returns an array
indexed by event names and whose values are either the method name to call or
an array composed of the method name to call and a priority.

.. tip::

    If you use the Symfony2 MVC framework, subscribers can be registered via
    your :ref:`configuration <dic-tags-kernel-event-subscriber>`. As an added
    bonus, the subscriber objects are instantiated only when needed.

.. index::
   single: Event Dispatcher; Stopping event flow

.. _event_dispatcher-event-propagation:

Stopping Event Flow/Propagation
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In some cases, it may make sense for a listener to prevent any other listeners
from being called. In other words, the listener needs to be able to tell the
dispatcher to stop all propagation of the event to future listeners (i.e. to
not notify any more listeners). This can be accomplished from inside a
listener via the
:method:`Symfony\\Component\\EventDispatcher\\Event::stopPropagation` method:

.. code-block:: php

   use Acme\StoreBundle\Event\FilterOrderEvent;

   public function onStoreOrder(FilterOrderEvent $event)
   {
       // ...

       $event->stopPropagation();
   }

Now, any listeners to ``store.order`` that have not yet been called will *not*
be called.

.. index::
   single: Profiler

Profiler
--------

When enabled, the Symfony2 profiler collects useful information about each
request made to your application and store them for later analysis. Use the
profiler in the development environment to help you to debug your code and
enhance performance; use it in the production environment to explore problems
after the fact.

You rarely have to deal with the profiler directly as Symfony2 provides
visualizer tools like the Web Debug Toolbar and the Web Profiler. If you use
the Symfony2 Standard Edition, the profiler, the web debug toolbar, and the
web profiler are all already configured with sensible settings.

.. note::

    The profiler collects information for all requests (simple requests,
    redirects, exceptions, Ajax requests, ESI requests; and for all HTTP
    methods and all formats). It means that for a single URL, you can have
    several associated profiling data (one per external request/response
    pair).

.. index::
   single: Profiler; Visualizing

Visualizing Profiling Data
~~~~~~~~~~~~~~~~~~~~~~~~~~

Using the Web Debug Toolbar
...........................

In the development environment, the web debug toolbar is available at the
bottom of all pages. It displays a good summary of the profiling data that
gives you instant access to a lot of useful information when something does
not work as expected.

If the summary provided by the Web Debug Toolbar is not enough, click on the
token link (a string made of 13 random characters) to access the Web Profiler.

.. note::

    If the token is not clickable, it means that the profiler routes are not
    registered (see below for configuration information).

Analyzing Profiling data with the Web Profiler
..............................................

The Web Profiler is a visualization tool for profiling data that you can use
in development to debug your code and enhance performance; but it can also be
used to explore problems that occur in production. It exposes all information
collected by the profiler in a web interface.

.. index::
   single: Profiler; Using the profiler service

Accessing the Profiling information
...................................

You don't need to use the default visualizer to access the profiling
information. But how can you retrieve profiling information for a specific
request after the fact? When the profiler stores data about a Request, it also
associates a token with it; this token is available in the ``X-Debug-Token``
HTTP header of the Response::

    $profile = $container->get('profiler')->loadProfileFromResponse($response);

    $profile = $container->get('profiler')->loadProfile($token);

.. tip::

    When the profiler is enabled but not the web debug toolbar, or when you
    want to get the token for an Ajax request, use a tool like Firebug to get
    the value of the ``X-Debug-Token`` HTTP header.

Use the ``find()`` method to access tokens based on some criteria::

    // get the latest 10 tokens
    $tokens = $container->get('profiler')->find('', '', 10);

    // get the latest 10 tokens for all URL containing /admin/
    $tokens = $container->get('profiler')->find('', '/admin/', 10);

    // get the latest 10 tokens for local requests
    $tokens = $container->get('profiler')->find('127.0.0.1', '', 10);

If you want to manipulate profiling data on a different machine than the one
where the information were generated, use the ``export()`` and ``import()``
methods::

    // on the production machine
    $profile = $container->get('profiler')->loadProfile($token);
    $data = $profiler->export($profile);

    // on the development machine
    $profiler->import($data);

.. index::
   single: Profiler; Visualizing

Configuration
.............

The default Symfony2 configuration comes with sensible settings for the
profiler, the web debug toolbar, and the web profiler. Here is for instance
the configuration for the development environment:

.. configuration-block::

    .. code-block:: yaml

        # load the profiler
        framework:
            profiler: { only_exceptions: false }

        # enable the web profiler
        web_profiler:
            toolbar: true
            intercept_redirects: true
            verbose: true

    .. code-block:: xml

        <!-- xmlns:webprofiler="http://symfony.com/schema/dic/webprofiler" -->
        <!-- xsi:schemaLocation="http://symfony.com/schema/dic/webprofiler http://symfony.com/schema/dic/webprofiler/webprofiler-1.0.xsd"> -->

        <!-- load the profiler -->
        <framework:config>
            <framework:profiler only-exceptions="false" />
        </framework:config>

        <!-- enable the web profiler -->
        <webprofiler:config
            toolbar="true"
            intercept-redirects="true"
            verbose="true"
        />

    .. code-block:: php

        // load the profiler
        $container->loadFromExtension('framework', array(
            'profiler' => array('only-exceptions' => false),
        ));

        // enable the web profiler
        $container->loadFromExtension('web_profiler', array(
            'toolbar' => true,
            'intercept-redirects' => true,
            'verbose' => true,
        ));

When ``only-exceptions`` is set to ``true``, the profiler only collects data
when an exception is thrown by the application.

When ``intercept-redirects`` is set to ``true``, the web profiler intercepts
the redirects and gives you the opportunity to look at the collected data
before following the redirect.

When ``verbose`` is set to ``true``, the Web Debug Toolbar displays a lot of
information. Setting ``verbose`` to ``false`` hides some secondary information
to make the toolbar shorter.

If you enable the web profiler, you also need to mount the profiler routes:

.. configuration-block::

    .. code-block:: yaml

        _profiler:
            resource: @WebProfilerBundle/Resources/config/routing/profiler.xml
            prefix:   /_profiler

    .. code-block:: xml

        <import resource="@WebProfilerBundle/Resources/config/routing/profiler.xml" prefix="/_profiler" />

    .. code-block:: php

        $collection->addCollection($loader->import("@WebProfilerBundle/Resources/config/routing/profiler.xml"), '/_profiler');

As the profiler adds some overhead, you might want to enable it only under
certain circumstances in the production environment. The ``only-exceptions``
settings limits profiling to 500 pages, but what if you want to get
information when the client IP comes from a specific address, or for a limited
portion of the website? You can use a request matcher:

.. configuration-block::

    .. code-block:: yaml

        # enables the profiler only for request coming for the 192.168.0.0 network
        framework:
            profiler:
                matcher: { ip: 192.168.0.0/24 }

        # enables the profiler only for the /admin URLs
        framework:
            profiler:
                matcher: { path: "^/admin/" }

        # combine rules
        framework:
            profiler:
                matcher: { ip: 192.168.0.0/24, path: "^/admin/" }

        # use a custom matcher instance defined in the "custom_matcher" service
        framework:
            profiler:
                matcher: { service: custom_matcher }

    .. code-block:: xml

        <!-- enables the profiler only for request coming for the 192.168.0.0 network -->
        <framework:config>
            <framework:profiler>
                <framework:matcher ip="192.168.0.0/24" />
            </framework:profiler>
        </framework:config>

        <!-- enables the profiler only for the /admin URLs -->
        <framework:config>
            <framework:profiler>
                <framework:matcher path="^/admin/" />
            </framework:profiler>
        </framework:config>

        <!-- combine rules -->
        <framework:config>
            <framework:profiler>
                <framework:matcher ip="192.168.0.0/24" path="^/admin/" />
            </framework:profiler>
        </framework:config>

        <!-- use a custom matcher instance defined in the "custom_matcher" service -->
        <framework:config>
            <framework:profiler>
                <framework:matcher service="custom_matcher" />
            </framework:profiler>
        </framework:config>

    .. code-block:: php

        // enables the profiler only for request coming for the 192.168.0.0 network
        $container->loadFromExtension('framework', array(
            'profiler' => array(
                'matcher' => array('ip' => '192.168.0.0/24'),
            ),
        ));

        // enables the profiler only for the /admin URLs
        $container->loadFromExtension('framework', array(
            'profiler' => array(
                'matcher' => array('path' => '^/admin/'),
            ),
        ));

        // combine rules
        $container->loadFromExtension('framework', array(
            'profiler' => array(
                'matcher' => array('ip' => '192.168.0.0/24', 'path' => '^/admin/'),
            ),
        ));

        # use a custom matcher instance defined in the "custom_matcher" service
        $container->loadFromExtension('framework', array(
            'profiler' => array(
                'matcher' => array('service' => 'custom_matcher'),
            ),
        ));

Learn more from the Cookbook
----------------------------

* :doc:`/cookbook/testing/profiling`
* :doc:`/cookbook/profiler/data_collector`
* :doc:`/cookbook/event_dispatcher/class_extension`
* :doc:`/cookbook/event_dispatcher/method_behavior`

.. _Observer: http://en.wikipedia.org/wiki/Observer_pattern
.. _`Symfony2 HttpKernel component`: https://github.com/symfony/HttpKernel
.. _Closures: http://php.net/manual/en/functions.anonymous.php
.. _`Symfony2 Dependency Injection component`: https://github.com/symfony/DependencyInjection
.. _PHP callable: http://www.php.net/manual/en/language.pseudo-types.php#language.types.callback
