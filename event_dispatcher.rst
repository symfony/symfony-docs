.. index::
   single: Events; Create listener
   single: Create subscriber

Events and Event Listeners
==========================

During the execution of a Symfony application, lots of event notifications are
triggered. Your application can listen to these notifications and respond to
them by executing any piece of code.

Symfony triggers several :doc:`events related to the kernel </reference/events>`
while processing the HTTP Request. Third-party bundles may also dispatch events, and
you can even dispatch :doc:`custom events </components/event_dispatcher>` from your
own code.

All the examples shown in this article use the same ``KernelEvents::EXCEPTION``
event for consistency purposes. In your own application, you can use any event
and even mix several of them in the same subscriber.

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
        public function onKernelException(ExceptionEvent $event)
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

.. tip::

    Each event receives a slightly different type of ``$event`` object. For
    the ``kernel.exception`` event, it is :class:`Symfony\\Component\\HttpKernel\\Event\\ExceptionEvent`.
    Check out the :doc:`Symfony events reference </reference/events>` to see
    what type of object each event provides.

Now that the class is created, you need to register it as a service and
notify Symfony that it is a "listener" on the ``kernel.exception`` event by
using a special "tag":

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\EventListener\ExceptionListener:
                tags:
                    - { name: kernel.event_listener, event: kernel.exception }

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\EventListener\ExceptionListener">
                    <tag name="kernel.event_listener" event="kernel.exception"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\EventListener\ExceptionListener;

        return function(ContainerConfigurator $containerConfigurator) {
            $services = $containerConfigurator->services();

            $services->set(ExceptionListener::class)
                ->tag('kernel.event_listener', ['event' => 'kernel.exception'])
            ;
        };

Symfony follows this logic to decide which method to call inside the event
listener class:

#. If the ``kernel.event_listener`` tag defines the ``method`` attribute, that's
   the name of the method to be called;
#. If no ``method`` attribute is defined, try to call the method whose name
   is ``on`` + "PascalCased event name" (e.g. ``onKernelException()`` method for
   the ``kernel.exception`` event);
#. If that method is not defined either, try to call the ``__invoke()`` magic
   method (which makes event listeners invokable);
#. If the ``__invoke()`` method is not defined either, throw an exception.

.. note::

    There is an optional attribute for the ``kernel.event_listener`` tag called
    ``priority``, which is a positive or negative integer that defaults to ``0``
    and it controls the order in which listeners are executed (the higher the
    number, the earlier a listener is executed). This is useful when you need to
    guarantee that one listener is executed before another. The priorities of the
    internal Symfony listeners usually range from ``-256`` to ``256`` but your
    own listeners can use any positive or negative integer.

Defining Event Listeners with PHP Attributes
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

An alternative way to define an event listener is to use the
:class:`Symfony\\Component\\EventDispatcher\\Attribute\\AsEventListener`
PHP attribute. This allows to configure the listener inside its class, without
having to add any configuration in external files::

    namespace App\EventListener;

    use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

    #[AsEventListener]
    final class MyListener
    {
        public function __invoke(CustomEvent $event): void
        {
            // ...
        }
    }

You can add multiple ``#[AsEventListener()]`` attributes to configure different methods::

    namespace App\EventListener;

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

.. _events-subscriber:

Creating an Event Subscriber
----------------------------

Another way to listen to events is via an **event subscriber**, which is a class
that defines one or more methods that listen to one or various events. The main
difference with the event listeners is that subscribers always know the events
to which they are listening.

If different event subscriber methods listen to the same event, their order is
defined by the ``priority`` parameter. This value is a positive or negative
integer which defaults to ``0``. The higher the number, the earlier the method
is called. **Priority is aggregated for all listeners and subscribers**, so your
methods could be called before or after the methods defined in other listeners
and subscribers. To learn more about event subscribers, read :doc:`/components/event_dispatcher`.

The following example shows an event subscriber that defines several methods which
listen to the same ``kernel.exception`` event::

    // src/EventSubscriber/ExceptionSubscriber.php
    namespace App\EventSubscriber;

    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Symfony\Component\HttpKernel\Event\ExceptionEvent;
    use Symfony\Component\HttpKernel\KernelEvents;

    class ExceptionSubscriber implements EventSubscriberInterface
    {
        public static function getSubscribedEvents()
        {
            // return the subscribed events, their methods and priorities
            return [
                KernelEvents::EXCEPTION => [
                    ['processException', 10],
                    ['logException', 0],
                    ['notifyException', -10],
                ],
            ];
        }

        public function processException(ExceptionEvent $event)
        {
            // ...
        }

        public function logException(ExceptionEvent $event)
        {
            // ...
        }

        public function notifyException(ExceptionEvent $event)
        {
            // ...
        }
    }

That's it! Your ``services.yaml`` file should already be setup to load services from
the ``EventSubscriber`` directory. Symfony takes care of the rest.

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
        public function onKernelRequest(RequestEvent $event)
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

Listeners and subscribers can be used in the same application indistinctly. The
decision to use either of them is usually a matter of personal taste. However,
there are some minor advantages for each of them:

* **Subscribers are easier to reuse** because the knowledge of the events is kept
  in the class rather than in the service definition. This is the reason why
  Symfony uses subscribers internally;
* **Listeners are more flexible** because bundles can enable or disable each of
  them conditionally depending on some configuration value.

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

        public function onKernelRequest(RequestEvent $event)
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
        protected function build(ContainerBuilder $container)
        {
            $container->addCompilerPass(new AddEventAliasesPass([
                MyCustomEvent::class => 'my_custom_event',
            ]));
        }
    }

The compiler pass will always extend the existing list of aliases. Because of
that, it is safe to register multiple instances of the pass with different
configurations.

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

or can get everything which partial matches the event name:

.. code-block:: terminal

    $ php bin/console debug:event-dispatcher kernel // matches "kernel.exception", "kernel.response" etc.
    $ php bin/console debug:event-dispatcher Security // matches "Symfony\Component\Security\Http\Event\CheckPassportEvent"

The :doc:`security </security>` system uses an event dispatcher per
firewall. Use the ``--dispatcher`` option to get the registered listeners
for a particular event dispatcher:

.. code-block:: terminal

    $ php bin/console debug:event-dispatcher --dispatcher=security.event_dispatcher.main

Learn more
----------

.. toctree::
    :maxdepth: 1

    event_dispatcher/before_after_filters
    event_dispatcher/method_behavior
