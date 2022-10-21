.. index::
   single: EventDispatcher
   single: Components; EventDispatcher

The EventDispatcher Component
=============================

    The EventDispatcher component provides tools that allow your application
    components to communicate with each other by dispatching events and
    listening to them.

Introduction
------------

Object-oriented code has gone a long way to ensuring code extensibility.
By creating classes that have well-defined responsibilities, your code becomes
more flexible and a developer can extend them with subclasses to modify
their behaviors. But if they want to share the changes with other developers
who have also made their own subclasses, code inheritance is no longer the
answer.

Consider the real-world example where you want to provide a plugin system
for your project. A plugin should be able to add methods, or do something
before or after a method is executed, without interfering with other plugins.
This is not an easy problem to solve with single inheritance, and even if
multiple inheritance was possible with PHP, it comes with its own drawbacks.

The Symfony EventDispatcher component implements the `Mediator`_ and `Observer`_
design patterns to make all these things possible and to make your projects
truly extensible.

Take an example from :doc:`the HttpKernel component </components/http_kernel>`.
Once a ``Response`` object has been created, it may be useful to allow other
elements in the system to modify it (e.g. add some cache headers) before
it's actually used. To make this possible, the Symfony kernel throws an
event - ``kernel.response``. Here's how it works:

* A *listener* (PHP object) tells a central *dispatcher* object that it
  wants to listen to the ``kernel.response`` event;

* At some point, the Symfony kernel tells the *dispatcher* object to dispatch
  the ``kernel.response`` event, passing with it an ``Event`` object that
  has access to the ``Response`` object;

* The dispatcher notifies (i.e. calls a method on) all listeners of the
  ``kernel.response`` event, allowing each of them to make modifications
  to the ``Response`` object.

.. index::
   single: EventDispatcher; Events

Installation
------------

.. code-block:: terminal

    $ composer require symfony/event-dispatcher

.. include:: /components/require_autoload.rst.inc

Usage
-----

.. seealso::

    This article explains how to use the EventDispatcher features as an
    independent component in any PHP application. Read the :doc:`/event_dispatcher`
    article to learn about how to use it in Symfony applications.

Events
~~~~~~

When an event is dispatched, it's identified by a unique name (e.g.
``kernel.response``), which any number of listeners might be listening to.
An :class:`Symfony\\Contracts\\EventDispatcher\\Event` instance is also
created and passed to all of the listeners. As you'll see later, the ``Event``
object itself often contains data about the event being dispatched.

.. index::
   pair: EventDispatcher; Naming conventions

Naming Conventions
..................

The unique event name can be any string, but optionally follows a few
naming conventions:

* Use only lowercase letters, numbers, dots (``.``) and underscores (``_``);
* Prefix names with a namespace followed by a dot (e.g. ``order.*``, ``user.*``);
* End names with a verb that indicates what action has been taken (e.g.
  ``order.placed``).

.. index::
   single: EventDispatcher; Event subclasses

Event Names and Event Objects
.............................

When the dispatcher notifies listeners, it passes an actual ``Event`` object
to those listeners. The base ``Event`` class contains a method for stopping
:ref:`event propagation <event_dispatcher-event-propagation>`, but not much
else.

.. seealso::

    Read ":doc:`/components/event_dispatcher/generic_event`" for more
    information about this base event object.

Often times, data about a specific event needs to be passed along with the
``Event`` object so that the listeners have the needed information. In such
case, a special subclass that has additional methods for retrieving and
overriding information can be passed when dispatching an event. For example,
the ``kernel.response`` event uses a
:class:`Symfony\\Component\\HttpKernel\\Event\\ResponseEvent`, which
contains methods to get and even replace the ``Response`` object.

The Dispatcher
~~~~~~~~~~~~~~

The dispatcher is the central object of the event dispatcher system. In
general, a single dispatcher is created, which maintains a registry of
listeners. When an event is dispatched via the dispatcher, it notifies all
listeners registered with that event::

    use Symfony\Component\EventDispatcher\EventDispatcher;

    $dispatcher = new EventDispatcher();

.. index::
   single: EventDispatcher; Listeners

Connecting Listeners
~~~~~~~~~~~~~~~~~~~~

To take advantage of an existing event, you need to connect a listener to
the dispatcher so that it can be notified when the event is dispatched.
A call to the dispatcher's ``addListener()`` method associates any valid
PHP callable to an event::

    $listener = new AcmeListener();
    $dispatcher->addListener('acme.foo.action', [$listener, 'onFooAction']);

The ``addListener()`` method takes up to three arguments:

#. The event name (string) that this listener wants to listen to;
#. A PHP callable that will be executed when the specified event is dispatched;
#. An optional priority, defined as a positive or negative integer (defaults to
   ``0``). The higher the number, the earlier the listener is called. If two
   listeners have the same priority, they are executed in the order that they
   were added to the dispatcher.

.. note::

    A `PHP callable`_ is a PHP variable that can be used by the
    ``call_user_func()`` function and returns ``true`` when passed to the
    ``is_callable()`` function. It can be a ``\Closure`` instance, an object
    implementing an ``__invoke()`` method (which is what closures are in fact),
    a string representing a function or an array representing an object
    method or a class method.

    So far, you've seen how PHP objects can be registered as listeners.
    You can also register PHP `Closures`_ as event listeners::

        use Symfony\Contracts\EventDispatcher\Event;

        $dispatcher->addListener('acme.foo.action', function (Event $event) {
            // will be executed when the acme.foo.action event is dispatched
        });

Once a listener is registered with the dispatcher, it waits until the event
is notified. In the above example, when the ``acme.foo.action`` event is dispatched,
the dispatcher calls the ``AcmeListener::onFooAction()`` method and passes
the ``Event`` object as the single argument::

    use Symfony\Contracts\EventDispatcher\Event;

    class AcmeListener
    {
        // ...

        public function onFooAction(Event $event)
        {
            // ... do something
        }
    }

The ``$event`` argument is the event object that was passed when dispatching the
event. In many cases, a special event subclass is passed with extra
information. You can check the documentation or implementation of each event to
determine which instance is passed.

.. sidebar:: Registering Event Listeners and Subscribers in the Service Container

    Registering service definitions and tagging them with the
    ``kernel.event_listener`` and ``kernel.event_subscriber`` tags is not enough
    to enable the event listeners and event subscribers. You must also register
    a compiler pass called ``RegisterListenersPass()`` in the container builder::

        use Symfony\Component\DependencyInjection\ContainerBuilder;
        use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
        use Symfony\Component\DependencyInjection\Reference;
        use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
        use Symfony\Component\EventDispatcher\EventDispatcher;

        $containerBuilder = new ContainerBuilder(new ParameterBag());
        // register the compiler pass that handles the 'kernel.event_listener'
        // and 'kernel.event_subscriber' service tags
        $containerBuilder->addCompilerPass(new RegisterListenersPass());

        $containerBuilder->register('event_dispatcher', EventDispatcher::class);

        // registers an event listener
        $containerBuilder->register('listener_service_id', \AcmeListener::class)
            ->addTag('kernel.event_listener', [
                'event' => 'acme.foo.action',
                'method' => 'onFooAction',
            ]);

        // registers an event subscriber
        $containerBuilder->register('subscriber_service_id', \AcmeSubscriber::class)
            ->addTag('kernel.event_subscriber');

    ``RegisterListenersPass`` resolves aliased class names which for instance
    allows to refer to an event via the fully qualified class name (FQCN) of the
    event class. The pass will read the alias mapping from a dedicated container
    parameter. This parameter can be extended by registering another compiler pass,
    ``AddEventAliasesPass``::

        use Symfony\Component\DependencyInjection\Compiler\PassConfig;
        use Symfony\Component\DependencyInjection\ContainerBuilder;
        use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
        use Symfony\Component\DependencyInjection\Reference;
        use Symfony\Component\EventDispatcher\DependencyInjection\AddEventAliasesPass;
        use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
        use Symfony\Component\EventDispatcher\EventDispatcher;

        $containerBuilder = new ContainerBuilder(new ParameterBag());
        $containerBuilder->addCompilerPass(new AddEventAliasesPass([
            \AcmeFooActionEvent::class => 'acme.foo.action',
        ]));
        $containerBuilder->addCompilerPass(new RegisterListenersPass(), PassConfig::TYPE_BEFORE_REMOVING);

        $containerBuilder->register('event_dispatcher', EventDispatcher::class);

        // registers an event listener
        $containerBuilder->register('listener_service_id', \AcmeListener::class)
            ->addTag('kernel.event_listener', [
                // will be translated to 'acme.foo.action' by RegisterListenersPass.
                'event' => \AcmeFooActionEvent::class,
                'method' => 'onFooAction',
            ]);

    .. note::

        Note that ``AddEventAliasesPass`` has to be processed before ``RegisterListenersPass``.

    The listeners pass assumes that the event dispatcher's service
    id is ``event_dispatcher``, that event listeners are tagged with the
    ``kernel.event_listener`` tag, that event subscribers are tagged
    with the ``kernel.event_subscriber`` tag and that the alias mapping is
    stored as parameter ``event_dispatcher.event_aliases``.

.. _event_dispatcher-closures-as-listeners:

.. index::
   single: EventDispatcher; Creating and dispatching an event

Creating and Dispatching an Event
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In addition to registering listeners with existing events, you can create
and dispatch your own events. This is useful when creating third-party
libraries and also when you want to keep different components of your own
system flexible and decoupled.

.. _creating-an-event-object:

Creating an Event Class
.......................

Suppose you want to create a new event - ``order.placed`` - that is dispatched
each time a customer orders a product with your application. When dispatching
this event, you'll pass a custom event instance that has access to the placed
order. Start by creating this custom event class and documenting it::

    namespace Acme\Store\Event;

    use Acme\Store\Order;
    use Symfony\Contracts\EventDispatcher\Event;

    /**
     * The order.placed event is dispatched each time an order is created
     * in the system.
     */
    class OrderPlacedEvent extends Event
    {
        public const NAME = 'order.placed';

        protected $order;

        public function __construct(Order $order)
        {
            $this->order = $order;
        }

        public function getOrder(): Order
        {
            return $this->order;
        }
    }

Each listener now has access to the order via the ``getOrder()`` method.

.. note::

    If you don't need to pass any additional data to the event listeners, you
    can also use the default
    :class:`Symfony\\Contracts\\EventDispatcher\\Event` class. In such case,
    you can document the event and its name in a generic ``StoreEvents`` class,
    similar to the :class:`Symfony\\Component\\HttpKernel\\KernelEvents`
    class.

Dispatch the Event
..................

The :method:`Symfony\\Component\\EventDispatcher\\EventDispatcher::dispatch`
method notifies all listeners of the given event. It takes two arguments:
the ``Event`` instance to pass to each listener of that event and the name
of the event to dispatch::

    use Acme\Store\Event\OrderPlacedEvent;
    use Acme\Store\Order;

    // the order is somehow created or retrieved
    $order = new Order();
    // ...

    // creates the OrderPlacedEvent and dispatches it
    $event = new OrderPlacedEvent($order);
    $dispatcher->dispatch($event, OrderPlacedEvent::NAME);

Notice that the special ``OrderPlacedEvent`` object is created and passed to
the ``dispatch()`` method. Now, any listener to the ``order.placed``
event will receive the ``OrderPlacedEvent``.

.. index::
   single: EventDispatcher; Event subscribers

.. _event_dispatcher-using-event-subscribers:

Using Event Subscribers
~~~~~~~~~~~~~~~~~~~~~~~

The most common way to listen to an event is to register an *event listener*
with the dispatcher. This listener can listen to one or more events and
is notified each time those events are dispatched.

Another way to listen to events is via an *event subscriber*. An event
subscriber is a PHP class that's able to tell the dispatcher exactly which
events it should subscribe to. It implements the
:class:`Symfony\\Component\\EventDispatcher\\EventSubscriberInterface`
interface, which requires a single static method called
:method:`Symfony\\Component\\EventDispatcher\\EventSubscriberInterface::getSubscribedEvents`.
Take the following example of a subscriber that subscribes to the
``kernel.response`` and ``order.placed`` events::

    namespace Acme\Store\Event;

    use Acme\Store\Event\OrderPlacedEvent;
    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Symfony\Component\HttpKernel\Event\ResponseEvent;
    use Symfony\Component\HttpKernel\KernelEvents;

    class StoreSubscriber implements EventSubscriberInterface
    {
        public static function getSubscribedEvents()
        {
            return [
                KernelEvents::RESPONSE => [
                    ['onKernelResponsePre', 10],
                    ['onKernelResponsePost', -10],
                ],
                OrderPlacedEvent::NAME => 'onStoreOrder',
            ];
        }

        public function onKernelResponsePre(ResponseEvent $event)
        {
            // ...
        }

        public function onKernelResponsePost(ResponseEvent $event)
        {
            // ...
        }

        public function onStoreOrder(OrderPlacedEvent $event)
        {
            // ...
        }
    }

This is very similar to a listener class, except that the class itself can
tell the dispatcher which events it should listen to. To register a subscriber
with the dispatcher, use the
:method:`Symfony\\Component\\EventDispatcher\\EventDispatcher::addSubscriber`
method::

    use Acme\Store\Event\StoreSubscriber;
    // ...

    $subscriber = new StoreSubscriber();
    $dispatcher->addSubscriber($subscriber);

The dispatcher will automatically register the subscriber for each event
returned by the ``getSubscribedEvents()`` method. This method returns an array
indexed by event names and whose values are either the method name to call
or an array composed of the method name to call and a priority (a positive or
negative integer that defaults to ``0``).

The example above shows how to register several listener methods for the same
event in subscriber and also shows how to pass the priority of each listener
method. The higher the number, the earlier the method is called. In the above
example, when the ``kernel.response`` event is triggered, the methods
``onKernelResponsePre()`` and ``onKernelResponsePost()`` are called in that
order.

.. index::
   single: EventDispatcher; Stopping event flow

.. _event_dispatcher-event-propagation:

Stopping Event Flow/Propagation
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In some cases, it may make sense for a listener to prevent any other listeners
from being called. In other words, the listener needs to be able to tell
the dispatcher to stop all propagation of the event to future listeners
(i.e. to not notify any more listeners). This can be accomplished from
inside a listener via the
:method:`Symfony\\Contracts\\EventDispatcher\\Event::stopPropagation` method::

    use Acme\Store\Event\OrderPlacedEvent;

    public function onStoreOrder(OrderPlacedEvent $event)
    {
        // ...

        $event->stopPropagation();
    }

Now, any listeners to ``order.placed`` that have not yet been called will
*not* be called.

It is possible to detect if an event was stopped by using the
:method:`Symfony\\Contracts\\EventDispatcher\\Event::isPropagationStopped`
method which returns a boolean value::

    // ...
    $dispatcher->dispatch($event, 'foo.event');
    if ($event->isPropagationStopped()) {
        // ...
    }

.. index::
   single: EventDispatcher; EventDispatcher aware events and listeners

.. _event_dispatcher-dispatcher-aware-events:

EventDispatcher Aware Events and Listeners
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The ``EventDispatcher`` always passes the dispatched event, the event's
name and a reference to itself to the listeners. This can lead to some advanced
applications of the ``EventDispatcher`` including dispatching other events inside
listeners, chaining events or even lazy loading listeners into the dispatcher object.

.. index::
   single: EventDispatcher; Event name introspection

.. _event_dispatcher-event-name-introspection:

Event Name Introspection
~~~~~~~~~~~~~~~~~~~~~~~~

The ``EventDispatcher`` instance, as well as the name of the event that
is dispatched, are passed as arguments to the listener::

    use Symfony\Contracts\EventDispatcher\Event;
    use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

    class MyListener
    {
        public function myEventListener(Event $event, string $eventName, EventDispatcherInterface $dispatcher)
        {
            // ... do something with the event name
        }
    }

Other Dispatchers
-----------------

Besides the commonly used ``EventDispatcher``, the component comes
with some other dispatchers:

* :doc:`/components/event_dispatcher/immutable_dispatcher`
* :doc:`/components/event_dispatcher/traceable_dispatcher`

Learn More
----------

.. toctree::
    :maxdepth: 1
    :glob:

    /components/event_dispatcher/*
    /event_dispatcher/*

* :ref:`The kernel.event_listener tag <dic-tags-kernel-event-listener>`
* :ref:`The kernel.event_subscriber tag <dic-tags-kernel-event-subscriber>`

.. _Mediator: https://en.wikipedia.org/wiki/Mediator_pattern
.. _Observer: https://en.wikipedia.org/wiki/Observer_pattern
.. _Closures: https://www.php.net/manual/en/functions.anonymous.php
.. _PHP callable: https://www.php.net/manual/en/language.types.callable.php
