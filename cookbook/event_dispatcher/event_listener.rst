.. index::
   single: Events; Create listener
   single: Create subscriber

How to Create Event Listeners and Subscribers
=============================================

Symfony has various events and hooks that can be used to perform custom
actions in your application. Those events are triggered by the HttpKernel
component and they are defined in the :class:`Symfony\\Component\\HttpKernel\\KernelEvents`
class.

To hook into an event and execute your own custom logic, you have to create
a service that listens to that event. As explained in this article, you can do
that in two different ways: creating an event listener or an event subscriber.

The examples of this article only use the ``KernelEvents::EXCEPTION`` event for
consistency purposes. In your own application, you can use any event and even mix
several of them in the same subscriber.

Creating an Event Listener
--------------------------

The most common way to listen to an event is to register an **event listener**::

    // src/AppBundle/Listener/ExceptionListener.php
    namespace AppBundle\Listener;

    use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

    class ExceptionListener
    {
        public function onKernelException(GetResponseForExceptionEvent $event)
        {
            // You get the exception object from the received event
            $exception = $event->getException();
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
                $response->setStatusCode(500);
            }

            // Send the modified response object to the event
            $event->setResponse($response);
        }
    }

.. tip::

    Each event receives a slightly different type of ``$event`` object. For
    the ``kernel.exception`` event, it is :class:`Symfony\\Component\\HttpKernel\\Event\\GetResponseForExceptionEvent`.
    To see what type of object each event listener receives, see :class:`Symfony\\Component\\HttpKernel\\KernelEvents`.

Now that the class is created, you just need to register it as a service and
notify Symfony that it is a "listener" on the ``kernel.exception`` event by
using a special "tag":

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            kernel.listener.your_listener_name:
                class: AppBundle\Listener\ExceptionListener
                tags:
                    - { name: kernel.event_listener, event: kernel.exception }

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <service id="kernel.listener.your_listener_name" class="AppBundle\Listener\ExceptionListener">
            <tag name="kernel.event_listener" event="kernel.exception" />
        </service>

    .. code-block:: php

        // app/config/services.php
        $container
            ->register('kernel.listener.your_listener_name', 'AppBundle\Listener\ExceptionListener')
            ->addTag('kernel.event_listener', array('event' => 'kernel.exception'))
        ;

.. note::

    There is an optional tag attribute called ``method`` which defines which method
    to execute when the event is triggered. By default the name of the method is
    ``on`` + "camel-cased event name". If the event is ``kernel.exception`` the
    method executed by default is ``onKernelException()``.

    The other optional tag attribute is called  ``priority``, which defaults to
    ``0`` and it controls the order in which listeners are executed (the highest
    the priority, the earlier a listener is executed). This is useful when you
    need to guarantee that one listener is executed before another. The priorities
    of the internal Symfony events range from ``-255`` to ``255`` but your own
    events can use any positive or negative integer.

Creating an Event Subscriber
----------------------------

Another way to listen to events is via an **event subscriber**, which is a class
that defines one or more methods that listen to one or various events. The main
difference with the event listeners is that subscribers always know which events
they are listening to.

In a given subscriber, different methods can listen to the same event. The order
in which methods are executed is defined by the ``priority`` parameter of each
method (the higher the priority the earlier the method is called). To learn more
about event subscribers, read :doc:`/components/event_dispatcher/introduction`.

The following example shows an event subscriber that defines several methods which
listen to the same ``kernel.exception`` event::

    // src/AppBundle/Subscriber/ExceptionSubscriber.php
    namespace AppBundle\Subscriber;

    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
    use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

    class ExceptionSubscriber implements EventSubscriberInterface
    {
        public static function getSubscribedEvents()
        {
            // return the subscribed events, their methods and priorities
            return array(
               'kernel.exception' => array(
                   array('processException', 10),
                   array('logException', 0),
                   array('notifyException', -10),
               )
            );
        }

        public function processException(GetResponseForExceptionEvent $event)
        {
            // ...
        }

        public function logException(GetResponseForExceptionEvent $event)
        {
            // ...
        }

        public function notifyException(GetResponseForExceptionEvent $event)
        {
            // ...
        }
    }

Now, you just need to register the class as a service and notify Symfony that it
is an event subscriber:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            app.exception_subscriber:
                class: AppBundle\Subscriber\ExceptionSubscriber
                tags:
                    - { name: kernel.event_subscriber }

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services">

            <services>
                <service id="app.exception_subscriber"
                    class="AppBundle\Subscriber\ExceptionSubscriber">

                    <tag name="kernel.event_subscriber"/>

                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/services.php
        $container
            ->register(
                'app.exception_subscriber',
                'AppBundle\Subscriber\ExceptionSubscriber'
            )
            ->addTag('kernel.event_subscriber')
        ;

Request Events, Checking Types
------------------------------

A single page can make several requests (one master request, and then multiple
sub-requests), which is why when working with the ``KernelEvents::REQUEST``
event, you might need to check the type of the request. This can be easily
done as follow::

    // src/AppBundle/Listener/RequestListener.php
    namespace AppBundle\Listener;

    use Symfony\Component\HttpKernel\Event\GetResponseEvent;
    use Symfony\Component\HttpKernel\HttpKernel;
    use Symfony\Component\HttpKernel\HttpKernelInterface;

    class RequestListener
    {
        public function onKernelRequest(GetResponseEvent $event)
        {
            if ($event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST) {
                // don't do anything if it's not the master request
                return;
            }

            // ...
        }
    }

.. tip::

    Two types of request are available in the :class:`Symfony\\Component\\HttpKernel\\HttpKernelInterface`
    interface: ``HttpKernelInterface::MASTER_REQUEST`` and
    ``HttpKernelInterface::SUB_REQUEST``.

Events or Subscribers
---------------------

Listeners and subscribers can be used in the same application indistinctly. The
decision to use either of them is usually a matter of personal taste. However,
there are some minor advantages for each of them:

* **Subscribers are easier to reuse** because the knowledge of the events is kept
  in the class rather than in the service definition. This is the reason why
  Symfony uses subscribers internally;
* **Listeners are more flexible** because bundles can enable or disable each of
  them conditionally depending on some configuration value.
