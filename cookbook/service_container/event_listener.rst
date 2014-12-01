.. index::
   single: Events; Create listener
   single: Create subscriber

How to Create Event Listeners and Subscribers
=============================================

Symfony has various events and hooks that can be used to trigger custom
behavior in your application. Those events are thrown by the HttpKernel
component and can be viewed in the :class:`Symfony\\Component\\HttpKernel\\KernelEvents` class.

To hook into an event and add your own custom logic, you have to create
a service that will act as an event listener on that event. You can do that in two different ways, 
creating an event listener or an event subscriber instead. In this entry,
you will see the two ways of creating a service that will act as an exception listener, allowing
you to modify how exceptions are shown by your application. The ``KernelEvents::EXCEPTION``
event is just one of the core kernel events.

Creating an Event Listener
--------------------------

The most common way to listen to an event is to register an event listener::

    // src/AppBundle/EventListener/ExceptionListener.php
    namespace AppBundle\EventListener;

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
                $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            // Send the modified response object to the event
            $event->setResponse($response);
        }
    }

.. versionadded:: 2.4
    Support for HTTP status code constants was introduced in Symfony 2.4.

.. tip::

    Each event receives a slightly different type of ``$event`` object. For
    the ``kernel.exception`` event, it is :class:`Symfony\\Component\\HttpKernel\\Event\\GetResponseForExceptionEvent`.
    To see what type of object each event listener receives, see :class:`Symfony\\Component\\HttpKernel\\KernelEvents`.

Now that the class is created, you just need to register it as a service and
notify Symfony that it is a "listener" on the ``kernel.exception`` event by
using a special "tag":

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        services:
            kernel.listener.your_listener_name:
                class: Acme\DemoBundle\EventListener\AcmeExceptionListener
                tags:
                    - { name: kernel.event_listener, event: kernel.exception, method: onKernelException }

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <service id="kernel.listener.your_listener_name" class="Acme\DemoBundle\EventListener\AcmeExceptionListener">
            <tag name="kernel.event_listener" event="kernel.exception" method="onKernelException" />
        </service>

    .. code-block:: php

        // app/config/config.php
        $container
            ->register('kernel.listener.your_listener_name', 'Acme\DemoBundle\EventListener\AcmeExceptionListener')
            ->addTag('kernel.event_listener', array('event' => 'kernel.exception', 'method' => 'onKernelException'))
        ;

.. note::

    There is an additional tag option ``priority`` that is optional and defaults
    to 0. This value can be from -255 to 255, and the listeners will be executed
    in the order of their priority (highest to lowest). This is useful when
    you need to guarantee that one listener is executed before another.

Creating an Event Subscriber
----------------------------

Another way to listen to events is via an event subscriber. An event subscriber
can define one or various methods that listen to one or various events,
and can set a priority for each method. The higher the priority, the earlier
the method is called. To learn more about event subscribers, see `The EventDispatcher component`_.
The following example shows a subscriber that subscribes various methods
to the ``kernel.exception`` event::

    // src/AppBundle/EventListener/ExceptionSubscriber.php
    namespace AppBundle\EventSubscriber;

    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

    class ExceptionSubscriber implements EventSubscriberInterface
    {
        public static function getSubscribedEvents()
        {
            // Return the events it is subscribed to, the methods that listen each event and the 
            // priority of each method
            return array(
               'kernel.exception' => array(
                   array('onKernelExceptionPre', 10),
                   array('onKernelExceptionMid', 5),
                   array('onKernelExceptionPost', 0),
               )
            );
        }
        
        public function onKernelExceptionPre(GetResponseForExceptionEvent $event)
        {
            $exception = $event->getException();
            $message = sprintf(
                'My Error says: %s with code: %s',
                $exception->getMessage(),
                $exception->getCode()
            );

            $response = new Response();
            $response->setContent($message);

            if ($exception instanceof HttpExceptionInterface) {
                $response->setStatusCode($exception->getStatusCode());
                $response->headers->replace($exception->getHeaders());
            } else {
                $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $event->setResponse($response);
        }
        
        public function onKernerlExceptionMid(GetResponseForExceptionEvent $event)
        {
            // ...
        }
        
        public function onKernerlExceptionPost(GetResponseForExceptionEvent $event)
        {
            // ...
        }
    }
    
Now, you just need to register the class as a service and notify Symfony that it 
is an event subscriber:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        services:
            kernel.listener.your_subscriber_name:
                class: Acme\DemoBundle\EventSubscriber\AcmeExceptionSubscriber
                tags:
                    - { name: kernel.event_subscriber }

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services">

            <services>
                <service id="acme_exception_subscriber"
                    class="Acme\DemoBundle\EventSubscriber\AcmeExceptionSubscriber">

                    <tag name="kernel.event_subscriber"/>

                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container
            ->register(
                'acme_exception_subscriber',
                'Acme\DemoBundle\EventSubscriber\AcmeExceptionSubscriber'
            )
            ->addTag('kernel.event_subscriber')
        ;

Request Events, Checking Types
------------------------------

.. versionadded:: 2.4
    The ``isMasterRequest()`` method was introduced in Symfony 2.4.
    Prior, the ``getRequestType()`` method must be used.

A single page can make several requests (one master request, and then multiple
sub-requests), which is why when working with the ``KernelEvents::REQUEST``
event, you might need to check the type of the request. This can be easily
done as follow::

    // src/AppBundle/EventListener/AcmeRequestListener.php
    namespace AppBundle\EventListener;

    use Symfony\Component\HttpKernel\Event\GetResponseEvent;
    use Symfony\Component\HttpKernel\HttpKernel;

    class AcmeRequestListener
    {
        public function onKernelRequest(GetResponseEvent $event)
        {
            if (!$event->isMasterRequest()) {
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

.. _`The EventDispatcher component`: http://symfony.com/doc/current/components/event_dispatcher/index.html