.. index::
   single: Event Dispatcher

The Event Dispatcher
====================

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

Take a simple example from the `Symfony2 HttpKernel component`_. Once a ``Response``
object has been created, it may be useful to allow other elements in the
system to modify it (e.g. add some cache headers) before it's actually
used. To make this possible, the Symfony2 kernel throws an event - ``onCoreResponse``.
Here's how it work:

* A *listener* (PHP object) tells a central *dispatcher* object that it
  wants to listen to the ``onCoreResponse`` event;

* At some point, the Symfony2 kernel tells the *dispatcher* object to dispatch
  the ``onCoreResponse`` event, passing with it an ``Event`` object that
  has access to the ``Response`` object;

* The dispatcher notifies (i.e. calls a method on) all listeners of the
  ``onCoreResponse`` event, allowing each of them to make any modification
  to the ``Response`` object.

.. index::
   single: Event Dispatcher; Events

Events
------

When an event is dispatched, it's identified by a unique name (e.g. ``onCoreResponse``),
which any number of listeners might be listening to. A
:class:`Symfony\\Component\\EventDispatcher\\Event` instance is also created
and passed to all of the listeners. As you'll see later, the ``Event`` object
itself often contains data about the event being dispatched.

.. index::
   pair: Event Dispatcher; Naming conventions

Naming Conventions
~~~~~~~~~~~~~~~~~~

The unique event name can be any string, but optionally follows a few simple
naming conventions:

* use only letters and numbers, written in lower camel case;

* prefix names with the word ``on`` followed by a namespace (e.g. ``Core``);

* end names with a verb that indicates what action is being taken (e.g. ``Request``).

Here are some examples of good event names:

* ``onCoreRequest``
* ``onAsseticWrite``

.. index::
   single: Event Dispatcher; Event Subclasses

Event Names and Event Objects
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When the dispatcher notifies listeners, it passes an actual ``Event`` object
to those listeners. The base ``Event`` class is very simple: it contains
a method for stopping :ref:`event propagation<event_dispatcher-event-propagation>`,
but not much else.

Often times, data about a specific event needs to be passed along with the
``Event`` object so that the listeners have needed information. In the case
of the ``onCoreResponse`` event, the ``Event`` object that's created and
passed to each listener is actually of type :class:`Symfony\\Component\\HttpKernel\\Event\\FilterResponseEvent`,
a subclass of the base ``Event`` object. This class contains methods such
as ``getResponse`` and ``setResponse``, allowing listeners to get or even
replace the ``Response`` object.

The moral of the story is this: when creating a listener to an event, the
``Event`` object that's passed to the listener may be a special subclass
that has additional methods for retrieving information from and responding
to the event.

The Dispatcher
--------------

The dispatcher is the central object of the event dispatcher system. In
general, a single dispatcher is created, which maintains a register of listeners.
When an event is dispatched via the dispatcher, it notifies all listeners
registered with that event.

.. code-block:: php

    use Symfony\Component\EventDispatcher\EventDispatcher;

    $dispatcher = new EventDispatcher();

.. index::
   single: Event Dispatcher; Listeners

Connecting Listeners
--------------------

To take advantage of an existing event, you need to connect a listener to
the dispatcher so that it can be notified when the event is dispatched.
A call to the dispatcher ``addListener()`` method associates a PHP object
(or :ref:`Closure<event_dispatcher-closures-as-listeners>`) to an event:

.. code-block:: php

    $myListener = new myListener();
    $dispatcher->addListener('onFooAction', $myListener);

The ``addListener()`` method takes up to three arguments:

* The event name (string) or event names (array of strings) that this listener
  wants to listen to;

* A PHP object (or :ref:`Closure<event_dispatcher-closures-as-listeners>`)
  that will be notified (i.e. a method called on it) when an event is thrown
  that it listens to;

* An optional priority integer (higher equals more important) that determines
  when a listener is triggered versus other listeners (defaults to ``0``). If
  two listeners have the same priority, they are executed in the order that
  they were added to the dispatcher.

Once a listener is registered with the dispatcher, it waits until the event is
notified. In the above example, when the ``onFooAction`` event is dispatched,
the dispatcher calls the ``myListener::onFooAction`` method and passes the
``Event`` object as the single argument:

.. code-block:: php

    use Symfony\Component\EventDispatcher\Event;

    class myListener
    {
        // ...
    
        public function onFooAction(Event $event)
        {
            // do something
        }
    }

The method named called on a listener object is always equivalent to the
name of the event (e.g. ``onFooAction``).

.. tip::

    If you use the Symfony2 MVC framework, listeners can be registered via
    your :ref:`configuration <dic-tags-kernel-listener>`. As an added bonus,
    the listener objects are instantiated only when needed.

In many cases, a special ``Event`` subclass that's specific to the given
event is passed to the listener. This gives the listener access to special
information about the event. Check the documentation or implementation of
each event to determine the exact ``Symfony\Component\EventDispatcher\Event``
instance that's being passed. For example, the ``onCoreResponse`` event
passes an instance of ``Symfony\Component\HttpKernel\Event\FilterResponseEvent``:

.. code-block:: php

    use Symfony\Component\HttpKernel\Event\FilterResponseEvent

    public function onCoreResponse(FilterResponseEvent $event)
    {
        $response = $event->getResponse();
        $request = $event->getRequest();

        // ...
    }

.. _event_dispatcher-closures-as-listeners:

.. sidebar:: Using Closures as Listeners

   So far, you've seen how PHP objects can be registered as listeners. You
   can also register PHP `Closures`_ as event listeners:
   
   .. code-block:: php
   
       use Symfony\Component\EventDispatcher\Event;
   
       $dispatcher->addListener('onFooAction', function(Event $event) {
           // will be executed when the onFooAction event is dispatched
       });

.. index::
   single: Event Dispatcher; Creating and Dispatching an Event

Creating and Dispatching an Event
---------------------------------

In addition to registering listeners with existing events, you can create
and throw your own events. This is useful when creating third-party libraries
and also when you want to keep different components of your own system flexible
and decoupled.

The Static ``Events`` Class
~~~~~~~~~~~~~~~~~~~~~~~~~~~

Suppose you want to create a new Event - ``onStoreOrder`` - that is dispatched
each time an order is created inside your application. To keep things organized,
start by creating an ``Events`` class inside your application that serves to
define and document your event:

.. code-block:: php

    namespace Acme\StoreBundle;
    
    final class Events
    {
        /**
         * The onStoreOrder event is thrown each time an order is created
         * in the system.
         * 
         * The event listener recieves an Acme\StoreBundle\Event\FilterOrderEvent
         * instance.
         *
         * @var string
         */
        const onStoreOrder = 'onStoreOrder';
    }

Notice that this class doesn't actually *do* anything. The purpose of the
``Events`` class is just to be a location where information about common
events can be centralized. Notice also that a special ``FilterOrderEvent``
class will be passed to each listener of this event.

Creating an Event object
~~~~~~~~~~~~~~~~~~~~~~~~

Later, when you dispatch this new event, you'll create an ``Event`` instance
and pass it to the dispatcher. The dispatcher then passes this same instance
to each of the listeners of the event. If you don't need to pass any information
to your listeners, you can use the default ``Symfony\Component\EventDispatcher\Event``
class. Most of the time, however, you *will* need to pass information about
the event to each listener. To accomplish this, you'll create a new class
that extends ``Symfony\Component\EventDispatcher\Event``.

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

Each listener now has access to to ``Order`` object via the ``getOrder``
method.

Dispatch the Event
~~~~~~~~~~~~~~~~~~

The :method:`Symfony\\Component\\EventDispatcher\\EventDispatcher::dispatch`
method notifies all listeners of the given event. It takes two arguments:
the name of the event to dispatch and the ``Event`` instance to pass to
each listener of that event:

.. code-block:: php

    use Acme\StoreBundle\Events;
    use Acme\StoreBundle\Order;
    use Acme\StoreBundle\Event\FilterOrderEvent;

    // the order is somehow created or retrieved
    $order = new Order();
    // ...
    
    // create the FilterOrderEvent and dispatch it
    $event = new FilterOrderEvent($order);
    $dispatcher->dispatch(Events::onStoreOrder, $event);

Notice that the special ``FilterOrderEvent`` object is created and passed
to the ``dispatch`` method. Now, any listener to the ``onStoreOrder`` event
will receive the ``FilterOrderEvent`` and have access to the ``Order`` object
via the ``getOrder`` method:

.. code-block:: php

    // some listener class that's been registered for onStoreOrder
    use Acme\StoreBundle\Event\FilterOrderEvent;

    public function onStoreOrder(FilterOrderEvent $event)
    {
        $order = $event->getOrder();
        // do something to or with the order
    }

Passing along the Event Dispatcher Object
-----------------------------------------

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
    manage these objects.

.. index::
   single: Event Dispatcher; Event subscribers

Using Event Subscribers
-----------------------

The most common way to listen to an event is to register an *event listener*
with the dispatcher. This listener can listen to one or more events and
is notified each time those events are dispatched.

Another way to listen to events is via an *event subscriber*. An event subscriber
is a PHP class that's able to tell the dispatcher exactly which events it should
subscribe to. It implements the :class:`Symfony\\Component\\EventDispatcher\\EventSubscriberInterface`
interface, which requires a single static method called ``getSubscribedEvents``.
Take the following example of a subscriber that subscribes to the ``onCoreResponse``
and ``onStoreOrder`` events:

.. code-block:: php

    namespace Acme\StoreBundle\Event;
    
    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

    class StoreSubscriber implements EventSubscriberInterface
    {
        static public function getSubscribedEvents()
        {
            return array('onCoreResponse', 'onStoreOrder');
        }

        public function onCoreResponse(FilterResponseEvent $event)
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
with the dispatcher, use the ``addSubscriberMethod`` method:

.. code-block:: php

    use Acme\StoreBundle\Event\StoreSubscriber;

    $subscriber = new StoreSubscriber();
    $dispatcher->addSubscriber($subscriber);

The dispatcher will automatically register the subscriber for each event
returned by the ``getSubscribedEvents`` method. Like with listeners, the
``addSubscriber`` method has an optional second argument, which is the priority
that should be given to each event.

.. index::
   single: Event Dispatcher; Stopping event flow

.. _event_dispatcher-event-propagation:

Stopping Event Flow/Propagation
-------------------------------

In some cases, it may make sense for a listener to prevent any other listeners
from being called. In other words, the listener needs to be able to tell the
dispatcher to stop all propagation of the event to future listeners (i.e. to
not notify any more listeners). This can be accomplished from inside a listener
via the :method:`Symfony\\Component\\EventDispatcher\\Event::stopPropagation` method:

.. code-block:: php

   use Acme\StoreBundle\Event\FilterOrderEvent;

   public function onStoreOrder(FilterOrderEvent $event)
   {
       // ...
       
       $event->stopPropagation();
   }

Now, any listeners to ``onStoreOrder`` that have not yet been called will
*not* be called.

Learn more from the Cookbook
----------------------------

* :doc:`/cookbook/event_dispatcher/class_extension`
* :doc:`/cookbook/event_dispatcher/method_behavior`

.. _Observer: http://en.wikipedia.org/wiki/Observer_pattern
.. _`Symfony2 HttpKernel component`: https://github.com/symfony/HttpKernel
.. _Closures: http://php.net/manual/en/functions.anonymous.php
.. _`Symfony2 Dependency Injection component`: https://github.com/symfony/DependencyInjection