.. index::
   single: Event Dispatcher
   single: Components; EventDispatcher

The Event Dispatcher Component
==============================

Introduction
------------

Objected Oriented code has gone a long way to ensuring code extensibility. By
creating classes that have well defined responsibilities, your code becomes
more flexible and a developer can extend them with subclasses to modify their
behaviors. But if he wants to share his changes with other developers who have
also made their own subclasses, code inheritance is no longer the answer.

Consider the real-world example where you want to provide a plugin system for
your project. A plugin should be able to add methods, or do something before
or after a method is executed, without interfering with other plugins. This is
not an easy problem to solve with single inheritance, and multiple inheritance
(were it possible with PHP) has its own drawbacks.

The Symfony2 Event Dispatcher component implements the `Observer`_ pattern in
a simple and effective way to make all these things possible and to make your
projects truly extensible.

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

Installation
------------

You can install the component in many different ways:

* Use the official Git repository (https://github.com/symfony/EventDispatcher);
* Install it via PEAR ( `pear.symfony.com/EventDispatcher`);
* Install it via Composer (`symfony/event-dispatcher` on Packagist).

Usage
-----

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
   single: Event Dispatcher; Event subclasses

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

The moral of the story is this: When creating a listener to an event, the
``Event`` object that's passed to the listener may be a special subclass that
has additional methods for retrieving information from and responding to the
event.

The Dispatcher
~~~~~~~~~~~~~~

The dispatcher is the central object of the event dispatcher system. In
general, a single dispatcher is created, which maintains a registry of
listeners. When an event is dispatched via the dispatcher, it notifies all
listeners registered with that event::

    use Symfony\Component\EventDispatcher\EventDispatcher;

    $dispatcher = new EventDispatcher();

.. index::
   single: Event Dispatcher; Listeners

Connecting Listeners
~~~~~~~~~~~~~~~~~~~~

To take advantage of an existing event, you need to connect a listener to the
dispatcher so that it can be notified when the event is dispatched. A call to
the dispatcher ``addListener()`` method associates any valid PHP callable to
an event::

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
    ``is_callable()`` function. It can be a ``\Closure`` instance, an object
    implementing an __invoke method (which is what closures are in fact),
    a string representing a function, or an array representing an object
    method or a class method.

    So far, you've seen how PHP objects can be registered as listeners. You
    can also register PHP `Closures`_ as event listeners::

        use Symfony\Component\EventDispatcher\Event;

        $dispatcher->addListener('foo.action', function (Event $event) {
            // will be executed when the foo.action event is dispatched
        });

Once a listener is registered with the dispatcher, it waits until the event is
notified. In the above example, when the ``foo.action`` event is dispatched,
the dispatcher calls the ``AcmeListener::onFooAction`` method and passes the
``Event`` object as the single argument::

    use Symfony\Component\EventDispatcher\Event;

    class AcmeListener
    {
        // ...

        public function onFooAction(Event $event)
        {
            // ... do something
        }
    }

In many cases, a special ``Event`` subclass that's specific to the given event
is passed to the listener. This gives the listener access to special
information about the event. Check the documentation or implementation of each
event to determine the exact ``Symfony\Component\EventDispatcher\Event``
instance that's being passed. For example, the ``kernel.event`` event passes an
instance of ``Symfony\Component\HttpKernel\Event\FilterResponseEvent``::

    use Symfony\Component\HttpKernel\Event\FilterResponseEvent

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $response = $event->getResponse();
        $request = $event->getRequest();

        // ...
    }

.. _event_dispatcher-closures-as-listeners:

.. index::
   single: Event Dispatcher; Creating and dispatching an event

Creating and Dispatching an Event
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In addition to registering listeners with existing events, you can create and
dispatch your own events. This is useful when creating third-party libraries
and also when you want to keep different components of your own system
flexible and decoupled.

The Static ``Events`` Class
...........................

Suppose you want to create a new Event - ``store.order`` - that is dispatched
each time an order is created inside your application. To keep things
organized, start by creating a ``StoreEvents`` class inside your application
that serves to define and document your event::

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
        const STORE_ORDER = 'store.order';
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
object. Create an ``Event`` class that makes this possible::

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
listener of that event::

    use Acme\StoreBundle\StoreEvents;
    use Acme\StoreBundle\Order;
    use Acme\StoreBundle\Event\FilterOrderEvent;

    // the order is somehow created or retrieved
    $order = new Order();
    // ...

    // create the FilterOrderEvent and dispatch it
    $event = new FilterOrderEvent($order);
    $dispatcher->dispatch(StoreEvents::STORE_ORDER, $event);

Notice that the special ``FilterOrderEvent`` object is created and passed to
the ``dispatch`` method. Now, any listener to the ``store.order`` event will
receive the ``FilterOrderEvent`` and have access to the ``Order`` object via
the ``getOrder`` method::

    // some listener class that's been registered for "STORE_ORDER" event
    use Acme\StoreBundle\Event\FilterOrderEvent;

    public function onStoreOrder(FilterOrderEvent $event)
    {
        $order = $event->getOrder();
        // do something to or with the order
    }

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
subscribes to the ``kernel.response`` and ``store.order`` events::

    namespace Acme\StoreBundle\Event;

    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

    class StoreSubscriber implements EventSubscriberInterface
    {
        static public function getSubscribedEvents()
        {
            return array(
                'kernel.response' => array(
                    array('onKernelResponsePre', 10),
                    array('onKernelResponseMid', 5),
                    array('onKernelResponsePost', 0),
                ),
                'store.order'     => array('onStoreOrder', 0),
            );
        }

        public function onKernelResponsePre(FilterResponseEvent $event)
        {
            // ...
        }

        public function onKernelResponseMid(FilterResponseEvent $event)
        {
            // ...
        }

        public function onKernelResponsePost(FilterResponseEvent $event)
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
method::

    use Acme\StoreBundle\Event\StoreSubscriber;

    $subscriber = new StoreSubscriber();
    $dispatcher->addSubscriber($subscriber);

The dispatcher will automatically register the subscriber for each event
returned by the ``getSubscribedEvents`` method. This method returns an array
indexed by event names and whose values are either the method name to call or
an array composed of the method name to call and a priority. The example
above shows how to register several listener methods for the same event in
subscriber and also shows how to pass the priority of each listener method.

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
:method:`Symfony\\Component\\EventDispatcher\\Event::stopPropagation` method::

   use Acme\StoreBundle\Event\FilterOrderEvent;

   public function onStoreOrder(FilterOrderEvent $event)
   {
       // ...

       $event->stopPropagation();
   }

Now, any listeners to ``store.order`` that have not yet been called will *not*
be called.

It is possible to detect if an event was stopped by using the
:method:`Symfony\\Component\\EventDispatcher\\Event::isStoppedPropagation` method
which returns a boolean value::

    $dispatcher->dispatch('foo.event', $event);
    if ($event->isStoppedPropagation()) {
        // ...
    }

.. index::
   single: Event Dispatcher; Event Dispatcher aware events and listeners

.. _event_dispatcher-dispatcher-aware-events:

EventDispatcher aware Events and Listeners
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 2.1
    The ``Event`` object contains a reference to the invoking dispatcher since Symfony 2.1

The ``EventDispatcher`` always injects a reference to itself in the passed event
object.  This means that all listeners have direct access to the
``EventDispatcher`` object that notified the listener via the passed ``Event``
object's :method:`Symfony\\Component\\EventDispatcher\\Event::getDispatcher`
method.

This can lead to some advanced applications of the ``EventDispatcher`` including
letting listeners dispatch other events, event chaining or even lazy loading of
more listeners into the dispatcher object. Examples follow:

Lazy loading listeners::

    use Symfony\Component\EventDispatcher\Event;
    use Acme\StoreBundle\Event\StoreSubscriber;

    class Foo
    {
        private $started = false;

        public function myLazyListener(Event $event)
        {
            if (false === $this->started) {
                $subscriber = new StoreSubscriber();
                $event->getDispatcher()->addSubscriber($subscriber);
            }

            $this->started = true;

            // ... more code
        }
    }

Dispatching another event from within a listener::

    use Symfony\Component\EventDispatcher\Event;

    class Foo
    {
        public function myFooListener(Event $event)
        {
            $event->getDispatcher()->dispatch('log', $event);

            // ... more code
        }
    }

While this above is sufficient for most uses, if your application uses multiple
``EventDispatcher`` instances, you might need to specifically inject a known
instance of the ``EventDispatcher`` into your listeners.  This could be done
using constructor or setter injection as follows:

Constructor injection::

    use Symfony\Component\EventDispatcher\EventDispatcherInterface;

    class Foo
    {
        protected $dispatcher = null;

        public function __construct(EventDispatcherInterface $dispatcher)
        {
            $this->dispatcher = $dispatcher;
        }
    }

Or setter injection::

    use Symfony\Component\EventDispatcher\EventDispatcherInterface;

    class Foo
    {
        protected $dispatcher = null;

        public function setEventDispatcher(EventDispatcherInterface $dispatcher)
        {
            $this->dispatcher = $dispatcher;
        }
    }

Choosing between the two is really a matter of taste. Many tend to prefer the
constructor injection as the objects are fully initialized at construction
time. But when you have a long list of dependencies, using setter injection
can be the way to go, especially for optional dependencies.

.. index::
   single: Event Dispatcher; Dispatcher shortcuts

.. _event_dispatcher-shortcuts:

Dispatcher Shortcuts
~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 2.1
    ``EventDispatcher::dispatch()`` method returns the event since Symfony 2.1.

The :method:`EventDispatcher::dispatch<Symfony\\Component\\EventDispatcher\\EventDispatcher::dispatch>`
method always returns an :class:`Symfony\\Component\\EventDispatcher\\Event`
object. This allows for various shortcuts. For example if one does not need
a custom event object, one can simply rely on a plain
:class:`Symfony\\Component\\EventDispatcher\\Event` object. You do not even need
to pass this to the dispatcher as it will create one by default unless you
specifically pass one::

    $dispatcher->dispatch('foo.event');

Moreover, the EventDispatcher always returns whichever event object that was
dispatched, i.e. either the event that was passed or the event that was
created internally by the dispatcher. This allows for nice shortcuts::

    if (!$dispatcher->dispatch('foo.event')->isStoppedPropagation()) {
        // ...
    }

Or::

    $barEvent = new BarEvent();
    $bar = $dispatcher->dispatch('bar.event', $barEvent)->getBar();

Or::

    $response = $dispatcher->dispatch('bar.event', new BarEvent())->getBar();

and so on...

.. index::
   single: Event Dispatcher; Event name introspection

.. _event_dispatcher-event-name-introspection:

Event Name Introspection
~~~~~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 2.1
    Added event name to the ``Event`` object since Symfony 2.1

Since the ``EventDispatcher`` already knows the name of the event when dispatching
it, the event name is also injected into the
:class:`Symfony\\Component\\EventDispatcher\\Event` objects, making it available
to event listeners via the :method:`Symfony\\Component\\EventDispatcher\\Event::getName`
method.

The event name, (as with any other data in a custom event object) can be used as
part of the listener's processing logic::

    use Symfony\Component\EventDispatcher\Event;

    class Foo
    {
        public function myEventListener(Event $event)
        {
            echo $event->getName();
        }
    }

.. _Observer: http://en.wikipedia.org/wiki/Observer_pattern
.. _`Symfony2 HttpKernel component`: https://github.com/symfony/HttpKernel
.. _Closures: http://php.net/manual/en/functions.anonymous.php
.. _PHP callable: http://www.php.net/manual/en/language.pseudo-types.php#language.types.callback
