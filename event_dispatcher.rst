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

Now that the class is created, you need to register it as a service and
notify Symfony that it is an event listener by using a special "tag":

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\EventListener\ExceptionListener:
                tags: [kernel.event_listener]

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\EventListener\ExceptionListener">
                    <tag name="kernel.event_listener"/>
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
                ->tag('kernel.event_listener')
            ;
        };

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
    own listeners can use any positive or negative integer.

.. note::

    There is an optional attribute for the ``kernel.event_listener`` tag called
    ``event`` which is useful when listener ``$event`` argument is not typed.
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

.. _event-dispatcher_event-listener-attributes:

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

:class:`Symfony\\Component\\EventDispatcher\\Attribute\\AsEventListener`
can also be applied to methods directly::

    namespace App\EventListener;

    use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

    final class MyMultiListener
    {
        #[AsEventListener()]
        public function onCustomEvent(CustomEvent $event): void
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
        protected function build(ContainerBuilder $containerBuilder)
        {
            $containerBuilder->addCompilerPass(new AddEventAliasesPass([
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

.. _event-dispatcher-before-after-filters:

How to Set Up Before and After Filters
--------------------------------------

It is quite common in web application development to need some logic to be
performed right before or directly after your controller actions acting as
filters or hooks.

Some web frameworks define methods like ``preExecute()`` and ``postExecute()``,
but there is no such thing in Symfony. The good news is that there is a much
better way to interfere with the Request -> Response process using the
:doc:`EventDispatcher component </components/event_dispatcher>`.

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

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <parameters>
                <parameter key="tokens" type="collection">
                    <parameter key="client1">pass1</parameter>
                    <parameter key="client2">pass2</parameter>
                </parameter>
            </parameters>
        </container>

    .. code-block:: php

        // config/services.php
        $container->setParameter('tokens', [
            'client1' => 'pass1',
            'client2' => 'pass2',
        ]);

Tag Controllers to Be Checked
.............................

A ``kernel.controller`` (aka ``KernelEvents::CONTROLLER``) listener gets notified
on *every* request, right before the controller is executed. So, first, you need
some way to identify if the controller that matches the request needs token validation.

A clean and easy way is to create an empty interface and make the controllers
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

    class FooController extends AbstractController implements TokenAuthenticatedController
    {
        // An action that needs authentication
        public function bar()
        {
            // ...
        }
    }

Creating an Event Subscriber
............................

Next, you'll need to create an event subscriber, which will hold the logic
that you want to be executed before your controllers. If you're not familiar with
event subscribers, you can learn more about them at :doc:`/event_dispatcher`::

    // src/EventSubscriber/TokenSubscriber.php
    namespace App\EventSubscriber;

    use App\Controller\TokenAuthenticatedController;
    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Symfony\Component\HttpKernel\Event\ControllerEvent;
    use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
    use Symfony\Component\HttpKernel\KernelEvents;

    class TokenSubscriber implements EventSubscriberInterface
    {
        public function __construct(
            private $tokens
        ) {
        }

        public function onKernelController(ControllerEvent $event)
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

        public static function getSubscribedEvents()
        {
            return [
                KernelEvents::CONTROLLER => 'onKernelController',
            ];
        }
    }

That's it! Your ``services.yaml`` file should already be setup to load services from
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

    public function onKernelController(ControllerEvent $event)
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

    public function onKernelResponse(ResponseEvent $event)
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

    public static function getSubscribedEvents()
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

    class CustomMailer
    {
        // ...

        public function send($subject, $message)
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
            private $subject,
            private $message,
        ) {
        }

        public function getSubject()
        {
            return $this->subject;
        }

        public function setSubject($subject)
        {
            $this->subject = $subject;
        }

        public function getMessage()
        {
            return $this->message;
        }

        public function setMessage($message)
        {
            $this->message = $message;
        }
    }

And the ``AfterSendMailEvent`` even like this::

    // src/Event/AfterSendMailEvent.php
    namespace App\Event;

    use Symfony\Contracts\EventDispatcher\Event;

    class AfterSendMailEvent extends Event
    {
        public function __construct(
            private $returnValue,
        ) {
        }

        public function getReturnValue()
        {
            return $this->returnValue;
        }

        public function setReturnValue($returnValue)
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
        public function onMailerPostSend(AfterSendMailEvent $event)
        {
            $returnValue = $event->getReturnValue();
            // modify the original ``$returnValue`` value

            $event->setReturnValue($returnValue);
        }

        public static function getSubscribedEvents()
        {
            return [
                'mailer.post_send' => 'onMailerPostSend',
            ];
        }
    }

That's it! Your subscriber should be called automatically (or read more about
:ref:`event subscriber configuration <ref-event-subscriber-configuration>`).
