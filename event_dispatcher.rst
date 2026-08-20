Events and Event Listeners
==========================

In programming, an **event** is a notification that is sent when something
meaningful happens in an application (e.g. a user logs in, an order is placed
or an error occurs). Other parts of the application can listen to those
notifications and respond to them by executing any piece of code.

Events help you extend an application without changing its existing code. For
example, when an order is placed in an online store, you can dispatch a custom
event that other parts of the application listen to, to send confirmation
emails, update statistics, notify third-party services, etc. Adding or removing
any of those features doesn't require changing the code that places the order.

This feature is provided by the EventDispatcher component, which implements the
`Mediator`_ and `Observer`_ design patterns. You can use it in any PHP
application, with or without Symfony.

During the execution of a Symfony application, lots of event notifications are
triggered. Most of them are :doc:`events related to the kernel </reference/events>`,
dispatched while processing the HTTP request. Third-party bundles and libraries
also dispatch events, and you can dispatch
:ref:`your own custom events <creating-an-event-object>` from your own code.

Installation
------------

In Symfony applications, this component is already installed because it's
a dependency of the framework itself. In other applications, run this command
to install the component before using it:

.. code-block:: terminal

    $ composer require symfony/event-dispatcher

.. include:: /components/require_autoload.rst.inc

Events and the Dispatcher
-------------------------

When an event is dispatched, it's identified by a unique name (e.g.
``kernel.response``), which any number of listeners might be listening to.
An event object is also created and passed to all listeners. This object is
often a subclass of :class:`Symfony\\Contracts\\EventDispatcher\\Event` with
additional methods to get and even override information related to the event.
For example, the ``kernel.response`` event uses a
:class:`Symfony\\Component\\HttpKernel\\Event\\ResponseEvent` class, which
contains methods to get and even replace the ``Response`` object.

Symfony dispatches the ``kernel.response`` event once the ``Response`` object
has been created, so other parts of the system can modify it (e.g. add some
cache headers) before it's actually used. Here's how the event dispatching
process works:

* A *listener* (a PHP callable) tells a central *dispatcher* object that it
  wants to listen to the ``kernel.response`` event;

* At some point, Symfony tells the *dispatcher* object to dispatch the
  ``kernel.response`` event, passing with it an ``Event`` object that has
  access to the ``Response`` object;

* The dispatcher notifies (i.e. calls a method on) all listeners of the
  ``kernel.response`` event, allowing each of them to make modifications
  to the ``Response`` object.

The dispatcher is the central object of the event system: it maintains a
registry of listeners and, when an event is dispatched, it notifies all
listeners registered with that event. In Symfony applications, the dispatcher
is a service that you can inject anywhere thanks to autowiring; in other PHP
applications, create the dispatcher yourself:

.. configuration-block::

    .. code-block:: php-symfony

        // src/Service/OrderService.php
        namespace App\Service;

        use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

        class OrderService
        {
            public function __construct(
                private EventDispatcherInterface $dispatcher,
            ) {
            }

            // ...
        }

    .. code-block:: php-standalone

        use Symfony\Component\EventDispatcher\EventDispatcher;

        $dispatcher = new EventDispatcher();

.. tip::

    When injecting the event dispatcher, type-hint one of its interfaces
    instead of the concrete ``EventDispatcher`` class. Use
    :class:`Symfony\\Contracts\\EventDispatcher\\EventDispatcherInterface`
    when you only need to dispatch events, or
    :class:`Symfony\\Component\\EventDispatcher\\EventDispatcherInterface`
    if you also need to :ref:`inspect or manage listeners <event-dispatcher-inspecting-listeners>`.

.. note::

    Symfony's event dispatcher implements the `PSR-14`_ standard:
    :class:`Symfony\\Contracts\\EventDispatcher\\EventDispatcherInterface`
    extends ``Psr\EventDispatcher\EventDispatcherInterface``. This means that
    you can use Symfony's dispatcher in any library that expects a PSR-14
    event dispatcher.

Creating an Event Listener
--------------------------

The most common way to listen to an event is to register an **event listener**::

    // src/EventListener/ExceptionListener.php
    namespace App\EventListener;

    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpKernel\Event\ExceptionEvent;
    use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

    class ExceptionListener
    {
        public function __invoke(ExceptionEvent $event): void
        {
            // You get the exception object from the received event
            $exception = $event->getThrowable();
            $message = sprintf(
                'My Error says: %s with code: %s',
                $exception->getMessage(),
                $exception->getCode()
            );

            // Customize your response object to display the exception details
            $response = new Response();
            $response->setContent($message);
            // the exception message can contain unfiltered user input;
            // set the content-type to text to avoid XSS issues
            $response->headers->set('Content-Type', 'text/plain; charset=utf-8');

            // HttpExceptionInterface is a special type of exception that
            // holds status code and header details
            if ($exception instanceof HttpExceptionInterface) {
                $response->setStatusCode($exception->getStatusCode());
                $response->headers->replace($exception->getHeaders());
            } else {
                $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            // sends the modified response object to the event
            $event->setResponse($response);
        }
    }

Now that the class is created, you need to register it as an event listener.
In Symfony applications, define the class as a service and add a special "tag"
to it. In other PHP applications, use the ``addListener()`` method of the
dispatcher:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\EventListener\ExceptionListener:
                tags: [kernel.event_listener]

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\EventListener\ExceptionListener;

        return App::config([
            'services' => [
                ExceptionListener::class => [
                    'tags' => ['kernel.event_listener'],
                ],
            ],
        ]);

    .. code-block:: php-standalone

        use App\EventListener\ExceptionListener;
        use Symfony\Component\EventDispatcher\EventDispatcher;

        $dispatcher = new EventDispatcher();
        $listener = new ExceptionListener();
        $dispatcher->addListener('kernel.exception', $listener);

Symfony follows this logic to decide which method to call inside the event
listener class:

#. If the ``kernel.event_listener`` tag defines the ``method`` attribute, that's
   the name of the method to be called;
#. If no ``method`` attribute is defined, try to call the ``__invoke()`` magic
   method (which makes event listeners invokable);
#. If the ``__invoke()`` method is not defined either, throw an exception.

.. note::

    There is an optional attribute for the ``kernel.event_listener`` tag called
    ``priority``, which is a positive or negative integer that defaults to ``0``
    and it controls the order in which listeners are executed (the higher the
    number, the earlier a listener is executed). This is useful when you need to
    guarantee that one listener is executed before another. The priorities of the
    internal Symfony listeners usually range from ``-256`` to ``256`` but your
    own listeners can use any positive or negative integer. If two listeners
    have the same priority, they are executed in the order that they were
    registered. When using the ``addListener()`` method, pass the priority as
    its optional third argument.

.. note::

    There is an optional attribute for the ``kernel.event_listener`` tag called
    ``event`` which is useful when the listener's ``$event`` argument is not typed.
    If you configure it, it will change type of ``$event`` object.
    For the ``kernel.exception`` event, it is :class:`Symfony\\Component\\HttpKernel\\Event\\ExceptionEvent`.
    Check out the :doc:`Symfony events reference </reference/events>` to see
    what type of object each event provides.

    With this attribute, Symfony follows this logic to decide which method to call
    inside the event listener class:

    #. If the ``kernel.event_listener`` tag defines the ``method`` attribute, that's
       the name of the method to be called;
    #. If no ``method`` attribute is defined, try to call the method whose name
       is ``on`` + "PascalCased event name" (e.g. ``onKernelException()`` method for
       the ``kernel.exception`` event);
    #. If that method is not defined either, try to call the ``__invoke()`` magic
       method (which makes event listeners invokable);
    #. If the ``__invoke()`` method is not defined either, throw an exception.

.. _event_dispatcher-closures-as-listeners:

An event listener can be any valid `PHP callable`_: an object implementing the
``__invoke()`` method, a method of an object, a static class method, a global
function, etc. When using the ``addListener()`` method, you can also register
`Closures`_ as event listeners::

    use Symfony\Contracts\EventDispatcher\Event;

    $dispatcher->addListener('order.placed', function (Event $event): void {
        // will be executed when the order.placed event is dispatched
    });

.. _event-dispatcher_event-listener-attributes:

Defining Event Listeners with PHP Attributes
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

An alternative way to define an event listener is to use the
:class:`Symfony\\Component\\EventDispatcher\\Attribute\\AsEventListener`
PHP attribute. This allows you to configure the listener inside its class, without
having to add any configuration in external files::

    namespace App\EventListener;

    use App\Event\CustomEvent;
    use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

    #[AsEventListener]
    final class MyListener
    {
        public function __invoke(CustomEvent $event): void
        {
            // ...
        }
    }

You can add multiple ``#[AsEventListener]`` attributes to configure different methods.
The ``method`` property is optional, and when not defined, it defaults to
``on`` + uppercased event name. In the example below, the ``'foo'`` event listener
doesn't explicitly define its method, so the ``onFoo()`` method will be called::

    namespace App\EventListener;

    use App\Event\CustomEvent;
    use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

    #[AsEventListener(event: CustomEvent::class, method: 'onCustomEvent')]
    #[AsEventListener(event: 'foo', priority: 42)]
    #[AsEventListener(event: 'bar', method: 'onBarEvent')]
    final class MyMultiListener
    {
        public function onCustomEvent(CustomEvent $event): void
        {
            // ...
        }

        public function onFoo(): void
        {
            // ...
        }

        public function onBarEvent(): void
        {
            // ...
        }
    }

:class:`Symfony\\Component\\EventDispatcher\\Attribute\\AsEventListener`
can also be applied to methods directly::

    namespace App\EventListener;

    use App\Event\CustomEvent;
    use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

    final class MyMultiListener
    {
        #[AsEventListener]
        public function onCustomEvent(CustomEvent $event): void
        {
            // ...
        }

        #[AsEventListener]
        public function onMultipleCustomEvent(CustomEvent|AnotherCustomEvent $event): void
        {
            // ...
        }

        #[AsEventListener(event: 'foo', priority: 42)]
        public function onFoo(): void
        {
            // ...
        }

        #[AsEventListener(event: 'bar')]
        public function onBarEvent(): void
        {
            // ...
        }
    }

.. note::

    Note that the attribute doesn't require its ``event`` parameter to be set
    if the method already type-hints the expected event.

.. _events-subscriber:
.. _event_dispatcher-using-event-subscribers:

Creating an Event Subscriber
----------------------------

Another way to listen to events is via an **event subscriber**, which is a class
that defines one or more methods that listen to one or various events. The main
difference with the event listeners is that subscribers always know the events
to which they are listening.

Event subscribers implement the
:class:`Symfony\\Component\\EventDispatcher\\EventSubscriberInterface`
interface, which requires a single static method called
:method:`Symfony\\Component\\EventDispatcher\\EventSubscriberInterface::getSubscribedEvents`.
This method returns an array whose keys are event names and whose values are
either the name of the method to call or an array composed of the method name
and a priority.

If different event subscriber methods listen to the same event, their order is
defined by the ``priority`` parameter. This value is a positive or negative
integer which defaults to ``0``. The higher the number, the earlier the method
is called. **Priority is aggregated for all listeners and subscribers**, so your
methods could be called before or after the methods defined in other listeners
and subscribers.

The following example shows an event subscriber that defines several methods which
listen to the same :ref:`kernel.exception event <component-http-kernel-kernel-exception>`
via its ``ExceptionEvent`` class::

    // src/EventSubscriber/ExceptionSubscriber.php
    namespace App\EventSubscriber;

    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Symfony\Component\HttpKernel\Event\ExceptionEvent;

    class ExceptionSubscriber implements EventSubscriberInterface
    {
        public static function getSubscribedEvents(): array
        {
            // return the subscribed events, their methods and priorities
            return [
                ExceptionEvent::class => [
                    ['processException', 10],
                    ['logException', 0],
                    ['notifyException', -10],
                ],
            ];
        }

        public function processException(ExceptionEvent $event): void
        {
            // ...
        }

        public function logException(ExceptionEvent $event): void
        {
            // ...
        }

        public function notifyException(ExceptionEvent $event): void
        {
            // ...
        }
    }

In Symfony applications, that's all you need to do. Your ``services.yaml``
file should already be set up to load services from the ``EventSubscriber``
directory, so Symfony takes care of the rest. In other PHP applications,
register the subscriber with the
:method:`Symfony\\Component\\EventDispatcher\\EventDispatcher::addSubscriber`
method::

    use App\EventSubscriber\ExceptionSubscriber;

    $subscriber = new ExceptionSubscriber();
    $dispatcher->addSubscriber($subscriber);

The dispatcher automatically registers the subscriber for each event returned
by the ``getSubscribedEvents()`` method.

.. _ref-event-subscriber-configuration:

.. tip::

    If your methods are *not* called when an exception is thrown, double-check that
    you're :ref:`loading services <service-container-services-load-example>` from
    the ``EventSubscriber`` directory and have :ref:`autoconfigure <services-autoconfigure>`
    enabled. You can also manually add the ``kernel.event_subscriber`` tag.

Request Events, Checking Types
------------------------------

A single page can make several requests (one main request, and then multiple
sub-requests - typically when :ref:`embedding controllers in templates <templates-embed-controllers>`).
For the core Symfony events, you might need to check to see if the event is for
a "main" request or a "sub request"::

    // src/EventListener/RequestListener.php
    namespace App\EventListener;

    use Symfony\Component\HttpKernel\Event\RequestEvent;

    class RequestListener
    {
        public function onKernelRequest(RequestEvent $event): void
        {
            if (!$event->isMainRequest()) {
                // don't do anything if it's not the main request
                return;
            }

            // ...
        }
    }

Certain things, like checking information on the *real* request, may not need to
be done on the sub-request listeners.

.. _events-or-subscribers:

Listeners or Subscribers
------------------------

Listeners and subscribers can be used in the same application interchangeably. The
decision to use either of them is usually a matter of personal taste. However,
there are some minor advantages for each of them:

* **Subscribers are easier to reuse** because the knowledge of the events is kept
  in the class rather than in the service definition. This is the reason why
  Symfony uses subscribers internally;
* **Listeners are more flexible** because bundles can enable or disable each of
  them conditionally depending on some configuration value.

Creating and Dispatching Custom Events
--------------------------------------

In addition to registering listeners with existing events, you can create and
dispatch your own events. This is useful when creating third-party libraries
and also when you want to keep different components of your own system
flexible and decoupled.

.. _creating-an-event-object:

Creating an Event Class
~~~~~~~~~~~~~~~~~~~~~~~

Suppose you want to create a new event that is dispatched each time a customer
orders a product with your application. When dispatching this event, you'll
pass a custom event instance that has access to the placed order. Start by
creating this custom event class and documenting it::

    // src/Event/OrderPlacedEvent.php
    namespace App\Event;

    use App\Entity\Order;
    use Symfony\Contracts\EventDispatcher\Event;

    /**
     * This event is dispatched each time an order
     * is placed in the system.
     */
    final class OrderPlacedEvent extends Event
    {
        public function __construct(
            private Order $order,
        ) {
        }

        public function getOrder(): Order
        {
            return $this->order;
        }
    }

Each listener now has access to the order via the ``getOrder()`` method.

Dispatching the Event
~~~~~~~~~~~~~~~~~~~~~

The :method:`Symfony\\Contracts\\EventDispatcher\\EventDispatcherInterface::dispatch`
method notifies all listeners of the given event. It takes two arguments:
the event instance to pass to each listener of that event and optionally the
name of the event to dispatch. If the name is not defined, the fully qualified
class name (FQCN) of the event instance is used as the event name:

.. configuration-block::

    .. code-block:: php-symfony

        // src/Service/OrderService.php
        namespace App\Service;

        use App\Entity\Order;
        use App\Event\OrderPlacedEvent;
        use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

        class OrderService
        {
            public function __construct(
                private EventDispatcherInterface $dispatcher,
            ) {
            }

            public function placeOrder(): void
            {
                // the order is somehow created or retrieved
                $order = new Order();
                // ...

                // creates the OrderPlacedEvent and dispatches it
                $event = new OrderPlacedEvent($order);
                $this->dispatcher->dispatch($event);
            }
        }

    .. code-block:: php-standalone

        use App\Entity\Order;
        use App\Event\OrderPlacedEvent;
        use Symfony\Component\EventDispatcher\EventDispatcher;

        $dispatcher = new EventDispatcher();

        // the order is somehow created or retrieved
        $order = new Order();
        // ...

        // creates the OrderPlacedEvent and dispatches it
        $event = new OrderPlacedEvent($order);
        $dispatcher->dispatch($event);

Notice that the special ``OrderPlacedEvent`` object is created and passed to
the ``dispatch()`` method. Now, any listener to the ``OrderPlacedEvent::class``
event will receive the ``OrderPlacedEvent``.

The ``dispatch()`` method returns the same event object after all listeners
have been called. This allows you to read any information that listeners may
have added or modified in the event::

    $event = $dispatcher->dispatch(new OrderPlacedEvent($order));

    // listeners may have modified the order (e.g. to apply some discount)
    $order = $event->getOrder();

.. note::

    If you don't need to pass any additional data to the event listeners, you
    can also use the default
    :class:`Symfony\\Contracts\\EventDispatcher\\Event` class. In such case,
    define and document the event name as a constant in some class, similar to
    the :class:`Symfony\\Component\\HttpKernel\\KernelEvents` class used by
    Symfony::

        // src/Event/StoreEvents.php
        namespace App\Event;

        final class StoreEvents
        {
            /**
             * This event is dispatched each time an order
             * is placed in the system.
             */
            public const ORDER_PLACED = 'order.placed';
        }

    Then, pass the event name as the second argument of the ``dispatch()``
    method::

        use App\Event\StoreEvents;
        use Symfony\Contracts\EventDispatcher\Event;

        $dispatcher->dispatch(new Event(), StoreEvents::ORDER_PLACED);

.. _event-dispatcher-generic-event:

The Generic Event Object
~~~~~~~~~~~~~~~~~~~~~~~~

Creating a custom event class, as shown in the previous sections, is the
recommended way to pass data to listeners: the class name identifies the event
and its methods define the exact data passed to the listeners.

However, for quick prototypes or very simple needs, you can use the
:class:`Symfony\\Component\\EventDispatcher\\GenericEvent` class instead of
creating one custom class per event. It stores an arbitrary event *subject*
and optional arguments, which are also accessible via the
:phpclass:`ArrayAccess` interface::

    use Symfony\Component\EventDispatcher\GenericEvent;

    $event = new GenericEvent($order, ['type' => 'online', 'counter' => 0]);
    $dispatcher->dispatch($event, 'order.placed');

    class OrderListener
    {
        public function handle(GenericEvent $event): void
        {
            if ($event->getSubject() instanceof Order) {
                // ...
            }

            if (isset($event['type']) && 'online' === $event['type']) {
                // ...
            }

            $event['counter']++;
        }
    }

In addition to ``getSubject()``, the class defines some methods to work with
the event arguments: ``getArgument()``, ``getArguments()``, ``setArgument()``,
``setArguments()`` and ``hasArgument()``.

.. _event_dispatcher-event-propagation:

Stopping Event Flow/Propagation
-------------------------------

In some cases, it may make sense for a listener to prevent any other listeners
from being called. In other words, the listener needs to be able to tell
the dispatcher to stop all propagation of the event to future listeners
(i.e. to not notify any more listeners). This can be accomplished from
inside a listener via the
:method:`Symfony\\Contracts\\EventDispatcher\\Event::stopPropagation` method::

    use App\Event\OrderPlacedEvent;

    public function onPlacedOrder(OrderPlacedEvent $event): void
    {
        // ...

        $event->stopPropagation();
    }

Now, any listeners to ``OrderPlacedEvent::class`` that have not yet been called
will *not* be called.

It is possible to detect if an event was stopped by using the
:method:`Symfony\\Contracts\\EventDispatcher\\Event::isPropagationStopped`
method which returns a boolean value::

    // ...
    $dispatcher->dispatch($event);
    if ($event->isPropagationStopped()) {
        // ...
    }

.. _event_dispatcher-dispatcher-aware-events:
.. _event_dispatcher-event-name-introspection:

Event Name Introspection
------------------------

In addition to the event object, the dispatcher passes the name of the
dispatched event and a reference to itself to the listeners. Add these
optional arguments to the listener signature when you need them (e.g. to
reuse the same listener for several events or to dispatch other events from
inside a listener)::

    use Symfony\Contracts\EventDispatcher\Event;
    use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

    class MyListener
    {
        public function myEventListener(Event $event, string $eventName, EventDispatcherInterface $dispatcher): void
        {
            // ... do something with the event name
        }
    }

.. _event-dispatcher-inspecting-listeners:

Inspecting and Removing Listeners
---------------------------------

The :class:`Symfony\\Component\\EventDispatcher\\EventDispatcherInterface`
defines some methods to get information about the registered listeners and to
remove them::

    use App\Event\OrderPlacedEvent;

    // checks whether some event has any registered listener (if you don't
    // pass any argument, it checks all events at once)
    $dispatcher->hasListeners(OrderPlacedEvent::class);

    // gets the listeners of some event, sorted from the highest to the
    // lowest priority (if you don't pass any argument, it returns the
    // listeners of all events)
    $listeners = $dispatcher->getListeners(OrderPlacedEvent::class);

    // gets the priority of some listener (it returns null if the listener
    // is not registered for that event)
    $priority = $dispatcher->getListenerPriority(OrderPlacedEvent::class, $listener);

    // removes a listener or a subscriber from the dispatcher
    $dispatcher->removeListener(OrderPlacedEvent::class, $listener);
    $dispatcher->removeSubscriber($subscriber);

These methods are defined in the component interface but not in the
:class:`Symfony\\Contracts\\EventDispatcher\\EventDispatcherInterface`. In
Symfony applications, type-hint your service argument with the component
interface to use these methods.

Event Aliases
-------------

When configuring event listeners and subscribers via dependency injection,
Symfony's core events can also be referred to by the fully qualified class
name (FQCN) of the corresponding event class::

    // src/EventSubscriber/RequestSubscriber.php
    namespace App\EventSubscriber;

    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Symfony\Component\HttpKernel\Event\RequestEvent;

    class RequestSubscriber implements EventSubscriberInterface
    {
        public static function getSubscribedEvents(): array
        {
            return [
                RequestEvent::class => 'onKernelRequest',
            ];
        }

        public function onKernelRequest(RequestEvent $event): void
        {
            // ...
        }
    }

Internally, the event FQCN are treated as aliases for the original event names.
Since the mapping already happens when compiling the service container, event
listeners and subscribers using FQCN instead of event names will appear under
the original event name when inspecting the event dispatcher.

This alias mapping can be extended for custom events by registering the
compiler pass ``AddEventAliasesPass``::

    // src/Kernel.php
    namespace App;

    use App\Event\MyCustomEvent;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\EventDispatcher\DependencyInjection\AddEventAliasesPass;
    use Symfony\Component\HttpKernel\Kernel as BaseKernel;

    class Kernel extends BaseKernel
    {
        protected function build(ContainerBuilder $container): void
        {
            $container->addCompilerPass(new AddEventAliasesPass([
                MyCustomEvent::class => 'my_custom_event',
            ]));
        }
    }

The compiler pass will always extend the existing list of aliases. Because of
that, it is safe to register multiple instances of the pass with different
configurations.

Registering Listeners in Standalone Applications
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If your application doesn't use the Symfony framework but uses the
:doc:`service container </service_container>`, you
can still use the ``kernel.event_listener`` and ``kernel.event_subscriber``
tags shown in the previous sections. To enable them, register a compiler pass
called ``RegisterListenersPass`` in the container builder::

    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
    use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
    use Symfony\Component\EventDispatcher\EventDispatcher;

    $container = new ContainerBuilder(new ParameterBag());
    // register the compiler pass that handles the 'kernel.event_listener'
    // and 'kernel.event_subscriber' service tags
    $container->addCompilerPass(new RegisterListenersPass());

    $container->register('event_dispatcher', EventDispatcher::class);

    // registers an event listener
    $container->register('listener_service_id', \AcmeListener::class)
        ->addTag('kernel.event_listener', [
            'event' => 'acme.foo.action',
            'method' => 'onFooAction',
        ]);

    // registers an event subscriber
    $container->register('subscriber_service_id', \AcmeSubscriber::class)
        ->addTag('kernel.event_subscriber');

``RegisterListenersPass`` resolves aliased class names which for instance
allows you to refer to an event via the fully qualified class name (FQCN) of
the event class. The pass will read the alias mapping from a dedicated
container parameter. This parameter can be extended by registering the
``AddEventAliasesPass`` compiler pass::

    use Symfony\Component\DependencyInjection\Compiler\PassConfig;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
    use Symfony\Component\EventDispatcher\DependencyInjection\AddEventAliasesPass;
    use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
    use Symfony\Component\EventDispatcher\EventDispatcher;

    $container = new ContainerBuilder(new ParameterBag());
    $container->addCompilerPass(new AddEventAliasesPass([
        \AcmeFooActionEvent::class => 'acme.foo.action',
    ]));
    $container->addCompilerPass(new RegisterListenersPass(), PassConfig::TYPE_BEFORE_REMOVING);

    $container->register('event_dispatcher', EventDispatcher::class);

    // registers an event listener
    $container->register('listener_service_id', \AcmeListener::class)
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

Debugging Event Listeners
-------------------------

You can find out what listeners are registered in the event dispatcher
using the console. To show all events and their listeners, run:

.. code-block:: terminal

    $ php bin/console debug:event-dispatcher

You can get registered listeners for a particular event by specifying
its name:

.. code-block:: terminal

    $ php bin/console debug:event-dispatcher kernel.exception

or you can get everything that partially matches the event name:

.. code-block:: terminal

    $ php bin/console debug:event-dispatcher kernel // matches "kernel.exception", "kernel.response" etc.
    $ php bin/console debug:event-dispatcher Security // matches "Symfony\Component\Security\Http\Event\CheckPassportEvent"

The :doc:`security </security>` system uses an event dispatcher per
firewall. Use the ``--dispatcher`` option to get the registered listeners
for a particular event dispatcher:

.. code-block:: terminal

    $ php bin/console debug:event-dispatcher --dispatcher=security.event_dispatcher.main

.. _event-dispatcher-traceable-dispatcher:

The Traceable Event Dispatcher
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The debugging features shown in the previous section are powered by the
:class:`Symfony\\Component\\EventDispatcher\\Debug\\TraceableEventDispatcher`
class. This event dispatcher wraps any other dispatcher to record which of the
event listeners are called by it. In standalone applications, you can use this
class directly by passing the wrapped dispatcher and a
:class:`Symfony\\Component\\Stopwatch\\Stopwatch` instance to its constructor::

    use Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher;
    use Symfony\Component\EventDispatcher\EventDispatcher;
    use Symfony\Component\Stopwatch\Stopwatch;

    // the event dispatcher to debug
    $dispatcher = new EventDispatcher();

    $traceableEventDispatcher = new TraceableEventDispatcher(
        $dispatcher,
        new Stopwatch()
    );

Use the traceable dispatcher as any other dispatcher to register event
listeners and dispatch events. When your application has processed all the
events, use the
:method:`Symfony\\Component\\EventDispatcher\\Debug\\TraceableEventDispatcher::getCalledListeners`
and
:method:`Symfony\\Component\\EventDispatcher\\Debug\\TraceableEventDispatcher::getNotCalledListeners`
methods to get information about the event listeners::

    // ...

    $calledListeners = $traceableEventDispatcher->getCalledListeners();
    $notCalledListeners = $traceableEventDispatcher->getNotCalledListeners();

.. _event-dispatcher-immutable-dispatcher:

The Immutable Event Dispatcher
------------------------------

The :class:`Symfony\\Component\\EventDispatcher\\ImmutableEventDispatcher`
is a locked or frozen event dispatcher. It's a proxy of another dispatcher
and it doesn't allow registering new listeners or subscribers.

To use it, first create a normal dispatcher and register some listeners or
subscribers. Then, wrap it with the immutable dispatcher::

    use Symfony\Component\EventDispatcher\EventDispatcher;
    use Symfony\Component\EventDispatcher\ImmutableEventDispatcher;
    use Symfony\Contracts\EventDispatcher\Event;

    $dispatcher = new EventDispatcher();
    $dispatcher->addListener('order.placed', function (Event $event): void {
        // ...
    });

    // ...

    $immutableDispatcher = new ImmutableEventDispatcher($dispatcher);

If your code tries to call any of the methods that modify the immutable
dispatcher (e.g. ``addListener()``), a ``BadMethodCallException`` is thrown.

.. _event-dispatcher-before-after-filters:

How to Set Up Before and After Filters
--------------------------------------

It is quite common in web application development to need some logic to be
performed right before or directly after your controller actions acting as
filters or hooks.

Some web frameworks define methods like ``preExecute()`` and ``postExecute()``,
but there is no such thing in Symfony. The good news is that there is a much
better way to interfere with the Request -> Response process using the
event dispatcher.

Token Validation Example
~~~~~~~~~~~~~~~~~~~~~~~~

Imagine that you need to develop an API where some controllers are public
but some others are restricted to one or some clients. For these private features,
you might provide a token to your clients to identify themselves.

So, before executing your controller action, you need to check if the action
is restricted or not. If it is restricted, you need to validate the provided
token.

.. note::

    Please note that for simplicity in this recipe, tokens will be defined
    in config and neither database setup nor authentication via the Security
    component will be used.

Before Filters with the ``kernel.controller`` Event
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

First, define some token configuration as parameters:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        parameters:
            tokens:
                client1: pass1
                client2: pass2

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'parameters' => [
                'tokens' => [
                    'client1' => 'pass1',
                    'client2' => 'pass2',
                ],
            ],
        ]);

Tag Controllers to Be Checked
.............................

A ``kernel.controller`` (aka ``KernelEvents::CONTROLLER``) listener gets notified
on *every* request, right before the controller is executed. So, first, you need
some way to identify if the controller that matches the request needs token validation.

A clean and simple way is to create an empty interface and make the controllers
implement it::

    namespace App\Controller;

    interface TokenAuthenticatedController
    {
        // ...
    }

A controller that implements this interface looks like this::

    namespace App\Controller;

    use App\Controller\TokenAuthenticatedController;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Response;

    class FooController extends AbstractController implements TokenAuthenticatedController
    {
        // An action that needs authentication
        public function bar(): Response
        {
            // ...
        }
    }

Creating an Event Subscriber
............................

Next, you'll need to create an event subscriber, which will hold the logic
that you want to be executed before your controllers. If you're not familiar with
event subscribers, you can learn more about :ref:`how to use them <events-subscriber>`::

    // src/EventSubscriber/TokenSubscriber.php
    namespace App\EventSubscriber;

    use App\Controller\TokenAuthenticatedController;
    use Symfony\Component\DependencyInjection\Attribute\Autowire;
    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Symfony\Component\HttpKernel\Event\ControllerEvent;
    use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
    use Symfony\Component\HttpKernel\KernelEvents;

    class TokenSubscriber implements EventSubscriberInterface
    {
        public function __construct(
            #[Autowire(param: 'tokens')]
            private array $tokens
        ) {
        }

        public function onKernelController(ControllerEvent $event): void
        {
            $controller = $event->getController();

            // when a controller class defines multiple action methods, the controller
            // is returned as [$controllerInstance, 'methodName']
            if (is_array($controller)) {
                $controller = $controller[0];
            }

            if ($controller instanceof TokenAuthenticatedController) {
                $token = $event->getRequest()->query->get('token');
                if (!in_array($token, $this->tokens)) {
                    throw new AccessDeniedHttpException('This action needs a valid token!');
                }
            }
        }

        public static function getSubscribedEvents(): array
        {
            return [
                KernelEvents::CONTROLLER => 'onKernelController',
            ];
        }
    }

That's it! Your ``services.yaml`` file should already be set up to load services from
the ``EventSubscriber`` directory. Symfony takes care of the rest. Your
``TokenSubscriber`` ``onKernelController()`` method will be executed on each request.
If the controller that is about to be executed implements ``TokenAuthenticatedController``,
token authentication is applied. This lets you have a "before" filter on any controller
you want.

.. tip::

    If your subscriber is *not* called on each request, double-check that
    you're :ref:`loading services <service-container-services-load-example>` from
    the ``EventSubscriber`` directory and have :ref:`autoconfigure <services-autoconfigure>`
    enabled. You can also manually add the ``kernel.event_subscriber`` tag.

After Filters with the ``kernel.response`` Event
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In addition to having a "hook" that's executed *before* your controller, you
can also add a hook that's executed *after* your controller. For this example,
imagine that you want to add a ``sha1`` hash (with a salt using that token) to
all responses that have passed this token authentication.

Another core Symfony event - called ``kernel.response`` (aka ``KernelEvents::RESPONSE``) -
is notified on every request, but after the controller returns a Response object.
To create an "after" listener, create a listener class and register
it as a service on this event.

For example, take the ``TokenSubscriber`` from the previous example and first
record the authentication token inside the request attributes. This will
serve as a basic flag that this request underwent token authentication::

    public function onKernelController(ControllerEvent $event): void
    {
        // ...

        if ($controller instanceof TokenAuthenticatedController) {
            $token = $event->getRequest()->query->get('token');
            if (!in_array($token, $this->tokens)) {
                throw new AccessDeniedHttpException('This action needs a valid token!');
            }

            // mark the request as having passed token authentication
            $event->getRequest()->attributes->set('auth_token', $token);
        }
    }

Now, configure the subscriber to listen to another event and add ``onKernelResponse()``.
This will look for the ``auth_token`` flag on the request object and set a custom
header on the response if it's found::

    // add the new use statement at the top of your file
    use Symfony\Component\HttpKernel\Event\ResponseEvent;

    public function onKernelResponse(ResponseEvent $event): void
    {
        // check to see if onKernelController marked this as a token "auth'ed" request
        if (!$token = $event->getRequest()->attributes->get('auth_token')) {
            return;
        }

        $response = $event->getResponse();

        // create a hash and set it as a response header
        $hash = sha1($response->getContent().$token);
        $response->headers->set('X-CONTENT-HASH', $hash);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

That's it! The ``TokenSubscriber`` is now notified before every controller is
executed (``onKernelController()``) and after every controller returns a response
(``onKernelResponse()``). By making specific controllers implement the ``TokenAuthenticatedController``
interface, your listener knows which controllers it should take action on.
And by storing a value in the request's "attributes" bag, the ``onKernelResponse()``
method knows to add the extra header. Have fun!

.. _event-dispatcher-method-behavior:

How to Customize a Method Behavior without Using Inheritance
------------------------------------------------------------

If you want to do something right before, or directly after a method is
called, you can dispatch an event respectively at the beginning or at the
end of the method::

    use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

    class CustomMailer
    {
        public function __construct(
            private EventDispatcherInterface $dispatcher,
        ) {
        }

        public function send(string $subject, string $message): mixed
        {
            // dispatch an event before the method
            $event = new BeforeSendMailEvent($subject, $message);
            $this->dispatcher->dispatch($event, 'mailer.pre_send');

            // get $subject and $message from the event, they may have been modified
            $subject = $event->getSubject();
            $message = $event->getMessage();

            // the real method implementation is here
            $returnValue = ...;

            // do something after the method
            $event = new AfterSendMailEvent($returnValue);
            $this->dispatcher->dispatch($event, 'mailer.post_send');

            return $event->getReturnValue();
        }
    }

In this example, two events are dispatched:

#. ``mailer.pre_send``, before the method is called,
#. and ``mailer.post_send`` after the method is called.

Each uses a custom Event class to communicate information to the listeners
of the two events. For example, ``BeforeSendMailEvent`` might look like
this::

    // src/Event/BeforeSendMailEvent.php
    namespace App\Event;

    use Symfony\Contracts\EventDispatcher\Event;

    class BeforeSendMailEvent extends Event
    {
        public function __construct(
            private string $subject,
            private string $message,
        ) {
        }

        public function getSubject(): string
        {
            return $this->subject;
        }

        public function setSubject(string $subject): void
        {
            $this->subject = $subject;
        }

        public function getMessage(): string
        {
            return $this->message;
        }

        public function setMessage(string $message): void
        {
            $this->message = $message;
        }
    }

And the ``AfterSendMailEvent`` event like this::

    // src/Event/AfterSendMailEvent.php
    namespace App\Event;

    use Symfony\Contracts\EventDispatcher\Event;

    class AfterSendMailEvent extends Event
    {
        public function __construct(
            private mixed $returnValue,
        ) {
        }

        public function getReturnValue(): mixed
        {
            return $this->returnValue;
        }

        public function setReturnValue(mixed $returnValue): void
        {
            $this->returnValue = $returnValue;
        }
    }

Both events allow you to get some information (e.g. ``getMessage()``) and even change
that information (e.g. ``setMessage()``).

Now, you can create an event subscriber to hook into this event. For example, you
could listen to the ``mailer.post_send`` event and change the method's return value::

    // src/EventSubscriber/MailPostSendSubscriber.php
    namespace App\EventSubscriber;

    use App\Event\AfterSendMailEvent;
    use Symfony\Component\EventDispatcher\EventSubscriberInterface;

    class MailPostSendSubscriber implements EventSubscriberInterface
    {
        public function onMailerPostSend(AfterSendMailEvent $event): void
        {
            $returnValue = $event->getReturnValue();
            // modify the original $returnValue value

            $event->setReturnValue($returnValue);
        }

        public static function getSubscribedEvents(): array
        {
            return [
                'mailer.post_send' => 'onMailerPostSend',
            ];
        }
    }

That's it! Your subscriber should be called automatically (or read more about
:ref:`event subscriber configuration <ref-event-subscriber-configuration>`).

Learn More
----------

- :ref:`The Request-Response Lifecycle <the-workflow-of-a-request>`
- :doc:`/reference/events`
- :ref:`Security-related Events <security-security-events>`
- :ref:`The kernel.event_listener tag <dic-tags-kernel-event-listener>`
- :ref:`The kernel.event_subscriber tag <dic-tags-kernel-event-subscriber>`

.. _`Mediator`: https://en.wikipedia.org/wiki/Mediator_pattern
.. _`Observer`: https://en.wikipedia.org/wiki/Observer_pattern
.. _`PSR-14`: https://www.php-fig.org/psr/psr-14/
.. _`PHP callable`: https://www.php.net/manual/en/language.types.callable.php
.. _`Closures`: https://www.php.net/manual/en/functions.anonymous.php
